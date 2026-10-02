<?php

$viewsDir = __DIR__ . '/../resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$results = [];

foreach ($iterator as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $content = file_get_contents($file->getPathname());
    $rel = str_replace(realpath($viewsDir) . DIRECTORY_SEPARATOR, '', $file->getRealPath());

    // Remove comments
    $clean = preg_replace('/\{\{--.*?--\}\}/s', '', $content);

    // Look for <tag attr="... @(if|foreach|unless|isset|empty) ...">
    // Attribute can be class, style, href, src, id, etc.
    if (preg_match_all('/<[a-zA-Z0-9\-_]+(?:\s+[^>]*?)?\s+([a-zA-Z0-9\-_:]+)\s*=\s*(["\'])(?:(?!\2).)*?@(if|foreach|unless|isset|empty)\b.*?\2[^>]*>/is', $clean, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[0] as $idx => $m) {
            $offset = $m[1];
            $line = substr_count(substr($clean, 0, $offset), "\n") + 1;
            $attrName = $matches[1][$idx][0];
            $directive = $matches[3][$idx][0];
            $snippet = substr(trim($m[0]), 0, 140);

            $results[$rel][] = [
                'line' => $line,
                'attr' => $attrName,
                'directive' => $directive,
                'snippet' => $snippet
            ];
        }
    }
}

echo "Found " . count($results) . " files with Blade directives inside HTML attributes:\n\n";
foreach ($results as $f => $items) {
    echo "FILE: {$f}\n";
    foreach ($items as $item) {
        echo "  Line {$item['line']} (attr: {$item['attr']}, directive: @{$item['directive']}):\n";
        echo "    " . str_replace("\n", " ", $item['snippet']) . "...\n";
    }
    echo "\n";
}
