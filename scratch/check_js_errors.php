<?php

$files = [
    'resources/views/admin/detail-presensi.blade.php',
    'resources/views/admin/pengaturan-brand.blade.php',
    'resources/views/admin/pengaturan-divisi.blade.php',
    'resources/views/admin/pengaturan-project.blade.php',
];

foreach ($files as $file) {
    echo "=== Checking: {$file} ===\n";
    $content = file_get_contents($file);
    preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $content, $matches);
    foreach ($matches[1] as $idx => $script) {
        if (empty(trim($script))) continue;

        // check if type="application/json"
        if (preg_match('/<script[^>]*type=[\'"]application\/json[\'"]/i', $matches[0][$idx])) {
            continue;
        }

        $sanitized = preg_replace('/([\'"])\{\{.*?\}\}\1/s', '"__STR__"', $script);
        $sanitized = preg_replace('/\{\{.*?\}\}/s', '"__VAL__"', $sanitized);
        $sanitized = preg_replace('/@json\s*\(.*?\)/s', '{}', $sanitized);
        $sanitized = preg_replace('/@(if|elseif|else|endif|foreach|endforeach|php|endphp)\b.*?(\n|$)/', "// blade\n", $sanitized);

        $tmp = tempnam(sys_get_temp_dir(), 'test_') . '.js';
        file_put_contents($tmp, $sanitized);
        $res = shell_exec("node --check \"$tmp\" 2>&1");
        unlink($tmp);

        if ($res && (str_contains($res, 'SyntaxError:') || str_contains($res, 'error:'))) {
            echo "Script index {$idx}:\n";
            echo $res . "\n";
            // Also print lines around the error
            if (preg_match('/:(\d+)\b/', $res, $lineM)) {
                $errLine = (int)$lineM[1];
                $jsLines = explode("\n", $sanitized);
                $start = max(0, $errLine - 5);
                $end = min(count($jsLines), $errLine + 5);
                echo "Code around line {$errLine}:\n";
                for ($i = $start; $i < $end; $i++) {
                    $prefix = ($i + 1 == $errLine) ? " > " : "   ";
                    echo $prefix . ($i + 1) . ": " . ($jsLines[$i] ?? '') . "\n";
                }
            }
        }
    }
}
