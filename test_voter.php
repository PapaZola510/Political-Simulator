<?php

use App\Models\Game;
use App\Services\GameEngine;

require 'C:/Users/Local Admin/Desktop/OpenCodePOL/vendor/autoload.php';

$app = require_once 'C:/Users/Local Admin/Desktop/OpenCodePOL/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$g = Game::latest()->first();
$t = $g->turns()->first();
$t->update(['voter_reactions' => null]);

$engine = app(GameEngine::class);
$new = $engine->generateVoterReactionsOnDemand($g, $t);

echo 'First reaction: '.($new[0]['reaction'] ?? 'none')."\n";
echo 'Total: '.count($new)."\n";
