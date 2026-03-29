<?php

$text = '```json
{"groups":[{"id":"students","name":"Test","support":67,"reaction":"Hi there"}]}
```';
$text = str_replace(['```json', '```'], '', $text);
$text = trim($text);
$start = strpos($text, '{');
$end = strrpos($text, '}');
$json = substr($text, $start, $end - $start + 1);
$decoded = json_decode($json, true);
print_r($decoded);
