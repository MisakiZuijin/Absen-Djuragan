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

    // 1. Remove comments
    $clean = preg_replace('/\{\{--.*?--\}\}/s', '', $content);
    // 2. Remove script and style blocks
    $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $clean);
    $clean = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $clean);
    // 3. Remove php blocks
    $clean = preg_replace('/@php.*?@endphp/s', '', $clean);
    // 4. Tokenize tags
    // Void / self-closing HTML tags
    $voidTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

    // Match all HTML tags: <tag ...> or </tag>
    preg_match_all('/<(\/)?([a-zA-Z0-9\-]+)(?:\s+[^>]*?)?(\/)?>/s', $clean, $matches, PREG_OFFSET_CAPTURE);

    $stack = [];
    $errors = [];

    foreach ($matches[0] as $idx => $m) {
        $fullTag = $m[0];
        $offset = $m[1];
        $line = substr_count(substr($clean, 0, $offset), "\n") + 1;
        $isClosing = !empty($matches[1][$idx][0]);
        $tagName = strtolower($matches[2][$idx][0]);
        $isSelfClosing = !empty($matches[3][$idx][0]) || in_array($tagName, $voidTags);

        // Skip blade component tags like <x-alert /> or wire:ignore tags that might be custom
        // Only check standard HTML tags that structure the page:
        $structuralTags = ['div', 'section', 'main', 'header', 'footer', 'nav', 'article', 'aside', 'form', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'ul', 'ol', 'li', 'select', 'button'];

        if (!in_array($tagName, $structuralTags)) {
            continue;
        }

        if ($isSelfClosing) {
            continue;
        }

        if ($isClosing) {
            if (empty($stack)) {
                $errors[] = [
                    'line' => $line,
                    'msg' => "Extra closing </{$tagName}> with no opening tag."
                ];
            } else {
                $top = array_pop($stack);
                if ($top['tag'] !== $tagName) {
                    $errors[] = [
                        'line' => $line,
                        'msg' => "Mismatched closing tag: expected </{$top['tag']}> (opened line {$top['line']}), found </{$tagName}>."
                    ];
                }
            }
        } else {
            $stack[] = ['tag' => $tagName, 'line' => $line, 'tagStr' => substr(trim($fullTag), 0, 50)];
        }
    }

    if (!empty($stack)) {
        foreach ($stack as $unclosed) {
            $errors[] = [
                'line' => $unclosed['line'],
                'msg' => "Unclosed <{$unclosed['tag']}> from line {$unclosed['line']}: {$unclosed['tagStr']}"
            ];
        }
    }

    if (!empty($errors)) {
        $results[$rel] = $errors;
    }
}

echo "DOM Validation complete.\n";
echo "Files with structural HTML tag errors: " . count($results) . "\n\n";

foreach ($results as $f => $errs) {
    echo "========================================================\n";
    echo "FILE: {$f}\n";
    echo "========================================================\n";
    foreach ($errs as $e) {
        echo "  [Line {$e['line']}] {$e['msg']}\n";
    }
    echo "\n";
}
