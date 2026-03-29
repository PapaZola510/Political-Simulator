<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\GameEngine;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(private readonly GameEngine $engine) {}

    public function index()
    {
        $saves = Game::latest()->take(12)->get()->map(function (Game $game) {
            [$monthName, $year] = $this->gameDate($game->turn_number);
            $game->date_label = "{$monthName} {$year}";

            return $game;
        });

        return view('game.index', [
            'saves' => $saves,
        ]);
    }

    public function customPresident()
    {
        return view('game.custom_president');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'preset' => ['nullable', 'in:Biden,Harris,AOC,Newsom,Trump,Vance,DeSantis,Haley,Custom'],
            'president_name' => ['required', 'string', 'max:80'],
            'president_party' => ['required', 'string', 'max:80'],
            'ideology' => ['nullable', 'string', 'max:80'],
            'age' => ['nullable', 'string', 'max:20'],
            'background' => ['nullable', 'string', 'max:80'],
            'gender' => ['nullable', 'string', 'max:20'],
            'party_support_hint' => ['nullable', 'string', 'max:80'],
        ]);

        if (($data['preset'] ?? null) !== 'Custom') {
            $presetMap = [
                'Biden'    => ['Joe Biden',                'Democratic'],
                'Harris'   => ['Kamala Harris',            'Democratic'],
                'AOC'      => ['Alexandria Ocasio-Cortez', 'Democratic'],
                'Newsom'   => ['Gavin Newsom',             'Democratic'],
                'Trump'    => ['Donald Trump',             'Republican'],
                'Vance'    => ['JD Vance',                 'Republican'],
                'DeSantis' => ['Ron DeSantis',             'Republican'],
                'Haley'    => ['Nikki Haley',              'Republican'],
            ];
            if (isset($presetMap[$data['preset'] ?? ''])) {
                [$data['president_name'], $data['president_party']] = $presetMap[$data['preset']];
            }
        }

        $game = $this->engine->startGame($data);
        session(['current_game_id' => $game->id]);

        return redirect()->route('game.dashboard', $game);
    }

    public function load(Game $game)
    {
        session(['current_game_id' => $game->id]);

        return redirect()->route('game.dashboard', $game);
    }

    public function destroy(Game $game)
    {
        $game->delete();

        return redirect()->route('game.index')->with('saved', 'Save deleted.');
    }

    public function continue()
    {
        $gameId = session('current_game_id');
        $game = $gameId ? Game::find($gameId) : null;

        if (! $game) {
            return redirect()->route('game.index');
        }

        return redirect()->route('game.dashboard', $game);
    }

    public function dashboard(Game $game)
    {
        if ($game->status === 'lost') {
            return redirect()->route('game.ended', $game);
        }

        $midtermPopup = $game->status === 'won';

        if (! $midtermPopup) {
            $this->engine->prepareNextCrisis($game->fresh());
        }
        $game = $game->fresh();
        $latestTurn = $game->turns()->latest('turn_number')->first();

        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $monthIndex = $game->turn_number % 12;
        $year = 2025 + intdiv($game->turn_number, 12);

        $profiles = [
            'Biden' => [
                'gender' => 'Male',
                'ideology' => 'Traditional Democrat',
                'age' => '80s',
                'background' => 'Senator',
                'home_region' => 'East Coast',
            ],
            'Harris' => [
                'gender' => 'Female',
                'ideology' => 'Progressive Democrat',
                'age' => '60s',
                'background' => 'Attorney General / Senator',
                'home_region' => 'West Coast',
            ],
            'AOC' => [
                'gender' => 'Female',
                'ideology' => 'Democratic Socialist',
                'age' => '30s',
                'background' => 'Community Organizer / Representative',
                'home_region' => 'Northeast',
            ],
            'Newsom' => [
                'gender' => 'Male',
                'ideology' => 'Progressive Democrat',
                'age' => '50s',
                'background' => 'Governor',
                'home_region' => 'West Coast',
            ],
            'Trump' => [
                'gender' => 'Male',
                'ideology' => 'Hardline Republican',
                'age' => '70s',
                'background' => 'Business',
                'home_region' => 'East Coast',
            ],
            'Vance' => [
                'gender' => 'Male',
                'ideology' => 'MAGA Republican',
                'age' => '40s',
                'background' => 'Author / Senator',
                'home_region' => 'Midwest',
            ],
            'DeSantis' => [
                'gender' => 'Male',
                'ideology' => 'Conservative Republican',
                'age' => '40s',
                'background' => 'Governor',
                'home_region' => 'South',
            ],
            'Haley' => [
                'gender' => 'Female',
                'ideology' => 'Establishment Republican',
                'age' => '50s',
                'background' => 'Governor / UN Ambassador',
                'home_region' => 'South',
            ],
        ];
        $baseProfile = $profiles[$game->preset ?? ''] ?? [
            'gender' => 'Unknown',
            'ideology' => 'Independent',
            'age' => 'Unknown',
            'background' => 'Public Service',
            'home_region' => 'National',
        ];

        $supportStrength = match (true) {
            $game->party_support >= 70 => 'Landslide',
            $game->party_support >= 60 => 'Comfortable',
            $game->party_support >= 50 => 'Razor-Thin',
            default => 'Electoral Weakness',
        };

        // Build dynamic electoral state data by applying cumulative state reaction adjustments
        $abbrToFips = ['AL'=>'01','AK'=>'02','AZ'=>'04','AR'=>'05','CA'=>'06','CO'=>'08','CT'=>'09','DE'=>'10','DC'=>'11','FL'=>'12','GA'=>'13','HI'=>'15','ID'=>'16','IL'=>'17','IN'=>'18','IA'=>'19','KS'=>'20','KY'=>'21','LA'=>'22','ME'=>'23','MD'=>'24','MA'=>'25','MI'=>'26','MN'=>'27','MS'=>'28','MO'=>'29','MT'=>'30','NE'=>'31','NV'=>'32','NH'=>'33','NJ'=>'34','NM'=>'35','NY'=>'36','NC'=>'37','ND'=>'38','OH'=>'39','OK'=>'40','OR'=>'41','PA'=>'42','RI'=>'44','SC'=>'45','SD'=>'46','TN'=>'47','TX'=>'48','UT'=>'49','VT'=>'50','VA'=>'51','WA'=>'53','WV'=>'54','WI'=>'55','WY'=>'56'];
        $baselineStateData = $stateElectoralData = ['01'=>['votes'=>9,'dem'=>35,'rep'=>65],'02'=>['votes'=>3,'dem'=>40,'rep'=>60],'04'=>['votes'=>11,'dem'=>49,'rep'=>51],'05'=>['votes'=>6,'dem'=>34,'rep'=>66],'06'=>['votes'=>54,'dem'=>62,'rep'=>38],'08'=>['votes'=>10,'dem'=>56,'rep'=>44],'09'=>['votes'=>7,'dem'=>58,'rep'=>42],'10'=>['votes'=>3,'dem'=>57,'rep'=>43],'11'=>['votes'=>3,'dem'=>90,'rep'=>10],'12'=>['votes'=>30,'dem'=>46,'rep'=>54],'13'=>['votes'=>16,'dem'=>49,'rep'=>51],'15'=>['votes'=>4,'dem'=>65,'rep'=>35],'16'=>['votes'=>4,'dem'=>32,'rep'=>68],'17'=>['votes'=>19,'dem'=>60,'rep'=>40],'18'=>['votes'=>11,'dem'=>39,'rep'=>61],'19'=>['votes'=>6,'dem'=>44,'rep'=>56],'20'=>['votes'=>6,'dem'=>38,'rep'=>62],'21'=>['votes'=>8,'dem'=>33,'rep'=>67],'22'=>['votes'=>8,'dem'=>38,'rep'=>62],'23'=>['votes'=>4,'dem'=>52,'rep'=>48],'24'=>['votes'=>10,'dem'=>65,'rep'=>35],'25'=>['votes'=>11,'dem'=>66,'rep'=>34],'26'=>['votes'=>15,'dem'=>50,'rep'=>50],'27'=>['votes'=>10,'dem'=>52,'rep'=>48],'28'=>['votes'=>6,'dem'=>37,'rep'=>63],'29'=>['votes'=>10,'dem'=>41,'rep'=>59],'30'=>['votes'=>4,'dem'=>40,'rep'=>60],'31'=>['votes'=>5,'dem'=>39,'rep'=>61],'32'=>['votes'=>6,'dem'=>50,'rep'=>50],'33'=>['votes'=>4,'dem'=>52,'rep'=>48],'34'=>['votes'=>14,'dem'=>58,'rep'=>42],'35'=>['votes'=>5,'dem'=>54,'rep'=>46],'36'=>['votes'=>28,'dem'=>61,'rep'=>39],'37'=>['votes'=>16,'dem'=>48,'rep'=>52],'38'=>['votes'=>3,'dem'=>30,'rep'=>70],'39'=>['votes'=>17,'dem'=>44,'rep'=>56],'40'=>['votes'=>7,'dem'=>32,'rep'=>68],'41'=>['votes'=>8,'dem'=>56,'rep'=>44],'42'=>['votes'=>19,'dem'=>50,'rep'=>50],'44'=>['votes'=>4,'dem'=>63,'rep'=>37],'45'=>['votes'=>9,'dem'=>43,'rep'=>57],'46'=>['votes'=>3,'dem'=>35,'rep'=>65],'47'=>['votes'=>11,'dem'=>36,'rep'=>64],'48'=>['votes'=>40,'dem'=>44,'rep'=>56],'49'=>['votes'=>6,'dem'=>35,'rep'=>65],'50'=>['votes'=>3,'dem'=>70,'rep'=>30],'51'=>['votes'=>13,'dem'=>53,'rep'=>47],'53'=>['votes'=>12,'dem'=>58,'rep'=>42],'54'=>['votes'=>4,'dem'=>25,'rep'=>75],'55'=>['votes'=>10,'dem'=>50,'rep'=>50],'56'=>['votes'=>3,'dem'=>25,'rep'=>75]];
        $bandAdjustments = ['Strongly Supports'=>3,'Supports'=>2,'Leans Support'=>1,'Neutral'=>0,'Leans Oppose'=>-1,'Opposes'=>-2,'Strongly Opposes'=>-3];
        $isRepublican = str_contains(strtolower($game->president_party), 'rep');

        $allTurns = $game->turns()->whereNotNull('state_reactions')->orderBy('turn_number')->get();
        foreach ($allTurns as $t) {
            $reactions = $t->state_reactions;
            if (! is_array($reactions)) {
                continue;
            }
            foreach ($reactions as $reaction) {
                $abbr = $reaction['abbr'] ?? null;
                $band = $reaction['band'] ?? 'Neutral';
                if (! $abbr || ! isset($abbrToFips[$abbr])) {
                    continue;
                }
                $fips = $abbrToFips[$abbr];
                if (! isset($stateElectoralData[$fips])) {
                    continue;
                }
                $adj = $bandAdjustments[$band] ?? 0;
                if ($adj === 0) {
                    continue;
                }
                if ($isRepublican) {
                    $newRep = min(100, max(0, $stateElectoralData[$fips]['rep'] + $adj));
                    $stateElectoralData[$fips]['rep'] = $newRep;
                    $stateElectoralData[$fips]['dem'] = 100 - $newRep;
                } else {
                    $newDem = min(100, max(0, $stateElectoralData[$fips]['dem'] + $adj));
                    $stateElectoralData[$fips]['dem'] = $newDem;
                    $stateElectoralData[$fips]['rep'] = 100 - $newDem;
                }
            }
        }

        // Cap each state's total drift to ±15% from its baseline — no state goes fully one-party
        foreach ($stateElectoralData as $fips => &$data) {
            $baseline = $baselineStateData[$fips];
            $newRep = min($baseline['rep'] + 15, max($baseline['rep'] - 15, $data['rep']));
            $data['rep'] = $newRep;
            $data['dem'] = 100 - $newRep;
        }
        unset($data);

        return view('game.dashboard', [
            'game' => $game,
            'latestTurn' => $latestTurn,
            'monthName' => $monthNames[$monthIndex],
            'year' => $year,
            'monthsUntilMidterm' => max(24 - $game->turn_number, 0),
            'profile' => array_merge($baseProfile, ['support_strength' => $supportStrength]),
            'stateElectoralData' => $stateElectoralData,
            'midtermPopup' => $midtermPopup,
        ]);
    }

    public function save(Game $game)
    {
        $game->touch();

        return redirect()->route('game.dashboard', $game)->with('saved', 'Game saved.');
    }

    public function situation(Game $game)
    {
        if ($game->status !== 'active') {
            return redirect()->route('game.dashboard', $game);
        }

        $this->engine->prepareNextCrisis($game->fresh());

        [$monthName, $year] = $this->gameDate($game->turn_number + 1);

        return view('game.situation', ['game' => $game->fresh(), 'monthName' => $monthName, 'year' => $year]);
    }

    public function decide(Request $request, Game $game)
    {
        if ($game->status !== 'active') {
            return redirect()->route('game.dashboard', $game);
        }

        $data = $request->validate([
            'custom_response' => ['required', 'string', 'max:500'],
        ]);

        $decision = trim($data['custom_response'] ?? '');
        if ($decision === '') {
            return back()->withErrors(['custom_response' => 'Type your response before submitting.']);
        }

        $usedCustom = filled($data['custom_response'] ?? null);
        $this->engine->applyDecision($game->fresh(), $decision, $usedCustom);

        $game = $game->fresh();
        if ($game->status === 'lost') {
            return redirect()->route('game.ended', $game);
        }

        return redirect()->route('game.news', $game);
    }

    public function news(Game $game)
    {
        $turn = $game->turns()->latest('turn_number')->first();
        if (! $turn) {
            return redirect()->route('game.dashboard', $game);
        }

        $newsPayload = null;
        if ($turn->news_payload && is_array($turn->news_payload) && count($turn->news_payload) > 0) {
            $newsPayload = $turn->news_payload;
        }

        if (empty($newsPayload)) {
            $newsPackage = $this->engine->generateNewsOnDemand($game, $turn);
            $newsPayload = $newsPackage;
            $turn->update([
                'news' => $newsPackage['outlets']['center']['headline'].': '.$newsPackage['outlets']['center']['body'],
                'news_payload' => $newsPackage,
            ]);
        }

        return view('game.news', [
            'game' => $game,
            'turn' => $turn,
            'newsPayload' => $newsPayload,
        ]);
    }

    public function stateOutlook(Game $game)
    {
        $turn = $game->turns()->latest('turn_number')->first();
        if (! $turn) {
            return redirect()->route('game.dashboard', $game);
        }

        $states = $turn->state_reactions;
        if (! $states) {
            $states = $this->engine->generateStateReactionsOnDemand($game, $turn);
            $turn->update(['state_reactions' => $states]);
        }

        usort($states, fn ($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));

        $bandCounts = [
            'Strongly Supports' => 0,
            'Supports' => 0,
            'Leans Support' => 0,
            'Neutral' => 0,
            'Leans Oppose' => 0,
            'Opposes' => 0,
            'Strongly Opposes' => 0,
        ];
        foreach ($states as $state) {
            $band = $state['band'] ?? 'Neutral';
            if (isset($bandCounts[$band])) {
                $bandCounts[$band]++;
            }
        }

        return view('game.state_outlook', [
            'game' => $game,
            'turn' => $turn,
            'stateReactions' => $turn->state_reactions ?? [],
            'topSupporters' => array_slice($states, 0, 5),
            'topOpposers' => array_slice(array_reverse($states), 0, 5),
            'bandCounts' => $bandCounts,
        ]);
    }

    public function voterReaction(Game $game)
    {
        $turn = $game->turns()->latest('turn_number')->first();
        if (! $turn) {
            return redirect()->route('game.dashboard', $game);
        }

        $voterReactions = null;
        if ($turn->voter_reactions && is_array($turn->voter_reactions) && count($turn->voter_reactions) > 0) {
            $voterReactions = $turn->voter_reactions;
        }

        if (empty($voterReactions)) {
            $voterReactions = $this->engine->generateVoterReactionsOnDemand($game, $turn);
            $turn->update(['voter_reactions' => $voterReactions]);
        }

        return view('game.voter_reaction', [
            'game' => $game,
            'turn' => $turn,
            'voterReactions' => $voterReactions,
        ]);
    }

    public function midterm(Game $game)
    {
        return view('game.midterm', ['game' => $game]);
    }

    public function ended(Game $game)
    {
        return view('game.ended', ['game' => $game]);
    }

    public function score(Request $request, Game $game)
    {
        // Preview mode: randomize stats in-memory so the score screen can be tested
        if ($request->boolean('preview')) {
            $game->approval      = rand(26, 90);
            $game->stability     = rand(26, 90);
            $game->party_support = rand(26, 90);
        }

        // Presidential rankings based on C-SPAN Historians Survey & major polling consensus
        $presidents = [
            ['rank' =>  1, 'name' => 'Abraham Lincoln',       'tier' => 'S'],
            ['rank' =>  2, 'name' => 'George Washington',     'tier' => 'S'],
            ['rank' =>  3, 'name' => 'Franklin D. Roosevelt', 'tier' => 'S'],
            ['rank' =>  4, 'name' => 'Theodore Roosevelt',    'tier' => 'S'],
            ['rank' =>  5, 'name' => 'Dwight D. Eisenhower',  'tier' => 'S'],
            ['rank' =>  6, 'name' => 'Harry S. Truman',       'tier' => 'A'],
            ['rank' =>  7, 'name' => 'Thomas Jefferson',      'tier' => 'A'],
            ['rank' =>  8, 'name' => 'John F. Kennedy',       'tier' => 'A'],
            ['rank' =>  9, 'name' => 'Ronald Reagan',         'tier' => 'A'],
            ['rank' => 10, 'name' => 'Barack Obama',          'tier' => 'A'],
            ['rank' => 11, 'name' => 'Lyndon B. Johnson',     'tier' => 'A'],
            ['rank' => 12, 'name' => 'James K. Polk',         'tier' => 'A'],
            ['rank' => 13, 'name' => 'Ulysses S. Grant',      'tier' => 'B'],
            ['rank' => 14, 'name' => 'James Monroe',          'tier' => 'B'],
            ['rank' => 15, 'name' => 'James Madison',         'tier' => 'B'],
            ['rank' => 16, 'name' => 'Woodrow Wilson',        'tier' => 'B'],
            ['rank' => 17, 'name' => 'Grover Cleveland',      'tier' => 'B'],
            ['rank' => 18, 'name' => 'William McKinley',      'tier' => 'B'],
            ['rank' => 19, 'name' => 'John Adams',            'tier' => 'B'],
            ['rank' => 20, 'name' => 'George H.W. Bush',      'tier' => 'B'],
            ['rank' => 21, 'name' => 'Jimmy Carter',          'tier' => 'B'],
            ['rank' => 22, 'name' => 'Gerald Ford',           'tier' => 'B'],
            ['rank' => 23, 'name' => 'Bill Clinton',          'tier' => 'C'],
            ['rank' => 24, 'name' => 'John Q. Adams',         'tier' => 'C'],
            ['rank' => 25, 'name' => 'Martin Van Buren',      'tier' => 'C'],
            ['rank' => 26, 'name' => 'George W. Bush',        'tier' => 'C'],
            ['rank' => 27, 'name' => 'Rutherford B. Hayes',   'tier' => 'C'],
            ['rank' => 28, 'name' => 'Chester A. Arthur',     'tier' => 'C'],
            ['rank' => 29, 'name' => 'William H. Taft',       'tier' => 'C'],
            ['rank' => 30, 'name' => 'Calvin Coolidge',       'tier' => 'C'],
            ['rank' => 31, 'name' => 'James A. Garfield',     'tier' => 'C'],
            ['rank' => 32, 'name' => 'Zachary Taylor',        'tier' => 'C'],
            ['rank' => 33, 'name' => 'Benjamin Harrison',     'tier' => 'D'],
            ['rank' => 34, 'name' => 'John Tyler',            'tier' => 'D'],
            ['rank' => 35, 'name' => 'Richard Nixon',         'tier' => 'D'],
            ['rank' => 36, 'name' => 'Millard Fillmore',      'tier' => 'D'],
            ['rank' => 37, 'name' => 'William H. Harrison',   'tier' => 'D'],
            ['rank' => 38, 'name' => 'Herbert Hoover',        'tier' => 'D'],
            ['rank' => 39, 'name' => 'Andrew Johnson',        'tier' => 'F'],
            ['rank' => 40, 'name' => 'Franklin Pierce',       'tier' => 'F'],
            ['rank' => 41, 'name' => 'Donald Trump',          'tier' => 'F'],
            ['rank' => 42, 'name' => 'James Buchanan',        'tier' => 'F'],
            ['rank' => 43, 'name' => 'Warren G. Harding',     'tier' => 'F'],
        ];

        $tierMeta = [
            'S' => ['label' => 'The Greats',               'badge' => 'bg-amber-400 text-amber-900',   'ring' => 'ring-amber-400',  'bg' => 'bg-amber-50 dark:bg-amber-900/20'],
            'A' => ['label' => 'Near Great',               'badge' => 'bg-green-500 text-white',       'ring' => 'ring-green-400',  'bg' => 'bg-green-50 dark:bg-green-900/20'],
            'B' => ['label' => 'Above Average',            'badge' => 'bg-blue-500 text-white',        'ring' => 'ring-blue-400',   'bg' => 'bg-blue-50 dark:bg-blue-900/20'],
            'C' => ['label' => 'Average',                  'badge' => 'bg-slate-500 text-white',       'ring' => 'ring-slate-400',  'bg' => 'bg-slate-100 dark:bg-slate-800'],
            'D' => ['label' => 'Below Average',            'badge' => 'bg-orange-500 text-white',      'ring' => 'ring-orange-400', 'bg' => 'bg-orange-50 dark:bg-orange-900/20'],
            'F' => ['label' => 'Poor / Failed Presidency', 'badge' => 'bg-red-600 text-white',         'ring' => 'ring-red-500',    'bg' => 'bg-red-50 dark:bg-red-900/20'],
        ];

        // Weighted presidency score (approval matters most, then stability, then party)
        $score = ($game->approval * 0.4) + ($game->stability * 0.3) + ($game->party_support * 0.3);

        // Letter grade thresholds
        if      ($score >= 78) $grade = 'A+';
        elseif  ($score >= 72) $grade = 'A';
        elseif  ($score >= 67) $grade = 'A-';
        elseif  ($score >= 62) $grade = 'B+';
        elseif  ($score >= 57) $grade = 'B';
        elseif  ($score >= 52) $grade = 'B-';
        elseif  ($score >= 47) $grade = 'C+';
        elseif  ($score >= 42) $grade = 'C';
        elseif  ($score >= 37) $grade = 'C-';
        elseif  ($score >= 30) $grade = 'D';
        else                   $grade = 'F';

        $gradeDescriptions = [
            'A+' => 'Exceptional. A transformational presidency that will be studied for generations.',
            'A'  => 'Outstanding. Your leadership will be remembered as one of the truly great ones.',
            'A-' => 'Excellent. A strong and effective president who rose to every challenge.',
            'B+' => 'Very Good. A capable leader who navigated crises with skill and steadiness.',
            'B'  => 'Good. A solid presidency — more wins than losses, more clarity than chaos.',
            'B-' => 'Above Average. Competent leadership with some notable stumbles along the way.',
            'C+' => 'Adequate. A mixed record — for every high, a corresponding low.',
            'C'  => 'Mediocre. History will remember your term as largely unremarkable.',
            'C-' => 'Below Expectations. Your presidency struggled to find consistent footing.',
            'D'  => 'Poor. Your term will be remembered for failures and missed opportunities.',
            'F'  => 'Failed. History\'s verdict is harsh — your presidency joins the worst in the nation\'s history.',
        ];

        $gradeColors = [
            'A+' => ['text' => 'text-green-600 dark:text-green-400',  'bg' => 'bg-green-100 dark:bg-green-900/30',  'border' => 'border-green-400'],
            'A'  => ['text' => 'text-green-600 dark:text-green-400',  'bg' => 'bg-green-100 dark:bg-green-900/30',  'border' => 'border-green-400'],
            'A-' => ['text' => 'text-green-600 dark:text-green-400',  'bg' => 'bg-green-100 dark:bg-green-900/30',  'border' => 'border-green-400'],
            'B+' => ['text' => 'text-blue-600 dark:text-blue-400',    'bg' => 'bg-blue-100 dark:bg-blue-900/30',    'border' => 'border-blue-400'],
            'B'  => ['text' => 'text-blue-600 dark:text-blue-400',    'bg' => 'bg-blue-100 dark:bg-blue-900/30',    'border' => 'border-blue-400'],
            'B-' => ['text' => 'text-blue-600 dark:text-blue-400',    'bg' => 'bg-blue-100 dark:bg-blue-900/30',    'border' => 'border-blue-400'],
            'C+' => ['text' => 'text-amber-600 dark:text-amber-400',  'bg' => 'bg-amber-100 dark:bg-amber-900/30', 'border' => 'border-amber-400'],
            'C'  => ['text' => 'text-amber-600 dark:text-amber-400',  'bg' => 'bg-amber-100 dark:bg-amber-900/30', 'border' => 'border-amber-400'],
            'C-' => ['text' => 'text-amber-600 dark:text-amber-400',  'bg' => 'bg-amber-100 dark:bg-amber-900/30', 'border' => 'border-amber-400'],
            'D'  => ['text' => 'text-orange-600 dark:text-orange-400','bg' => 'bg-orange-100 dark:bg-orange-900/30','border' => 'border-orange-400'],
            'F'  => ['text' => 'text-red-600 dark:text-red-400',      'bg' => 'bg-red-100 dark:bg-red-900/30',     'border' => 'border-red-500'],
        ];

        // Map score (27–78) to historical rank (43–1). Deterministic — same score always gives same rank.
        $clampedScore = (float) max(27.0, min(78.0, $score));
        $normalized   = ($clampedScore - 27.0) / (78.0 - 27.0);
        $playerRank   = (int) round(43 - ($normalized * 42));
        $playerRank   = max(1, min(43, $playerRank));

        // Player's tier by rank boundary
        if      ($playerRank <=  5) $playerTier = 'S';
        elseif  ($playerRank <= 12) $playerTier = 'A';
        elseif  ($playerRank <= 22) $playerTier = 'B';
        elseif  ($playerRank <= 32) $playerTier = 'C';
        elseif  ($playerRank <= 38) $playerTier = 'D';
        else                        $playerTier = 'F';

        // Group presidents by tier for Box 1
        $tierOrder     = ['S', 'A', 'B', 'C', 'D', 'F'];
        $groupedByTier = [];
        foreach ($presidents as $p) {
            $groupedByTier[$p['tier']][] = $p['name'];
        }

        // Visible tiers: player's tier ± 1
        $playerTierIndex = (int) array_search($playerTier, $tierOrder);
        $visibleTierKeys = [];
        if ($playerTierIndex > 0)                          $visibleTierKeys[] = $tierOrder[$playerTierIndex - 1];
        $visibleTierKeys[] = $playerTier;
        if ($playerTierIndex < count($tierOrder) - 1)     $visibleTierKeys[] = $tierOrder[$playerTierIndex + 1];

        // Build merged list (player inserted at $playerRank, historical presidents shift down)
        $allEntries = [];
        foreach ($presidents as $p) {
            $newRank = $p['rank'] >= $playerRank ? $p['rank'] + 1 : $p['rank'];
            $allEntries[$newRank] = ['rank' => $newRank, 'name' => $p['name'], 'isPlayer' => false];
        }
        $allEntries[$playerRank] = ['rank' => $playerRank, 'name' => $game->president_name, 'isPlayer' => true];
        ksort($allEntries);
        $allEntries = array_values($allEntries);

        // Find player's index in the merged list
        $playerIdx = 0;
        foreach ($allEntries as $idx => $entry) {
            if ($entry['isPlayer']) { $playerIdx = $idx; break; }
        }

        // Slice a window of 5 centered on player (handle edges)
        $start = max(0, $playerIdx - 2);
        $end   = min(count($allEntries) - 1, $playerIdx + 2);
        if ($playerIdx - $start < 2) $end   = min(count($allEntries) - 1, $start + 4);
        if ($end - $playerIdx   < 2) $start = max(0, $end - 4);
        $window = array_slice($allEntries, $start, 5);

        // Ordinal suffix for rank display
        $n      = $playerRank;
        $suffix = ($n % 100 >= 11 && $n % 100 <= 13)
            ? 'th'
            : ['th','st','nd','rd','th','th','th','th','th','th'][$n % 10];
        $ordinal = $n . $suffix;

        return view('game.score', compact(
            'game', 'grade', 'gradeDescriptions', 'gradeColors',
            'playerRank', 'ordinal', 'playerTier', 'tierMeta',
            'tierOrder', 'groupedByTier', 'visibleTierKeys', 'window'
        ));
    }

    private function gameDate(int $turnNumber): array
    {
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $monthIndex = $turnNumber % 12;
        $year = 2025 + intdiv($turnNumber, 12);

        return [$monthNames[$monthIndex], $year];
    }
}
