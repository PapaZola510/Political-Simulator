<?php

$json = file_get_contents('C:/Users/Local Admin/Desktop/OpenCodePOL/storage/voter_raw.txt');
$decoded = json_decode($json, true);
echo 'Decode result: '.($decoded === null ? 'FAILED: '.json_last_error_msg() : 'OK')."\n";
if ($decoded) {
    echo 'Groups count: '.count($decoded['groups'] ?? [])."\n";
    echo 'First reaction: '.($decoded['groups'][0]['reaction'] ?? 'none')."\n";
}
