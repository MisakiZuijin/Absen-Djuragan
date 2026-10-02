<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$viewsDir = resource_path('views');
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$realJsErrors = [];

foreach ($files as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $path = $file->getPathname();
    $relPath = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $path);
    $content = file_get_contents($path);

    // Find all <script> tags, ignoring type="application/json"
    if (preg_match_all('/<script\b(?![^>]*type=[\'"]application\/json[\'"])[^>]*>(.*?)<\/script>/is', $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[1] as $idx => $scriptMatch) {
            $rawScript = $scriptMatch[0];
            $offset = $scriptMatch[1];
            $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;

            if (empty(trim($rawScript))) continue;

            // Clean blade directives and tags accurately without jumping
            $clean = $rawScript;

            // 1. Blade comments {{-- ... --}}
            $clean = preg_replace('/\{\{--.*?--\}\}/s', '', $clean);

            // 2. @json(...) -> {}
            $clean = preg_replace('/@json\s*\([^\)]*\)/', '{}', $clean);

            // 3. Quoted Blade tags: "foo {{ $x }} bar" or 'foo {{ $x }} bar'
            // We just replace {{ ... }} with a safe string literal fragment
            $clean = preg_replace('/\{\{[^}]*\}\}/', '___BLADE___', $clean);
            $clean = preg_replace('/\{!![^}]*!!\}/', '___BLADE___', $clean);

            // 4. Line directives: @if, @else, @endif, @foreach, etc.
            $clean = preg_replace('/^\s*@(if|elseif|else|endif|foreach|endforeach|forelse|endforelse|empty|isset|endisset|unless|endunless|switch|case|default|endswitch|php|endphp|push|endpush|stack|include|yield|section|endsection)\b.*$/m', '// blade-directive', $clean);

            // Test syntax with node --check
            $tmp = tempnam(sys_get_temp_dir(), 'jscheck_') . '.js';
            file_put_contents($tmp, $clean);
            $check = shell_exec("node --check \"$tmp\" 2>&1");
            unlink($tmp);

            if ($check && (str_contains($check, 'SyntaxError:') || str_contains($check, 'error:'))) {
                // Get error line inside temp file
                $errLine = 0;
                if (preg_match('/:(\d+)\b/', $check, $lm)) {
                    $errLine = (int)$lm[1];
                }
                $cleanLines = explode("\n", $clean);
                $snippet = $cleanLines[max(0, $errLine - 1)] ?? '';

                $realJsErrors[] = [
                    'file' => $relPath,
                    'script_line' => $lineNum,
                    'err_line' => $errLine,
                    'error' => trim(explode("\n", $check)[0]),
                    'snippet' => trim($snippet),
                    'full_check' => $check
                ];
            }
        }
    }
}

echo "Found " . count($realJsErrors) . " JS syntax issues.\n\n";
foreach ($realJsErrors as $err) {
    echo "FILE: {$err['file']} (around Blade line {$err['script_line']}, JS line {$err['err_line']})\n";
    echo "  Error: {$err['error']}\n";
    echo "  Snippet: {$err['snippet']}\n\n";
}
