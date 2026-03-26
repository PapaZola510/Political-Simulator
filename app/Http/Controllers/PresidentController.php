<?php

namespace App\Http\Controllers;

use App\Models\President;
use App\Models\Game;
use App\Models\GameSave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class PresidentController extends Controller
{
    public function index()
    {
        return Inertia::render('president/index');
    }

    public function select(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:preset,custom',
            'preset' => 'required_if:type,preset',
            'name' => 'required_if:type,custom',
            'gender' => 'required_if:type,custom',
            'party' => 'required_if:type,custom',
            'age_group' => 'required_if:type,custom',
            'background' => 'required_if:type,custom',
            'home_region' => 'required_if:type,custom',
            'ideology' => 'required_if:type,custom',
            'support_strength' => 'required_if:type,custom',
        ]);

        $president = null;

        if ($validated['type'] === 'preset') {
            $president = $this->getPresetPresident($validated['preset']);
        } else {
            $president = President::create([
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'party' => $validated['party'],
                'age_group' => $validated['age_group'],
                'background' => $validated['background'],
                'home_region' => $validated['home_region'],
                'ideology' => $validated['ideology'],
                'support_strength' => $validated['support_strength'],
            ]);
        }

        Session::put('president', [
            'id' => $president->id,
            'name' => $president->name,
            'gender' => $president->gender,
            'party' => $president->party,
            'age_group' => $president->age_group,
            'background' => $president->background,
            'home_region' => $president->home_region,
            'ideology' => $president->ideology,
            'support_strength' => $president->support_strength,
            'voter_modifiers' => $president->getVoterModifiers(),
            'starting_stats' => $president->getStartingStats(),
        ]);

        $startingStats = $president->getStartingStats();
        
        // Deactivate all existing games for this session
        Game::where('session_id', Session::getId())->update(['is_active' => false]);
        
        // Create a new game for this president
        $game = Game::create([
            'session_id' => Session::getId(),
            'president_name' => $president->name,
            'president_party' => $president->party,
            'president_ideology' => $president->ideology,
            'current_turn' => 1,
            'current_month' => 1,
            'current_year' => 2025,
            'approval' => $startingStats['approval'] ?? 50,
            'stability' => $startingStats['stability'] ?? 50,
            'party_support' => $startingStats['party_support'] ?? 50,
            'current_phase' => 'dashboard',
            'is_active' => true,
            'used_events' => [],
        ]);

        // Auto-save the new game
        $autoSaveName = 'Auto Save - ' . $president->name . ' - ' . date('M j, Y');
        GameSave::create([
            'game_id' => $game->id,
            'save_name' => $autoSaveName,
            'state_snapshot' => [
                'turn' => 1,
                'month' => 1,
                'year' => 2025,
                'approval' => $startingStats['approval'] ?? 50,
                'stability' => $startingStats['stability'] ?? 50,
                'party_support' => $startingStats['party_support'] ?? 50,
                'phase' => 'dashboard',
                'used_events' => [],
            ],
        ]);

        Session::forget('game_state');

        return redirect('/');
    }

    protected function getPresetPresident(string $preset): President
    {
        return match($preset) {
            'biden' => President::create([
                'name' => 'Joe Biden',
                'gender' => 'male',
                'party' => 'democrat',
                'age_group' => '60-70',
                'background' => 'senator',
                'home_region' => 'east_coast',
                'ideology' => 'traditional',
                'support_strength' => 'comfortable',
            ]),
            'trump' => President::create([
                'name' => 'Donald Trump',
                'gender' => 'male',
                'party' => 'republican',
                'age_group' => '60-70',
                'background' => 'business',
                'home_region' => 'east_coast',
                'ideology' => 'hardcore',
                'support_strength' => 'landslide',
            ]),
            default => throw new \Exception('Unknown preset'),
        };
    }

    public function reset()
    {
        $game = Game::where('session_id', Session::getId())->where('is_active', true)->first();
        if ($game) {
            $game->update(['is_active' => false]);
        }
        Session::forget('president');
        Session::forget('game_state');
        return redirect('/president');
    }
}
