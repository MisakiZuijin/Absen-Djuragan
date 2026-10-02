<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Blade;

$viewsDir = resource_path('views');
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$results = [];
$totalFiles = 0;

$pairedDirectives = [
    'if' => ['endif'],
    'unless' => ['endunless'],
    'isset' => ['endisset'],
    'empty' => ['endempty'],
    'foreach' => ['endforeach'],
    'forelse' => ['endforelse'],
    'for' => ['endfor'],
    'while' => ['endwhile'],
    'switch' => ['endswitch'],
    'section' => ['endsection', 'stop', 'show', 'overwrite'],
    'push' => ['endpush'],
    'prepend' => ['endprepend'],
    'can' => ['endcan'],
    'cannot' => ['endcannot'],
    'auth' => ['endauth'],
    'guest' => ['endguest'],
    'error' => ['enderror'],
    'component' => ['endcomponent'],
    'slot' => ['endslot'],
];

$allOpenDirectives = array_keys($pairedDirectives);
$allCloseDirectives = [];
foreach ($pairedDirectives as $open => $closes) {
    foreach ($closes as $c) {
        $allCloseDirectives[$c] = $open;
    }
}

foreach ($files as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $totalFiles++;
    $path = $file->getPathname();
    $relPath = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $path);
    $content = file_get_contents($path);
    $lines = explode("\n", $content);

    $fileIssues = [];

    // 1. Check Blade Compilation & PHP Syntax
    try {
        $compiled = Blade::compileString($content);
        $tempFile = tempnam(sys_get_temp_dir(), 'blade_lint_') . '.php';
        file_put_contents($tempFile, $compiled);
        $lintOutput = shell_exec("php -l \"$tempFile\" 2>&1");
        unlink($tempFile);

        if ($lintOutput && !str_contains($lintOutput, 'No syntax errors detected')) {
            $fileIssues[] = [
                'type' => 'PHP_PARSE_ERROR_IN_COMPILED_BLADE',
                'line' => 0,
                'detail' => trim($lintOutput)
            ];
        }
    } catch (\Throwable $e) {
        $fileIssues[] = [
            'type' => 'BLADE_COMPILATION_ERROR',
            'line' => $e->getLine(),
            'detail' => $e->getMessage()
        ];
    }

    // 2. Check Directive Balance
    $stack = [];
    $inPhpBlock = false;
    $inScriptBlock = false;

    foreach ($lines as $lineNum => $line) {
        $lineIndex = $lineNum + 1;
        $trimmed = trim($line);

        // Check @php ... @endphp
        if (preg_match('/@php\b(?!\s*\()/', $trimmed)) {
            $inPhpBlock = true;
        }
        if (str_contains($trimmed, '@endphp')) {
            $inPhpBlock = false;
            continue;
        }
        if ($inPhpBlock) {
            continue; // Skip directive checking inside raw @php blocks
        }

        // Check <script> ... </script>
        if (stripos($trimmed, '<script') !== false) {
            $inScriptBlock = true;
        }
        if (stripos($trimmed, '</script>') !== false) {
            $inScriptBlock = false;
        }

        // Check unclosed {{ or {!! on single line (multi-line is allowed, but let's check basic mismatches)
        $openDouble = substr_count($line, '{{');
        $closeDouble = substr_count($line, '}}');
        $openRaw = substr_count($line, '{!!');
        $closeRaw = substr_count($line, '!!}');

        // Look for stray @directives
        preg_match_all('/@([a-zA-Z_]+)\b(?:\s*\((.*?)\))?/', $line, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $directive = strtolower($match[1]);

            // Skip alpine @click, @change, @submit, @keydown etc inside HTML tags
            if (in_array($directive, ['click', 'change', 'submit', 'keydown', 'keyup', 'keypress', 'mouseenter', 'mouseleave', 'input', 'blur', 'focus', 'window'])) {
                continue;
            }

            // Skip common blade keywords that are single
            if (in_array($directive, [
                'extends', 'include', 'yield', 'csrf', 'method', 'livewire', 'vite',
                'else', 'elseif', 'empty', 'case', 'break', 'default', 'continue',
                'json', 'dd', 'dump', 'class', 'style', 'checked', 'selected', 'disabled',
                'props', 'aware', 'each', 'stack', 'once', 'endonce', 'vite'
            ])) {
                continue;
            }

            if (in_array($directive, $allOpenDirectives)) {
                $stack[] = ['directive' => $directive, 'line' => $lineIndex];
            } elseif (isset($allCloseDirectives[$directive])) {
                $expectedOpen = $allCloseDirectives[$directive];
                if (empty($stack)) {
                    $fileIssues[] = [
                        'type' => 'UNMATCHED_CLOSING_DIRECTIVE',
                        'line' => $lineIndex,
                        'detail' => "@{$directive} found but no open directive on stack."
                    ];
                } else {
                    $last = array_pop($stack);
                    if ($last['directive'] !== $expectedOpen) {
                        $fileIssues[] = [
                            'type' => 'MISMATCHED_DIRECTIVE',
                            'line' => $lineIndex,
                            'detail' => "@{$directive} closed, but top of stack was @{$last['directive']} from line {$last['line']}."
                        ];
                    }
                }
            }
        }
    }

    if (!empty($stack)) {
        foreach ($stack as $unclosed) {
            $fileIssues[] = [
                'type' => 'UNCLOSED_DIRECTIVE',
                'line' => $unclosed['line'],
                'detail' => "Unclosed @{$unclosed['directive']} from line {$unclosed['line']}."
            ];
        }
    }

    // 3. Check for HTML tag nesting issues (like unclosed <div> or stray </div>)
    // Strip php and blade blocks first
    $cleanContent = preg_replace('/@php.*?@endphp/s', '', $content);
    $cleanContent = preg_replace('/{{.*?}}/s', '', $cleanContent);
    $cleanContent = preg_replace('/{!!.*?!}/s', '', $cleanContent);
    $cleanContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $cleanContent);
    $cleanContent = preg_replace('/<!--.*?-->/s', '', $cleanContent);

    // Count <div> vs </div>
    preg_match_all('/<div\b[^>]*>/i', $cleanContent, $openDivs);
    preg_match_all('/<\/div>/i', $cleanContent, $closeDivs);
    $diffDiv = count($openDivs[0]) - count($closeDivs[0]);
    if ($diffDiv !== 0) {
        $fileIssues[] = [
            'type' => 'UNBALANCED_DIV_TAGS',
            'line' => 0,
            'detail' => "Found " . count($openDivs[0]) . " <div...> tags and " . count($closeDivs[0]) . " </div> tags (Difference: {$diffDiv})."
        ];
    }

    // 4. Check for blade @if inside HTML attribute string (very common syntax error in blade extensions)
    // e.g. class="... @if(...) ... @endif ..."
    if (preg_match('/<[^>]*\b(?:class|id|style|href|value)\s*=\s*"[^"]*@(if|foreach|unless)\b[^"]*"[^>]*>/is', $content, $attrMatch)) {
        $fileIssues[] = [
            'type' => 'DIRECTIVE_INSIDE_HTML_ATTRIBUTE',
            'line' => 0,
            'detail' => "Blade directive @" . $attrMatch[1] . " found inside HTML attribute quotes, which causes editor syntax errors."
        ];
    }

    if (!empty($fileIssues)) {
        $results[$relPath] = $fileIssues;
    }
}

echo "Scanned {$totalFiles} blade files.\n";
echo "Found issues in " . count($results) . " files.\n\n";

foreach ($results as $file => $issues) {
    echo "=== File: {$file} ===\n";
    foreach ($issues as $issue) {
        echo "  [{$issue['type']}] Line {$issue['line']}: {$issue['detail']}\n";
    }
    echo "\n";
}
