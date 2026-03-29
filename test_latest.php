<?php

$raw = file_get_contents('C:/Users/Local Admin/Desktop/OpenCodePOL/storage/voter_raw.txt');
$text = str_replace(['```json', '```'], ['', ''], $text);
$text = trim($raw);

// Find JSON bounds
$start = strpos($text, '{');
$end = strrpos($text, '}');
$json = substr($text, $start, $end - $start + 1);

// Clean control characters
$json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $json);

$decoded = json_decode($json, true);
echo 'Decode: '.($decoded === null ? 'FAILED - '.json_last_error_msg() : 'OK')."\n";

if ($decoded && isset($decoded['groups'])) {
    echo 'Groups: '.count($decoded['groups'])."\n";
    echo 'First: '.substr($decoded['groups'][0]['reaction'] ?? '', 0, 80)."...\n";
} else {
    echo "No groups in decoded\n";
}
