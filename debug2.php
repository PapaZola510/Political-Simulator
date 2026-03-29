<?php

require 'C:/Users/Local Admin/Desktop/OpenCodePOL/vendor/autoload.php';

$app = require_once 'C:/Users/Local Admin/Desktop/OpenCodePOL/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$text = file_get_contents('C:/Users/Local Admin/Desktop/OpenCodePOL/storage/voter_raw.txt');

// Test decodeJsonFromText logic
$text = str_replace(['```json', '```'], ['', ''], $text);
$text = trim($text);

$start = strpos($text, '{');
$end = strrpos($text, '}');
$json = substr($text, $start, $end - $start + 1);

echo 'JSON length: '.strlen($json)."\n";

// Try basic decode
$decoded = json_decode($json, true);
echo 'Basic decode: '.($decoded === null ? 'FAILED' : 'OK')."\n";

if ($decoded === null) {
    // Find the duplicate pattern
    preg_match_all('/"support":\s*\d+/', $json, $matches);
    echo 'Support occurrences: '.count($matches[0])."\n";
    print_r($matches[0]);
}
