<?php

$viewsDir = __DIR__ . '/../resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$filesToCheck = [];
foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $filesToCheck[] = $file->getPathname();
    }
}

sort($filesToCheck);
$report = [];

foreach ($filesToCheck as $filePath) {
    $relPath = str_replace(realpath($viewsDir) . DIRECTORY_SEPARATOR, '', realpath($filePath));
    $rawContent = file_get_contents($filePath);

    $issues = [];

    // --- 1. Strip Blade comments: {{-- ... --}} ---
    // Keep line numbers roughly intact by replacing comment content with spaces/newlines
    $contentNoComments = preg_replace_callback('/\{\{--.*?--\}\}/s', function($m) {
        return str_repeat("\n", substr_count($m[0], "\n"));
    }, $rawContent);

    // --- 2. Check Blade Directives Nesting ---
    $lines = explode("\n", $contentNoComments);
    $directiveStack = [];
    $inPhpBlock = false;

    $blockPairs = [
        'if' => ['endif'],
        'unless' => ['endunless'],
        'isset' => ['endisset'],
        'empty' => ['endempty'],
        'foreach' => ['endforeach'],
        'forelse' => ['endforelse'],
        'for' => ['endfor'],
        'while' => ['endwhile'],
        'switch' => ['endswitch'],
        'push' => ['endpush'],
        'prepend' => ['endprepend'],
        'can' => ['endcan'],
        'cannot' => ['endcannot'],
        'auth' => ['endauth'],
        'guest' => ['endguest'],
        'error' => ['enderror'],
        'component' => ['endcomponent'],
        'slot' => ['endslot'],
        'once' => ['endonce'],
    ];

    $allOpen = array_keys($blockPairs);
    $closeToOpen = [];
    foreach ($blockPairs as $o => $cList) {
        foreach ($cList as $c) {
            $closeToOpen[$c] = $o;
        }
    }

    foreach ($lines as $idx => $line) {
        $lineNum = $idx + 1;
        $trimmed = trim($line);

        // Check @php block vs single-line @php(...)
        if (preg_match('/@php\b(?!\s*\()/', $trimmed)) {
            $inPhpBlock = true;
        }
        if (str_contains($trimmed, '@endphp')) {
            $inPhpBlock = false;
            continue;
        }
        if ($inPhpBlock) {
            continue;
        }

        // Section handling:
        // @section('name') has matching @endsection or @stop or @show
        // @section('name', 'value') is INLINE and has NO matching end directive!
        if (preg_match_all('/@section\s*\((.*?)\)/', $line, $secMatches, PREG_SET_ORDER)) {
            foreach ($secMatches as $sm) {
                // If there are commas separating arguments at root level of the argument list:
                $args = $sm[1];
                // Simple heuristic: if comma exists and isn't inside quotes/brackets, it's 2-arg inline
                // e.g. 'title', 'Dashboard'
                if (preg_match('/^[\'"][^\'"]+[\'"]\s*,\s*/', trim($args))) {
                    // Inline @section, does not need @endsection
                } else {
                    $directiveStack[] = ['dir' => 'section', 'line' => $lineNum];
                }
            }
        }
        if (preg_match_all('/@(endsection|stop|show|overwrite)\b/', $line, $secEndMatches)) {
            foreach ($secEndMatches[1] as $endDir) {
                // Pop the last section from stack
                $foundSection = false;
                for ($k = count($directiveStack) - 1; $k >= 0; $k--) {
                    if ($directiveStack[$k]['dir'] === 'section') {
                        array_splice($directiveStack, $k, 1);
                        $foundSection = true;
                        break;
                    }
                }
                if (!$foundSection) {
                    $issues[] = [
                        'type' => 'UNMATCHED_ENDSECTION',
                        'line' => $lineNum,
                        'msg' => "@{$endDir} found without an opening @section block."
                    ];
                }
            }
        }

        // Check other paired directives
        // Match @directive(...) or @directive
        if (preg_match_all('/@([a-zA-Z_]+)\b(?:\s*(\(.*?\)))?/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $dir = strtolower($m[1]);
                $hasParen = !empty(trim($m[2] ?? ''));

                if ($dir === 'section' || $dir === 'endsection' || $dir === 'stop' || $dir === 'show' || $dir === 'overwrite') {
                    continue; // Handled separately
                }
                // Skip JS / Alpine event directives
                if (in_array($dir, ['click', 'change', 'submit', 'keydown', 'keyup', 'keypress', 'mouseenter', 'mouseleave', 'input', 'blur', 'focus', 'window'])) {
                    continue;
                }

                // In Blade: @empty without parentheses is the empty branch of @forelse
                // Only @empty(...) with parentheses is a block requiring @endempty!
                if ($dir === 'empty' && !$hasParen) {
                    continue;
                }

                if (in_array($dir, $allOpen)) {
                    $directiveStack[] = ['dir' => $dir, 'line' => $lineNum];
                } elseif (isset($closeToOpen[$dir])) {
                    $expected = $closeToOpen[$dir];
                    if (empty($directiveStack)) {
                        $issues[] = [
                            'type' => 'UNMATCHED_CLOSING_DIRECTIVE',
                            'line' => $lineNum,
                            'msg' => "@{$dir} without preceding open directive."
                        ];
                    } else {
                        $top = array_pop($directiveStack);
                        if ($top['dir'] !== $expected) {
                            $issues[] = [
                                'type' => 'MISMATCHED_DIRECTIVE',
                                'line' => $lineNum,
                                'msg' => "@{$dir} closed, but expecting @end{$top['dir']} (opened at line {$top['line']})."
                            ];
                        }
                    }
                }
            }
        }

        // Check for unbalanced braces on the line (ignoring strings)
        // Check unbalanced {{ }}
        $openEcho = substr_count($line, '{{');
        $closeEcho = substr_count($line, '}}');
        // If single line has mismatched {{ }} and does not continue on next line...
        // Let's only flag if file-wide total is mismatched
    }

    if (!empty($directiveStack)) {
        foreach ($directiveStack as $unclosed) {
            $issues[] = [
                'type' => 'UNCLOSED_DIRECTIVE',
                'line' => $unclosed['line'],
                'msg' => "Unclosed @{$unclosed['dir']} from line {$unclosed['line']}."
            ];
        }
    }

    // --- 3. File-wide Echo Braces Balance ---
    $openDoubleCount = substr_count($contentNoComments, '{{');
    $closeDoubleCount = substr_count($contentNoComments, '}}');
    if ($openDoubleCount !== $closeDoubleCount) {
        $issues[] = [
            'type' => 'UNBALANCED_ECHO_BRACES',
            'line' => 0,
            'msg' => "Mismatched {{ and }}: {$openDoubleCount} '{{' vs {$closeDoubleCount} '}}'."
        ];
    }

    $openRawCount = substr_count($contentNoComments, '{!!');
    $closeRawCount = substr_count($contentNoComments, '!!}');
    if ($openRawCount !== $closeRawCount) {
        $issues[] = [
            'type' => 'UNBALANCED_RAW_ECHO',
            'line' => 0,
            'msg' => "Mismatched {!! and !!}: {$openRawCount} '{!!' vs {$closeRawCount} '!!}'."
        ];
    }

    // --- 4. Check for Blade Directives inside HTML Attributes ---
    // Linters flag <div class="... @if(...) ... @endif ...">
    if (preg_match_all('/<[^>]*\b(?:class|id|style|href|value|type)\s*=\s*"[^"]*@(if|foreach|unless)\b[^"]*"[^>]*>/is', $contentNoComments, $attrMatches, PREG_OFFSET_CAPTURE)) {
        foreach ($attrMatches[0] as $am) {
            $offset = $am[1];
            $lineAtOffset = substr_count(substr($contentNoComments, 0, $offset), "\n") + 1;
            $issues[] = [
                'type' => 'DIRECTIVE_INSIDE_HTML_ATTRIBUTE',
                'line' => $lineAtOffset,
                'msg' => "Blade directive inside HTML attribute quotes causes IDE syntax errors: " . substr(trim($am[0]), 0, 100) . "..."
            ];
        }
    }

    // --- 5. Check JavaScript syntax inside <script> blocks ---
    if (preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $contentNoComments, $scriptMatches, PREG_OFFSET_CAPTURE)) {
        foreach ($scriptMatches[1] as $sMatch) {
            $scriptCode = $sMatch[0];
            $scriptOffset = $sMatch[1];
            $scriptLine = substr_count(substr($contentNoComments, 0, $scriptOffset), "\n") + 1;

            // If empty script (e.g. <script src="...">), skip
            if (empty(trim($scriptCode))) {
                continue;
            }

            // Replace quoted blade echo '{{ ... }}' or "{{ ... }}" with "__BLADE_STR__"
            $sanitizedJs = preg_replace('/([\'"])\{\{.*?\}\}\1/s', '"__BLADE_STR__"', $scriptCode);
            $sanitizedJs = preg_replace('/([\'"])\{!!.*?!!\}\1/s', '"__BLADE_STR__"', $sanitizedJs);
            // Replace @json(...) with "{}"
            $sanitizedJs = preg_replace('/@json\s*\(.*?\)/s', '{}', $sanitizedJs);
            // Replace remaining unquoted {{ ... }} with "__BLADE_VAL__"
            $sanitizedJs = preg_replace('/\{\{.*?\}\}/s', '"__BLADE_VAL__"', $sanitizedJs);
            $sanitizedJs = preg_replace('/\{!!.*?!!\}/s', '"__BLADE_VAL__"', $sanitizedJs);
            // Replace blade @if / @endif inside script with comments
            $sanitizedJs = preg_replace('/@(if|elseif|else|endif|foreach|endforeach|php|endphp)\b.*?(\n|$)/', "// blade\n", $sanitizedJs);

            // Write to temp file and test with node --check
            $tempJs = tempnam(sys_get_temp_dir(), 'js_lint_') . '.js';
            file_put_contents($tempJs, $sanitizedJs);
            $jsCheck = shell_exec("node --check \"$tempJs\" 2>&1");
            unlink($tempJs);

            if ($jsCheck && (str_contains($jsCheck, 'SyntaxError:') || str_contains($jsCheck, 'error:'))) {
                // Filter out harmless mock mismatches if any
                $issues[] = [
                    'type' => 'JAVASCRIPT_SYNTAX_ERROR',
                    'line' => $scriptLine,
                    'msg' => "JavaScript syntax error in <script> block: " . trim(explode("\n", $jsCheck)[0])
                ];
            }
        }
    }

    // --- 6. HTML Major Tags Balance (div, table, form, select) ---
    // Strip Blade, scripts, styles
    $strippedHtml = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $contentNoComments);
    $strippedHtml = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $strippedHtml);
    $strippedHtml = preg_replace('/@php.*?@endphp/s', '', $strippedHtml);
    $strippedHtml = preg_replace('/\{\{.*?\}\}/s', '', $strippedHtml);
    $strippedHtml = preg_replace('/\{!!.*?!!\}/s', '', $strippedHtml);
    $strippedHtml = preg_replace('/@[a-zA-Z_]+\b.*?\n/', "\n", $strippedHtml);

    // Tags to check
    $tagsToCheck = ['div', 'table', 'form', 'select', 'tbody', 'thead', 'tr'];
    foreach ($tagsToCheck as $tag) {
        preg_match_all("/<{$tag}\b[^>]*>/i", $strippedHtml, $openMatches);
        preg_match_all("/<\/{$tag}>/i", $strippedHtml, $closeMatches);
        $diff = count($openMatches[0]) - count($closeMatches[0]);
        if ($diff !== 0) {
            // Note: In layouts or partials (e.g. sidebar or modal partials), unclosed tags might be intentional.
            // But in standalone views or full pages, it's often a syntax bug!
            $issues[] = [
                'type' => "UNBALANCED_{$tag}_TAG",
                'line' => 0,
                'msg' => "Unbalanced <{$tag}>: " . count($openMatches[0]) . " opening vs " . count($closeMatches[0]) . " closing (diff: {$diff})."
            ];
        }
    }

    if (!empty($issues)) {
        $report[$relPath] = $issues;
    }
}

echo "CHECK FINISHED: " . count($filesToCheck) . " files examined.\n";
echo "Files with potential issues: " . count($report) . "\n\n";

foreach ($report as $file => $issueList) {
    echo "========================================================\n";
    echo "FILE: {$file}\n";
    echo "========================================================\n";
    foreach ($issueList as $iss) {
        $lineInfo = $iss['line'] > 0 ? "Line {$iss['line']}" : "General";
        echo "  [{$iss['type']}] ({$lineInfo}): {$iss['msg']}\n";
    }
    echo "\n";
}
