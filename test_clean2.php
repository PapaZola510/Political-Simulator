<?php

$raw = file_get_contents('C:/Users/Local Admin/Desktop/OpenCodePOL/storage/voter_raw.txt');

// Force UTF-8
$utf8 = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');

// Remove control characters except newlines/tabs
$clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $utf8);

// Try decode with flags
$decoded = json_decode($clean, true, 512, JSON_INVALID_UTF8_IGNORE);
echo 'Decode: '.($decoded === null ? 'FAILED: '.json_last_error_msg() : 'OK')."\n";

if ($decoded && isset($decoded['groups'])) {
    echo 'Groups: '.count($decoded['groups'])."\n";
    echo 'First: '.substr($decoded['groups'][0]['reaction'] ?? '', 0, 80)."\n";
}

// Try different approach - extract manually
echo "\n--- Manual extraction ---\n";
if (preg_match_all('/\{"id":"([^"]+)","name":"([^"]+)","support":(\d+),"reaction":"([^"]+)"\}/i', $clean, $m, PREG_SET_ORDER)) {
    echo 'Matches: '.count($m)."\n";
    if (count($m) > 0) {
        echo 'First: '.$m[0][1].' - '.substr($m[0][3], 0, 60)."...\n";
    }
}
