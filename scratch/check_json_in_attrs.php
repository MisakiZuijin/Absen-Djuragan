<?php

$viewsDir = __DIR__ . '/../resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$results = [];

foreach ($iterator as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $filePath = $file->getPathname();
    $rel = str_replace(realpath($viewsDir) . DIRECTORY_SEPARATOR, '', $file->getRealPath());
    $content = file_get_contents($filePath);

    // Look for attr="{{ json_encode(...) }}" or attr='@json(...)'
    if (preg_match_all('/([@a-zA-Z0-9\-_:]+)\s*=\s*(["\'])(?:(?!\2).)*?(?:json_encode|@json)\b.*?\2/is', $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[0] as $idx => $m) {
            $offset = $m[1];
            $line = substr_count(substr($content, 0, $offset), "\n") + 1;
            $attr = $matches[1][$idx][0];
            $snippet = substr(trim($m[0]), 0, 100);

            $results[$rel][] = [
                'line' => $line,
                'attr' => $attr,
                'snippet' => $snippet
            ];
        }
    }
}

echo "Found " . count($results) . " files with json_encode inside HTML attributes:\n\n";
foreach ($results as $f => $items) {
    echo "FILE: {$f}\n";
    foreach ($items as $item) {
        echo "  Line {$item['line']} (attr: {$item['attr']}): {$item['snippet']}...\n";
    }
    echo "\n";
}
