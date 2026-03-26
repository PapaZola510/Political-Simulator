<?php

namespace App\Services;

use App\Models\Game;
use App\Models\PlayerDecision;
use App\Models\Consequence;
use App\Models\GameSave;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class GameService
{
    public function createGame(array $president): Game
    {
        return Game::create([
            'session_id' => Session::getId(),
            'president_name' => $president['name'] ?? 'Unknown',
            'president_party' => $president['party'] ?? 'independent',
            'president_ideology' => $president['ideology'] ?? 'moderate',
            'current_turn' => 1,
            'current_month' => 1,
            'current_year' => 2025,
            'approval' => $president['starting_stats']['approval'] ?? 50,
            'stability' => $president['starting_stats']['stability'] ?? 50,
            'party_support' => $president['starting_stats']['party_support'] ?? 50,
            'current_phase' => 'dashboard',
            'is_active' => true,
            'used_events' => [],
        ]);
    }

    public function getActiveGame(): ?Game
    {
        return Game::where('session_id', Session::getId())
            ->where('is_active', true)
            ->first();
    }

    public function saveDecision(Game $game, int $turn, string $scenarioTitle, string $decisionText, array $tags, array $statChanges): PlayerDecision
    {
        return PlayerDecision::create([
            'game_id' => $game->id,
            'turn_number' => $turn,
            'scenario_title' => $scenarioTitle,
            'decision_text' => $decisionText,
            'decision_tags' => $tags,
            'stat_changes' => $statChanges,
        ]);
    }

    public function getRecentDecisions(Game $game, int $count = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $game->decisions()
            ->orderBy('turn_number', 'desc')
            ->limit($count)
            ->get();
    }

    public function getDecisionTags(Game $game): array
    {
        $recentDecisions = $this->getRecentDecisions($game, 10);
        $allTags = [];
        
        foreach ($recentDecisions as $decision) {
            $tags = $decision->decision_tags ?? [];
            $allTags = array_merge($allTags, $tags);
        }
        
        return array_unique($allTags);
    }

    public function createConsequence(Game $game, string $title, string $description, int $turn, array $tags = []): Consequence
    {
        return Consequence::create([
            'game_id' => $game->id,
            'title' => $title,
            'description' => $description,
            'trigger_turn' => $turn,
            'is_resolved' => false,
            'is_shown' => false,
            'trigger_tags' => $tags,
        ]);
    }

    public function getPendingConsequences(Game $game): \Illuminate\Database\Eloquent\Collection
    {
        return $game->consequences()
            ->where('is_shown', false)
            ->where('is_resolved', false)
            ->orderBy('trigger_turn', 'asc')
            ->get();
    }

    public function markConsequenceShown(Consequence $consequence): void
    {
        $consequence->update(['is_shown' => true]);
    }

    public function markConsequenceResolved(Consequence $consequence): void
    {
        $consequence->update(['is_resolved' => true]);
    }

    public function updateGameState(Game $game, array $state): void
    {
        $game->update([
            'current_turn' => $state['turn'] ?? $game->current_turn,
            'current_month' => $state['month'] ?? $game->current_month,
            'current_year' => $state['year'] ?? $game->current_year,
            'approval' => $state['approval'] ?? $game->approval,
            'stability' => $state['stability'] ?? $game->stability,
            'party_support' => $state['party_support'] ?? $game->party_support,
            'current_phase' => $state['phase'] ?? $game->current_phase,
            'used_events' => $state['used_events'] ?? [],
        ]);
    }

    public function saveGame(Game $game, string $saveName, array $fullState): GameSave
    {
        return GameSave::create([
            'game_id' => $game->id,
            'save_name' => $saveName,
            'state_snapshot' => $fullState,
        ]);
    }

    public function loadGame(GameSave $save): array
    {
        return $save->state_snapshot;
    }

    public function endGame(Game $game): void
    {
        $game->update(['is_active' => false]);
    }

    public function hasRecentConsequence(Game $game, int $turn, int $withinTurns = 3): bool
    {
        return $game->consequences()
            ->where('trigger_turn', '>=', $turn - $withinTurns)
            ->exists();
    }
}
