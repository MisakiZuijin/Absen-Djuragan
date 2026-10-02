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

    // Remove comments
    $clean = preg_replace('/\{\{--.*?--\}\}/s', '', $content);

    // Find all <label ... for="xxx" ...>
    preg_match_all('/<label\b[^>]*\bfor\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $clean, $labelMatches, PREG_OFFSET_CAPTURE);

    if (empty($labelMatches[1])) continue;

    // Find all id="..." in file
    preg_match_all('/\bid\s*=\s*["\']([^"\']+)["\']/i', $clean, $idMatches);
    $allIds = $idMatches[1];

    foreach ($labelMatches[1] as $idx => $m) {
        $forVal = $m[0];
        $offset = $m[1];
        $line = substr_count(substr($clean, 0, $offset), "\n") + 1;

        // Skip dynamic blade ids e.g. for="field_{{ $item->id }}" if id has matching pattern
        if (str_contains($forVal, '{{') || str_contains($forVal, '{!!')) {
            continue;
        }

        if (!in_array($forVal, $allIds)) {
            $results[$rel][] = [
                'line' => $line,
                'for' => $forVal,
                'tag' => trim($labelMatches[0][$idx][0])
            ];
        }
    }
}

echo "Found " . count($results) . " files with missing ID for <label for=...>\n\n";
foreach ($results as $f => $items) {
    echo "FILE: {$f}\n";
    foreach ($items as $item) {
        echo "  Line {$item['line']}: <label for=\"{$item['for']}\"> (no id=\"{$item['for']}\" in file)\n";
    }
    echo "\n";
}
