<?php

// Read raw file
$raw = file_get_contents('C:/Users/Local Admin/Desktop/OpenCodePOL/storage/voter_raw.txt');

// Clean: remove null bytes and other control characters
$clean = preg_replace('/[\x00-\x1F\x7F]/', '', $raw);

// Try decoding
$decoded = json_decode($clean, true);
echo 'Decode: '.($decoded === null ? 'FAILED: '.json_last_error_msg() : 'OK')."\n";

if ($decoded && isset($decoded['groups'])) {
    echo 'Groups: '.count($decoded['groups'])."\n";
    echo 'First reaction: '.substr($decoded['groups'][0]['reaction'] ?? '', 0, 100)."...\n";
}
