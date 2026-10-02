<?php

function traceTags($file) {
    echo "=== Tracing tags in {$file} ===\n";
    $content = file_get_contents($file);
    // remove comments and script
    $clean = preg_replace('/\{\{--.*?--\}\}/s', '', $content);
    $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $clean);

    $lines = explode("\n", $clean);
    $stack = [];

    foreach ($lines as $lineNum => $line) {
        // match tags
        preg_match_all('/(<\/?(div|section|form|table|tbody|thead|tr|td|th)\b[^>]*>)/i', $line, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $match) {
            $tagStr = $match[0];
            $isClose = str_starts_with($tagStr, '</');
            preg_match('/<\/?([a-zA-Z0-9]+)/', $tagStr, $tm);
            $tagName = strtolower($tm[1]);

            if ($isClose) {
                if (empty($stack)) {
                    echo "EXTRA CLOSING </{$tagName}> at Line " . ($lineNum + 1) . ": {$tagStr}\n";
                } else {
                    $top = array_pop($stack);
                    if ($top['tag'] !== $tagName) {
                        echo "TAG MISMATCH at Line " . ($lineNum + 1) . ": got </{$tagName}>, expected </{$top['tag']}> (opened at Line {$top['line']})\n";
                    }
                }
            } else {
                // Check self-closing
                if (!str_ends_with($tagStr, '/>')) {
                    $stack[] = ['tag' => $tagName, 'line' => $lineNum + 1, 'str' => $tagStr];
                }
            }
        }
    }

    if (!empty($stack)) {
        echo "UNCLOSED TAGS at end of file:\n";
        foreach ($stack as $unclosed) {
            echo "  <{$unclosed['tag']}> from Line {$unclosed['line']}: " . substr(trim($unclosed['str']), 0, 60) . "\n";
        }
    } else {
        echo "All tags perfectly matched!\n";
    }
    echo "\n";
}

traceTags('resources/views/login.blade.php');
traceTags('resources/views/register.blade.php');
traceTags('resources/views/users/layouts/main.blade.php');
traceTags('resources/views/admin/Outsiders/create.blade.php');
traceTags('resources/views/admin/Outsiders/edit.blade.php');
traceTags('resources/views/admin/table-user.blade.php');
