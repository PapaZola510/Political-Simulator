<?php

use App\Models\Game;
use App\Services\GameEngine;

chdir('C:/Users/Local Admin/Desktop/OpenCodePOL');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$g = Game::latest()->first();
$t = $g->turns()->first();

// Get the stored data
$stored = $t->voter_reactions;

echo 'Stored reactions count: '.(is_array($stored) ? count($stored) : 0)."\n";
echo 'First stored reaction: '.($stored[0]['reaction'] ?? 'none')."\n\n";

// If empty, generate fresh
if (! is_array($stored) || count($stored) === 0) {
    echo "Generating fresh...\n";
    $engine = app(GameEngine::class);
    $new = $engine->generateVoterReactionsOnDemand($g, $t);
    $t->update(['voter_reactions' => $new]);
    echo 'New first: '.($new[0]['reaction'] ?? 'none')."\n";

    // Check new raw
    $newStored = $t->fresh()->voter_reactions;
    echo 'After save: '.($newStored[0]['reaction'] ?? 'none')."\n";
} else {
    echo "Data already exists - not regenerating\n";
}
