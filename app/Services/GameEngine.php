<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Turn;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GameEngine
{
    private const STATES = [
        ['name' => 'Alabama', 'abbr' => 'AL'], ['name' => 'Alaska', 'abbr' => 'AK'], ['name' => 'Arizona', 'abbr' => 'AZ'], ['name' => 'Arkansas', 'abbr' => 'AR'],
        ['name' => 'California', 'abbr' => 'CA'], ['name' => 'Colorado', 'abbr' => 'CO'], ['name' => 'Connecticut', 'abbr' => 'CT'], ['name' => 'Delaware', 'abbr' => 'DE'],
        ['name' => 'Florida', 'abbr' => 'FL'], ['name' => 'Georgia', 'abbr' => 'GA'], ['name' => 'Hawaii', 'abbr' => 'HI'], ['name' => 'Idaho', 'abbr' => 'ID'],
        ['name' => 'Illinois', 'abbr' => 'IL'], ['name' => 'Indiana', 'abbr' => 'IN'], ['name' => 'Iowa', 'abbr' => 'IA'], ['name' => 'Kansas', 'abbr' => 'KS'],
        ['name' => 'Kentucky', 'abbr' => 'KY'], ['name' => 'Louisiana', 'abbr' => 'LA'], ['name' => 'Maine', 'abbr' => 'ME'], ['name' => 'Maryland', 'abbr' => 'MD'],
        ['name' => 'Massachusetts', 'abbr' => 'MA'], ['name' => 'Michigan', 'abbr' => 'MI'], ['name' => 'Minnesota', 'abbr' => 'MN'], ['name' => 'Mississippi', 'abbr' => 'MS'],
        ['name' => 'Missouri', 'abbr' => 'MO'], ['name' => 'Montana', 'abbr' => 'MT'], ['name' => 'Nebraska', 'abbr' => 'NE'], ['name' => 'Nevada', 'abbr' => 'NV'],
        ['name' => 'New Hampshire', 'abbr' => 'NH'], ['name' => 'New Jersey', 'abbr' => 'NJ'], ['name' => 'New Mexico', 'abbr' => 'NM'], ['name' => 'New York', 'abbr' => 'NY'],
        ['name' => 'North Carolina', 'abbr' => 'NC'], ['name' => 'North Dakota', 'abbr' => 'ND'], ['name' => 'Ohio', 'abbr' => 'OH'], ['name' => 'Oklahoma', 'abbr' => 'OK'],
        ['name' => 'Oregon', 'abbr' => 'OR'], ['name' => 'Pennsylvania', 'abbr' => 'PA'], ['name' => 'Rhode Island', 'abbr' => 'RI'], ['name' => 'South Carolina', 'abbr' => 'SC'],
        ['name' => 'South Dakota', 'abbr' => 'SD'], ['name' => 'Tennessee', 'abbr' => 'TN'], ['name' => 'Texas', 'abbr' => 'TX'], ['name' => 'Utah', 'abbr' => 'UT'],
        ['name' => 'Vermont', 'abbr' => 'VT'], ['name' => 'Virginia', 'abbr' => 'VA'], ['name' => 'Washington', 'abbr' => 'WA'], ['name' => 'West Virginia', 'abbr' => 'WV'],
        ['name' => 'Wisconsin', 'abbr' => 'WI'], ['name' => 'Wyoming', 'abbr' => 'WY'],
    ];

    private const SWING_STATES = ['PA', 'MI', 'AZ', 'GA', 'WI', 'NV', 'NC', 'NH', 'FL'];

    private const VOTER_GROUPS = [
        ['id' => 'students', 'name' => 'Student Activists', 'issues' => 'Education, climate, social issues, debt', 'emoji' => '🎓', 'pastel' => 'pink'],
        ['id' => 'yuppie', 'name' => 'Young Urban Professionals', 'issues' => 'Career, housing, economy, tech', 'emoji' => '💼', 'pastel' => 'purple'],
        ['id' => 'young_conservatives', 'name' => 'Young Conservatives', 'issues' => 'Limited government, free markets, guns, tradition', 'emoji' => '🛡️', 'pastel' => 'indigo'],
        ['id' => 'working_class', 'name' => 'Working-Class Urban Labor', 'issues' => 'Jobs, wages, unions, trade', 'emoji' => '🏭', 'pastel' => 'orange'],
        ['id' => 'suburban', 'name' => 'Suburban Families', 'issues' => 'Schools, safety, taxes, family', 'emoji' => '🏡', 'pastel' => 'teal'],
        ['id' => 'rural', 'name' => 'Rural Farmers', 'issues' => 'Farming, guns, religion, local communities', 'emoji' => '🌾', 'pastel' => 'green'],
        ['id' => 'small_business', 'name' => 'Small Business Owners', 'issues' => 'Regulations, taxes, customer spending', 'emoji' => '🏪', 'pastel' => 'amber'],
        ['id' => 'corporate', 'name' => 'Corporate Executives', 'issues' => 'Profits, markets, taxes, deregulation', 'emoji' => '📈', 'pastel' => 'gray'],
        ['id' => 'public_sector', 'name' => 'Public Sector Workers', 'issues' => 'Government funding, jobs, unions', 'emoji' => '🏛️', 'pastel' => 'cyan'],
        ['id' => 'retirees', 'name' => 'Retirees & Seniors', 'issues' => 'Healthcare, Social Security, stability', 'emoji' => '💗', 'pastel' => 'red'],
        ['id' => 'minorities', 'name' => 'Minority Communities', 'issues' => 'Equity, opportunity, discrimination', 'emoji' => '👥', 'pastel' => 'violet'],
        ['id' => 'independents', 'name' => 'Independent Voters', 'issues' => 'Pragmatism, results, not partisan', 'emoji' => '🧭', 'pastel' => 'slate'],
    ];

    private const CRISES = [
        ['title' => 'Border Security Surge', 'description' => 'A sharp spike in unauthorized crossings has overwhelmed border agents and shelters, with three border-state governors declaring emergencies and demanding immediate federal intervention.', 'options' => ['Deploy National Guard to border regions', 'Fast-track humanitarian asylum processing', 'Negotiate emergency relief package with border governors']],
        ['title' => 'Cyberattack on Energy Grid', 'description' => 'A sophisticated state-sponsored cyberattack has triggered rolling blackouts across six states, crippling hospitals, financial networks, and water treatment facilities for over 48 hours.', 'options' => ['Authorize a retaliatory offensive cyber strike', 'Declare national emergency and mobilize cyber defense units', 'Prioritize civilian infrastructure hardening and grid recovery']],
        ['title' => 'Supreme Court Flashpoint', 'description' => 'A landmark Supreme Court ruling on a deeply polarizing issue has ignited coast-to-coast protests, with counter-demonstrations turning violent in several cities and Congress under pressure to act.', 'options' => ['Call for bipartisan congressional dialogue', 'Push emergency federal legislation to counter the ruling', 'Address the nation directly and urge restraint']],
        ['title' => 'Global Oil Shock', 'description' => 'A sudden military conflict between major oil producers has disrupted global supply chains, driving gas prices to record highs and threatening recession as businesses and consumers struggle with rising costs.', 'options' => ['Release emergency strategic petroleum reserves', 'Subsidize domestic fuel and transport costs', 'Fast-track a national clean-energy emergency package']],
        ['title' => 'Federal Budget Standoff', 'description' => 'Partisan gridlock has pushed Congress to the edge of a government shutdown with 72 hours remaining, threatening furloughs for federal workers, suspended benefits, and a cascading economic shock.', 'options' => ['Offer targeted compromise budget cuts', 'Hold a firm hardline position to force the opposition', 'Launch a televised public pressure campaign against Congress']],
        ['title' => 'Pandemic Outbreak', 'description' => 'A fast-spreading respiratory illness has crossed into 14 states, overwhelming emergency rooms and prompting the CDC to issue its highest-level public health alert in a decade. Governors are demanding a federal response plan.', 'options' => ['Declare a federal public health emergency', 'Launch a nationwide voluntary vaccination and testing drive', 'Issue federal travel and workplace safety guidance']],
        ['title' => 'Mass Shooting Wave', 'description' => 'Three mass shootings in five days — at a school, a shopping center, and a place of worship — have left the nation in shock. Vigils are turning into protests demanding action, and Congress is under immense pressure.', 'options' => ['Push emergency gun safety legislation through Congress', 'Announce a national mental health and crisis intervention initiative', 'Defer to state and local law enforcement authority']],
        ['title' => 'Gulf Coast Catastrophe', 'description' => 'A Category 5 hurricane made landfall overnight, devastating coastal cities across three states. Hundreds are missing, critical infrastructure is destroyed, and FEMA is warning it cannot handle the scale without direct presidential direction.', 'options' => ['Deploy FEMA and National Guard in full emergency mode', 'Request immediate emergency supplemental funding from Congress', 'Coordinate a public-private disaster relief task force']],
        ['title' => 'Trade War Escalation', 'description' => 'A major trading partner has imposed sweeping retaliatory tariffs on American exports, hitting agriculture, manufacturing, and tech. Farm-state senators are furious, and markets are sliding amid fears of a prolonged trade war.', 'options' => ['Impose counter-tariffs to apply maximum economic pressure', 'Open direct emergency trade negotiations at the diplomatic level', 'Subsidize and protect the most-affected domestic industries']],
        ['title' => 'Allied Nation Under Attack', 'description' => 'A NATO-adjacent ally has been invaded by a regional power. The ally is formally requesting military assistance, allies in Europe are watching to see how you respond, and the UN Security Council is deadlocked.', 'options' => ['Deploy military advisors and expedite weapons transfers', 'Impose a sweeping sanctions package against the aggressor', 'Push for an immediate UN-mediated ceasefire and peace talks']],
        ['title' => 'Banking System Crisis', 'description' => 'Three major regional banks have collapsed in 48 hours, triggering a bank run and threatening a broader financial meltdown. Markets are in freefall and economists are warning of a potential repeat of 2008 without immediate action.', 'options' => ['Issue an emergency federal bailout and deposit guarantee', 'Allow market forces to determine which institutions survive', 'Appoint an emergency financial stabilization task force']],
        ['title' => 'Opioid and Fentanyl Surge', 'description' => 'Overdose deaths have hit a historic annual record, driven by a flood of synthetic fentanyl across border networks. Rural and suburban communities are hardest hit, and governors are calling for an immediate federal crackdown.', 'options' => ['Launch a law enforcement crackdown targeting trafficking networks', 'Expand federal funding for addiction treatment and recovery programs', 'Pursue a harm-reduction strategy with decriminalization of possession']],
        ['title' => 'AI and Automation Jobs Crisis', 'description' => 'A wave of AI-driven layoffs has displaced hundreds of thousands of workers in manufacturing, finance, and customer service in under six months. Labor unions are organizing a national strike and Congress is demanding the White House respond.', 'options' => ['Propose a federal retraining and transition assistance program', 'Push legislation requiring companies to offset automation with worker benefits', 'Call for a national AI development moratorium pending workforce review']],
        ['title' => 'Foreign Disinformation Campaign', 'description' => 'Intelligence agencies have confirmed that a foreign government is running a massive coordinated disinformation operation on social media, targeting the upcoming midterms and inflaming domestic divisions on race, immigration, and crime.', 'options' => ['Publicly attribute and sanction the foreign government responsible', 'Work with tech platforms on a quiet takedown and counter-narrative operation', 'Call a bipartisan national security briefing and propose election protection legislation']],
        ['title' => 'Space and Defense Technology Breach', 'description' => 'An adversary nation has demonstrated an advanced anti-satellite weapon, destroying one of America\'s key defense satellites in a test. Military officials say it fundamentally alters the strategic balance and demand an immediate presidential response.', 'options' => ['Accelerate emergency space defense and satellite replacement funding', 'Propose an international treaty banning anti-satellite weapons', 'Coordinate a classified allied intelligence-sharing and deterrence response']],
    ];


    public function startGame(array $payload): Game
    {
        $presetPartySupport = [
            'Trump'    => 65,
            'AOC'      => 65,
            'Harris'   => 60,
            'DeSantis' => 60,
            'Biden'    => 55,
            'Vance'    => 55,
            'Haley'    => 50,
            'Newsom'   => 50,
        ];

        $preset = $payload['preset'] ?? null;
        $partySupport = $presetPartySupport[$preset] ?? match ($payload['party_support_hint'] ?? null) {
            'Landslide' => 65,
            'Comfortable' => 60,
            'Razor-thin' => 55,
            default => 50,
        };

        $game = Game::create([
            'president_name' => $payload['president_name'],
            'president_party' => $payload['president_party'],
            'preset' => $payload['preset'] ?? null,
            'ideology' => $payload['ideology'] ?? null,
            'age' => $payload['age'] ?? null,
            'background' => $payload['background'] ?? null,
            'gender' => $payload['gender'] ?? null,
            'party_support_hint' => $payload['party_support_hint'] ?? null,
            'party_support' => $partySupport,
        ]);

        $this->prepareNextCrisis($game);

        return $game->fresh();
    }

    public function prepareNextCrisis(Game $game): void
    {
        if ($game->status !== 'active' || $game->active_crisis_title) {
            return;
        }

        $upcomingTurn = $game->turn_number + 1;
        $isZen = random_int(1, 100) <= 10;

        if ($isZen) {
            $game->update([
                'active_crisis_title' => 'Zen Month',
                'active_crisis_description' => 'No major crisis this month. You can either rest political capital or quietly build alliances.',
                'active_crisis_options' => ['Take a low-profile month', 'Hold unity town halls', 'Rebuild party relationships'],
                'last_turn_zen' => true,
            ]);

            return;
        }

        if ($upcomingTurn % 4 === 0) {
            $crisis = $this->generateConsequenceCrisis($game);
        } else {
            $crisis = Arr::random(self::CRISES);
        }

        $game->update([
            'active_crisis_title' => $crisis['title'],
            'active_crisis_description' => $crisis['description'],
            'active_crisis_options' => $crisis['options'],
            'last_turn_zen' => false,
        ]);
    }

    public function applyDecision(Game $game, string $decision, bool $usedCustomResponse): void
    {
        if ($game->status !== 'active' || ! $game->active_crisis_title) {
            return;
        }

        $turnNumber = $game->turn_number + 1;
        [$approvalDelta, $stabilityDelta, $partyDelta] = $this->calculateDeltasWithAI($game, $decision, $usedCustomResponse, $game->active_crisis_title === 'Zen Month');

        $approval = max(0, min(100, $game->approval + $this->applyDiminishingReturn($game->approval, $approvalDelta)));
        $stability = max(0, min(100, $game->stability + $this->applyDiminishingReturn($game->stability, $stabilityDelta)));
        $partySupport = max(0, min(100, $game->party_support + $this->applyDiminishingReturn($game->party_support, $partyDelta)));
        $pressure = max(0, $game->pressure_score + (abs($approvalDelta) + abs($stabilityDelta) + abs($partyDelta)));

        // Store the actual applied change so the UI displays what really happened
        $appliedApprovalDelta = $approval - $game->approval;
        $appliedStabilityDelta = $stability - $game->stability;
        $appliedPartyDelta = $partySupport - $game->party_support;

        Turn::create([
            'game_id' => $game->id,
            'turn_number' => $turnNumber,
            'crisis_title' => $game->active_crisis_title,
            'crisis_description' => $game->active_crisis_description,
            'decision' => $decision,
            'used_custom_response' => $usedCustomResponse,
            'is_zen_month' => $game->active_crisis_title === 'Zen Month',
            'approval_delta' => $appliedApprovalDelta,
            'stability_delta' => $appliedStabilityDelta,
            'party_support_delta' => $appliedPartyDelta,
        ]);

        $status = 'active';
        $lossReason = null;

        if ($approval <= 25) {
            $status = 'lost';
            $lossReason = 'Impeachment';
        } elseif ($stability <= 25) {
            $status = 'lost';
            $lossReason = 'Overthrown';
        } elseif ($partySupport <= 25) {
            $status = 'lost';
            $lossReason = '25th Amendment';
        } elseif ($turnNumber >= 24) {
            $status = 'won';
        }

        $game->update([
            'turn_number' => $turnNumber,
            'approval' => $approval,
            'stability' => $stability,
            'party_support' => $partySupport,
            'pressure_score' => $pressure,
            'status' => $status,
            'loss_reason' => $lossReason,
            'last_decision' => $decision,
            'active_crisis_title' => null,
            'active_crisis_description' => null,
            'active_crisis_options' => null,
        ]);

        if ($status === 'active') {
            $this->checkLowStatRepercussion($game->fresh());
            $this->prepareNextCrisis($game->fresh());
        }
    }

    private function checkLowStatRepercussion(Game $game): void
    {
        // Already has a crisis queued (e.g. consequence cycle) — don't override
        if ($game->active_crisis_title) {
            return;
        }

        // Need at least 3 turns to detect a streak
        $turns = $game->turns()->orderBy('turn_number', 'desc')->take(4)->get();
        if ($turns->count() < 3) {
            return;
        }

        // Reconstruct the stat value at the end of each of the last 3 turns
        // v[0] = end of current turn (now), v[1] = 1 turn ago, v[2] = 2 turns ago, v[3] = 3 turns ago
        $approval   = [$game->approval];
        $stability  = [$game->stability];
        $party      = [$game->party_support];

        foreach ($turns as $i => $turn) {
            $approval[]  = $approval[$i]  - $turn->approval_delta;
            $stability[] = $stability[$i] - $turn->stability_delta;
            $party[]     = $party[$i]     - $turn->party_support_delta;
        }

        // "Edge trigger": fire only when the 3rd consecutive ≤40 turn is reached,
        // not on every subsequent turn. Verified by checking that the 4th value was >40 (or absent).
        $fourthOk = fn (array $v) => ! isset($v[3]) || $v[3] > 40;

        $approvalHit  = $approval[0]  <= 40 && $approval[1]  <= 40 && $approval[2]  <= 40 && $fourthOk($approval);
        $stabilityHit = $stability[0] <= 40 && $stability[1] <= 40 && $stability[2] <= 40 && $fourthOk($stability);
        $partyHit     = $party[0]     <= 40 && $party[1]     <= 40 && $party[2]     <= 40 && $fourthOk($party);

        if (! $approvalHit && ! $stabilityHit && ! $partyHit) {
            return;
        }

        $repercussions = [];

        if ($approvalHit) {
            $repercussions[] = [
                'value' => $game->approval,
                'title' => 'Public Confidence Collapse',
                'description' => 'Three consecutive months of sustained low approval have triggered a crisis of public trust. Mass protests are forming outside the White House, major media outlets are calling for resignation, and congressional leaders from your own party are privately questioning your fitness to govern. The nation is watching.',
                'options' => [
                    'Hold a prime-time national address to reconnect with the public directly',
                    'Announce a bold new domestic policy initiative to shift the narrative',
                    'Reach across the aisle and propose a bipartisan unity commission',
                ],
            ];
        }

        if ($stabilityHit) {
            $repercussions[] = [
                'value' => $game->stability,
                'title' => 'Government Stability Crisis',
                'description' => 'Three months of deteriorating government stability has reached a breaking point. Senior cabinet officials are reportedly considering resignation, federal agencies are struggling to coordinate, and foreign governments are privately questioning America\'s institutional reliability. Emergency congressional oversight hearings have been called.',
                'options' => [
                    'Conduct an emergency cabinet reshuffle to restore institutional confidence',
                    'Issue an executive order centralizing crisis coordination under the White House',
                    'Request an emergency joint session of Congress to demonstrate unity and resolve',
                ],
            ];
        }

        if ($partyHit) {
            $repercussions[] = [
                'value' => $game->party_support,
                'title' => 'Party Revolt',
                'description' => 'Three months of weakening party support has fractured your political coalition. Senior party figures are openly distancing themselves from your agenda, key donors are withholding funds, and internal polling shows significant defections. Party leadership has demanded an emergency meeting to discuss your future.',
                'options' => [
                    'Meet privately with party leadership and recommit to the core party platform',
                    'Make a high-profile personnel change to signal renewed partisan alignment',
                    'Launch a grassroots party engagement tour across key states',
                ],
            ];
        }

        // If multiple stats are critical, surface the most severe (lowest value)
        usort($repercussions, fn ($a, $b) => $a['value'] <=> $b['value']);
        $crisis = $repercussions[0];

        $game->update([
            'active_crisis_title'       => $crisis['title'],
            'active_crisis_description' => $crisis['description'],
            'active_crisis_options'     => $crisis['options'],
            'last_turn_zen'             => false,
        ]);
    }

    private function generateConsequenceCrisis(Game $game): array
    {
        $recentTurns = $game->turns()
            ->orderBy('turn_number', 'desc')
            ->take(4)
            ->get(['turn_number', 'crisis_title', 'decision']);

        if ($recentTurns->isEmpty()) {
            return $this->generateFallbackConsequence('');
        }

        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            $decisionsText = $recentTurns->pluck('decision')->implode(' ');
            return $this->generateFallbackConsequence($decisionsText);
        }

        $decisionHistory = $recentTurns->map(function ($turn) {
            return "Turn {$turn->turn_number} — Crisis: \"{$turn->crisis_title}\" → President's decision: \"{$turn->decision}\"";
        })->implode("\n");

        $prompt = "You are a political consequence generator for a US presidential simulator.\n\n"
            . "Based on the president's recent decisions, generate ONE realistic consequence scenario that flows DIRECTLY from those choices.\n\n"
            . "Rules:\n"
            . "1. If the decision was a real policy choice (e.g. 'increase border security', 'deploy troops', 'cut taxes'), create a realistic downstream backlash or ripple effect — protests, labor shortages, legal challenges, diplomatic fallout, economic side effects, etc. Reference the SPECIFIC policy area.\n"
            . "2. If the decision was nonsensical, off-topic, incoherent, or clearly unserious (random words, gibberish, jokes, fictional references), generate a leadership credibility crisis: media, lawmakers, and allies openly question the president's fitness to govern.\n"
            . "3. The title should feel like a newspaper headline (5-8 words).\n"
            . "4. The description must be 2-3 vivid sentences, written in present tense, as if unfolding now. Name specific affected groups (workers, states, allies, agencies) and make it feel real.\n"
            . "5. Provide exactly 3 response options (short action phrases, 6-12 words each) appropriate to the scenario.\n"
            . "6. Return strict JSON only — no markdown, no explanation:\n"
            . "{\"title\": \"<headline>\", \"description\": \"<2-3 sentences>\", \"options\": [\"<opt1>\", \"<opt2>\", \"<opt3>\"]}\n\n"
            . "President's recent decisions (most recent first):\n"
            . $decisionHistory;

        try {
            $response = $this->callAnthropic([
                'model' => config('services.anthropic.model') ?: 'claude-sonnet-4-20250514',
                'max_tokens' => 512,
                'temperature' => 0.7,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if (! $response || ! $response->successful()) {
                $decisionsText = $recentTurns->pluck('decision')->implode(' ');
                return $this->generateFallbackConsequence($decisionsText);
            }

            $text = data_get($response->json(), 'content.0.text', '');
            $text = preg_replace('/```(?:json)?\s*([\s\S]*?)\s*```/', '$1', trim($text));
            $data = json_decode($text, true);

            if (
                ! is_array($data)
                || empty($data['title'])
                || empty($data['description'])
                || ! isset($data['options'])
                || ! is_array($data['options'])
                || count($data['options']) < 3
            ) {
                $decisionsText = $recentTurns->pluck('decision')->implode(' ');
                return $this->generateFallbackConsequence($decisionsText);
            }

            return [
                'title'       => 'Consequence: ' . $data['title'],
                'description' => $data['description'],
                'options'     => array_slice($data['options'], 0, 3),
            ];

        } catch (Throwable $e) {
            Log::warning('AI consequence crisis generation failed', ['error' => $e->getMessage()]);
            $decisionsText = $recentTurns->pluck('decision')->implode(' ');
            return $this->generateFallbackConsequence($decisionsText);
        }
    }

    private function generateFallbackConsequence(string $decisionsText): array
    {
        $lower = strtolower($decisionsText);

        // Nonsensical/empty input → leadership credibility crisis
        if (str_word_count($lower) < 4 || ! preg_match('/[a-z]{4,}/', $lower)) {
            return [
                'title'       => 'Consequence: Leadership Under the Microscope',
                'description' => 'A series of decisions that left lawmakers and commentators baffled has sparked a national conversation about the president\'s judgment. Cable news panels are running wall-to-wall coverage questioning the White House\'s competence, and senior party figures are privately demanding answers.',
                'options'     => [
                    'Hold a press conference and take questions directly from reporters',
                    'Announce a bold new initiative to redirect public attention',
                    'Meet privately with party leadership to shore up internal support',
                ],
            ];
        }

        // Border / immigration
        if (preg_match('/\b(border|immigr|migrant|asylum|crossing|deportat)\b/', $lower)) {
            return [
                'title'       => 'Consequence: Border Policy Ripple Effect',
                'description' => 'Your border enforcement decisions have ignited a wave of protests in major cities, with immigrant advocacy groups filing emergency injunctions in federal court. Agricultural and hospitality industries are simultaneously warning of critical labor shortages, and senators from both parties are demanding an urgent policy review.',
                'options'     => [
                    'Stand firm and authorize additional border enforcement resources',
                    'Order a humanitarian review of current processing procedures',
                    'Convene an emergency summit with border-state governors and industry leaders',
                ],
            ];
        }

        // Military / defense / war
        if (preg_match('/\b(militar|deploy|troop|weapon|defense|nato|war|strike|soldier|combat)\b/', $lower)) {
            return [
                'title'       => 'Consequence: Military Action Blowback',
                'description' => 'Anti-war demonstrations are spreading across a dozen cities in response to your recent military posture. Veteran groups are sharply divided, and key congressional leaders are demanding formal authorization hearings. Three allied governments have requested urgent consultations over the regional implications.',
                'options'     => [
                    'Brief congressional leaders and seek formal authorization',
                    'Issue a public statement clarifying scope and exit strategy',
                    'Propose an international diplomatic forum to reduce tensions',
                ],
            ];
        }

        // Economy / trade / taxes
        if (preg_match('/\b(tax|trade|tariff|econom|market|budget|deficit|spend|fiscal|gdp|wall street)\b/', $lower)) {
            return [
                'title'       => 'Consequence: Economic Policy Shockwave',
                'description' => 'Your recent economic decisions triggered a sharp market sell-off, with small business coalitions threatening a political backlash and union leaders demanding an emergency meeting at the White House. Wall Street analysts are revising growth forecasts downward and consumer confidence has dropped to a six-month low.',
                'options'     => [
                    'Hold an emergency economic summit with business and labor leaders',
                    'Announce targeted relief measures for the most affected sectors',
                    'Make the public case for the long-term benefits of the policy',
                ],
            ];
        }

        // Healthcare
        if (preg_match('/\b(health|medic|hospital|insurance|pharma|drug|care|patient)\b/', $lower)) {
            return [
                'title'       => 'Consequence: Healthcare Backlash Intensifies',
                'description' => 'Patient advocacy groups and the American Medical Association have launched a coordinated campaign warning that your healthcare decisions will leave millions without adequate coverage. Congressional oversight hearings have been scheduled and the story is dominating every major news cycle this week.',
                'options'     => [
                    'Announce a review commission to address the most pressing concerns',
                    'Hold town halls in affected districts to defend the policy directly',
                    'Fast-track supplemental legislation to close the most critical gaps',
                ],
            ];
        }

        // Climate / energy / environment
        if (preg_match('/\b(climat|energy|oil|green|environment|carbon|fossil|emission|renewable)\b/', $lower)) {
            return [
                'title'       => 'Consequence: Energy Policy Fault Lines Exposed',
                'description' => 'Environmental coalitions are mobilizing a national protest campaign while industry groups are simultaneously lobbying for further deregulation, putting the White House in a crossfire. Internationally, three climate partner nations are questioning U.S. commitments, creating friction ahead of a major diplomatic summit.',
                'options'     => [
                    'Reaffirm climate commitments through an executive action',
                    'Announce a bipartisan energy commission to balance competing interests',
                    'Stand by the decision and make the energy independence case publicly',
                ],
            ];
        }

        // Guns / crime / justice
        if (preg_match('/\b(gun|firearm|shoot|crime|police|justice|prison|amend|second)\b/', $lower)) {
            return [
                'title'       => 'Consequence: Public Safety Debate Erupts',
                'description' => 'Your public safety decisions have reignited one of the nation\'s most polarizing debates, with gun rights organizations and reform advocates flooding social media and congressional offices. Three swing-state governors are under pressure from constituents to publicly respond, pulling them away from your coalition.',
                'options'     => [
                    'Propose a bipartisan public safety task force to find common ground',
                    'Hold firm and rally your base with a strong public defense of the decision',
                    'Defer to state and local authorities to reduce federal political exposure',
                ],
            ];
        }

        // Technology / cyber / AI
        if (preg_match('/\b(cyber|tech|hack|ai|data|digital|internet|silicon)\b/', $lower)) {
            return [
                'title'       => 'Consequence: Tech Sector Pushback',
                'description' => 'Major technology companies and civil liberties organizations are pushing back hard on your recent tech-related decisions, with a coalition filing a legal challenge in federal court. International partners are raising questions about data sovereignty and digital policy compatibility, threatening cooperation on key tech initiatives.',
                'options'     => [
                    'Meet with tech industry leaders to negotiate a workable framework',
                    'Announce a national digital policy review with broad stakeholder input',
                    'Stand by the decision and defend it on national security grounds',
                ],
            ];
        }

        // Generic fallback
        return [
            'title'       => 'Consequence: Political Pressure Mounts',
            'description' => 'The cumulative weight of recent decisions has triggered a coordinated backlash from opposition leaders and media commentators. Town halls are turning contentious, approval numbers are softening in key demographics, and party strategists are quietly urging a course correction before the situation compounds.',
            'options'     => [
                'Address the criticism head-on in a prime-time national address',
                'Pivot to a popular new initiative to shift the news cycle',
                'Meet privately with senior advisors to reassess your strategy',
            ],
        ];
    }

    private function applyDiminishingReturn(int $current, int $delta): int
    {
        if ($delta <= 0) {
            return $delta; // losses apply in full — no cushion for bad decisions
        }
        if ($current >= 75) {
            return (int) round($delta * 0.25); // heavily reduced: +8 raw → +2 actual
        }
        if ($current >= 60) {
            return (int) round($delta * 0.5);  // reduced: +8 raw → +4 actual
        }

        return $delta; // normal gains below 60
    }

    private function calculateDeltas(string $decision, bool $usedCustomResponse, bool $isZenMonth): array
    {
        if ($isZenMonth) {
            // Still evaluate the decision — nonsense should still hurt
            $lower = strtolower($decision);
            $wordCount = str_word_count($lower);
            $hasSubstance = $wordCount >= 4 && preg_match('/\b(build|meet|address|engage|outreach|diplomacy|alliance|policy|reform|invest|community|review|strengthen|support|reach|consult|brief|prepare|plan)\b/', $lower);
            if (! $hasSubstance) {
                // Nonsensical or unserious zen response — penalise
                return [random_int(-6, -3), random_int(-6, -3), random_int(-6, -3)];
            }
            return [random_int(0, 3), random_int(1, 3), random_int(0, 3)];
        }

        $positiveTerms = ['negotiate', 'compromise', 'aid', 'invest', 'support', 'dialogue', 'protect'];
        $hardlineTerms = ['deploy', 'force', 'hardline', 'emergency', 'crackdown', 'offensive'];

        $decisionLower = strtolower($decision);
        $softScore = 0;
        $hardScore = 0;

        foreach ($positiveTerms as $term) {
            if (str_contains($decisionLower, $term)) {
                $softScore += 2;
            }
        }

        foreach ($hardlineTerms as $term) {
            if (str_contains($decisionLower, $term)) {
                $hardScore += 2;
            }
        }

        if ($usedCustomResponse) {
            $softScore += 1;
        }

        $approval = random_int(-4, 4) + $softScore - intdiv($hardScore, 2);
        $stability = random_int(-4, 4) + $hardScore - intdiv($softScore, 2);
        $party = random_int(-4, 4) + ($hardScore > $softScore ? 2 : -1);

        return [
            max(-12, min(12, $approval)),
            max(-12, min(12, $stability)),
            max(-12, min(12, $party)),
        ];
    }

    private function calculateDeltasWithAI(Game $game, string $decision, bool $usedCustomResponse, bool $isZenMonth): array
    {
        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return $this->calculateDeltas($decision, $usedCustomResponse, $isZenMonth);
        }

        $context = [
            'crisis_title' => $game->active_crisis_title,
            'crisis_description' => $game->active_crisis_description,
            'president_name' => $game->president_name,
            'party' => $game->president_party,
            'current_approval' => $game->approval,
            'current_stability' => $game->stability,
            'current_party_support' => $game->party_support,
            'decision' => $decision,
            'used_custom_response' => $usedCustomResponse,
        ];

        $zenRule = $isZenMonth
            ? "IMPORTANT: This is a Zen Month — no active crisis. The president is expected to take quiet, constructive actions (building alliances, low-profile diplomacy, domestic outreach). "
            . "A sensible, statesmanlike action earns a small positive reward (max +3 per line). "
            . "A nonsensical, unserious, or completely irrelevant response (e.g. pop culture references, gibberish, jokes) must still be penalized: score -4 to -7 across all three lines — the public and party notice when the president wastes a quiet month embarrassingly.\n"
            : "";

        $prompt = "You are the political consequence engine for a presidential crisis simulator.\n"
            ."Given the active crisis and the president's decision, return approval_delta, stability_delta, and party_support_delta.\n"
            ."\n"
            .$zenRule
            ."Rules:\n"
            ."1. Deltas must be integers. Positive cap is +8 (reserved for exceptional decisions only). Negative floor is -10. Most decisions should land between -5 and +5.\n"
            ."2. Be strict. Irrelevant, absurd, or off-topic responses score -6 to -10 across all three. Vague or generic responses that technically relate but offer no substance score -2 to +1.\n"
            ."3. Only a genuinely well-targeted, specific, crisis-appropriate decision earns +4 to +8. Simply mentioning the right topic is not enough — the response must demonstrate real political judgment.\n"
            ."4. A hardline response boosts stability most. A conciliatory response boosts approval most. One line should always benefit more than the others. All three can be positive if the decision genuinely deserves it, but the secondary two gains must be noticeably smaller than the primary one — not all three climbing equally.\n"
            ."5. Party support reflects alignment with the president's base. Centrist or bipartisan moves often cost party support even when they help approval.\n"
            ."6. Add slight random variance (+/-1) so no two identical decisions feel identical.\n"
            ."7. Output strict JSON only — no explanation, no markdown:\n"
            ."{\"approval_delta\": <int>, \"stability_delta\": <int>, \"party_support_delta\": <int>, \"reasoning\": \"<one sentence>\"}\n"
            ."\nContext: ".json_encode($context, JSON_UNESCAPED_SLASHES);

        try {
            $response = $this->callAnthropic([
                'model' => config('services.anthropic.model') ?: 'claude-sonnet-4-20250514',
                'max_tokens' => 256,
                'temperature' => 0.4,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if (! $response || ! $response->successful()) {
                return $this->calculateDeltas($decision, $usedCustomResponse, $isZenMonth);
            }

            $text = data_get($response->json(), 'content.0.text', '');
            $text = preg_replace('/```(?:json)?\s*([\s\S]*?)\s*```/', '$1', trim($text));
            $data = json_decode($text, true);

            if (
                ! is_array($data)
                || ! isset($data['approval_delta'], $data['stability_delta'], $data['party_support_delta'])
                || ! is_int($data['approval_delta'])
                || ! is_int($data['stability_delta'])
                || ! is_int($data['party_support_delta'])
            ) {
                return $this->calculateDeltas($decision, $usedCustomResponse, $isZenMonth);
            }

            if (isset($data['reasoning'])) {
                Log::info('AI delta reasoning', ['reasoning' => $data['reasoning'], 'crisis' => $game->active_crisis_title]);
            }

            return [
                max(-12, min(12, (int) $data['approval_delta'])),
                max(-12, min(12, (int) $data['stability_delta'])),
                max(-12, min(12, (int) $data['party_support_delta'])),
            ];
        } catch (Throwable $e) {
            Log::warning('AI delta calculation failed, using fallback', ['error' => $e->getMessage()]);

            return $this->calculateDeltas($decision, $usedCustomResponse, $isZenMonth);
        }
    }

    private function generateNewsPackage(Game $game, string $decision, int $approvalDelta, int $stabilityDelta, int $partyDelta): array
    {
        $references = $this->extractDecisionReferences($decision);
        $payload = $this->generateNewsWithAnthropic(
            $game,
            $decision,
            $references,
            $approvalDelta,
            $stabilityDelta,
            $partyDelta
        );

        if (! $payload) {
            $payload = $this->generateFallbackNews($game, $decision, $references, $approvalDelta, $stabilityDelta, $partyDelta);
        }

        return $payload;
    }

    private function generateNewsWithAnthropic(
        Game $game,
        string $decision,
        array $references,
        int $approvalDelta,
        int $stabilityDelta,
        int $partyDelta,
        ?string $crisisTitle = null,
        ?string $crisisDescription = null
    ): ?array {
        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return null;
        }

        $overallDelta = $approvalDelta + $stabilityDelta + $partyDelta;
        $sentiment = $overallDelta > 3 ? 'positive' : ($overallDelta < -3 ? 'negative' : 'mixed');

        $context = [
            'president' => $game->president_name,
            'party' => $game->president_party,
            'crisis_title' => $crisisTitle ?? $game->active_crisis_title,
            'crisis_description' => $crisisDescription ?? $game->active_crisis_description,
            'decision' => $decision,
            'references' => $references,
            'overall_sentiment' => $sentiment,
        ];

        $prompt = "Generate political news coverage JSON for exactly 3 outlets.\n".
            "Rules:\n".
            "1) No hallucinations. Only mention what is explicitly in the decision text and provided context.\n".
            "2) Left outlet focuses workers/equity/vulnerable groups.\n".
            "3) Center outlet focuses practicality/feasibility/trade-offs.\n".
            "4) Right outlet focuses leadership/security/domestic industry.\n".
            "5) If coverage is critical, each outlet must criticize for different reasons.\n".
            "6) Headline length 5-8 words.\n".
            "7) Body length max 2-3 sentences.\n".
            "8) Each outlet must cite at least two specific elements from decision text.\n".
            "9) NEVER mention approval ratings, poll numbers, percentage points, or any numerical stat changes. Do not reference approval, stability, or party support scores in any form.\n".
            "Output strict JSON only:\n".
            "{\"outlets\":{\"left\":{\"name\":\"The People's Herald\",\"headline\":\"...\",\"body\":\"...\",\"critique_reason\":\"...\"},\"center\":{\"name\":\"The Civic Report\",\"headline\":\"...\",\"body\":\"...\",\"critique_reason\":\"...\"},\"right\":{\"name\":\"The Patriot Post\",\"headline\":\"...\",\"body\":\"...\",\"critique_reason\":\"...\"}}}\n".
            'Context: '.json_encode($context, JSON_UNESCAPED_SLASHES);

        try {
            $response = $this->callAnthropic([
                'model' => config('services.anthropic.model') ?: 'claude-sonnet-4-20250514',
                'max_tokens' => 4096,
                'temperature' => 0.3,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if (! $response || ! $response->successful()) {
                return null;
            }

            $text = data_get($response->json(), 'content.0.text');
            if (! is_string($text) || $text === '') {
                return null;
            }

            $decoded = $this->decodeJsonFromText($text);
            if (! is_array($decoded) || ! isset($decoded['outlets'])) {
                return null;
            }

            return $this->sanitizeNewsPayload($decoded, $references, 'anthropic');
        } catch (Throwable $e) {
            Log::warning('Anthropic news generation exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function decodeJsonFromText(string $text): ?array
    {
        // Remove markdown code fences
        $text = str_replace(['```json', '```'], ['', ''], $text);
        $text = trim($text);

        // Find JSON bounds
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $json = substr($text, $start, $end - $start + 1);

        // Clean control characters that break JSON
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $json);

        // Try basic decode
        $decoded = json_decode($json, true);

        if ($decoded === null) {
            // Try manual regex extraction (less strict)
            $groups = [];
            // Match each group object - handle escaped quotes in reaction
            preg_match_all('/\{[^}]+\}/', $json, $objects);
            foreach ($objects[0] ?? [] as $obj) {
                if (preg_match('/"id":"([^"]+)"/', $obj, $id) &&
                    preg_match('/"name":"([^"]+)"/', $obj, $name) &&
                    preg_match('/"support":(\d+)/', $obj, $support)) {
                    // Extract reaction - find text between "reaction":" and next "
                    if (preg_match('/"reaction":"(.+?)"(?:,|\})/', $obj, $reaction)) {
                        $groups[] = [
                            'id' => $id[1],
                            'name' => $name[1],
                            'support' => (int) $support[1],
                            'reaction' => $reaction[1],
                        ];
                    }
                }
            }
            if (count($groups) === 12) {
                return ['groups' => $groups];
            }
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function sanitizeNewsPayload(array $payload, array $references, string $source): array
    {
        $defaults = [
            'left' => ['name' => "The People's Herald", 'critique_reason' => 'equity_impact'],
            'center' => ['name' => 'The Civic Report', 'critique_reason' => 'feasibility_tradeoffs'],
            'right' => ['name' => 'The Patriot Post', 'critique_reason' => 'security_industry'],
        ];

        $outlets = [];
        foreach ($defaults as $key => $meta) {
            $headline = trim((string) data_get($payload, "outlets.$key.headline", 'Policy Decision Draws New Questions'));
            $body = trim((string) data_get($payload, "outlets.$key.body", 'Analysts are reviewing the proposal details and political implications.'));
            $reason = trim((string) data_get($payload, "outlets.$key.critique_reason", $meta['critique_reason']));

            $headlineWords = preg_split('/\s+/', $headline) ?: [];
            if (count($headlineWords) < 5 || count($headlineWords) > 8) {
                $headline = 'White House Decision Sparks Policy Debate';
            }

            $body = $this->ensureReferencesInBody($body, $references);

            $outlets[$key] = [
                'name' => $meta['name'],
                'headline' => $headline,
                'body' => $body,
                'critique_reason' => $reason,
            ];
        }

        if (
            $outlets['left']['critique_reason'] === $outlets['center']['critique_reason'] ||
            $outlets['left']['critique_reason'] === $outlets['right']['critique_reason'] ||
            $outlets['center']['critique_reason'] === $outlets['right']['critique_reason']
        ) {
            $outlets['left']['critique_reason'] = 'equity_impact';
            $outlets['center']['critique_reason'] = 'feasibility_tradeoffs';
            $outlets['right']['critique_reason'] = 'security_industry';
        }

        return ['source' => $source, 'outlets' => $outlets];
    }

    private function ensureReferencesInBody(string $body, array $references): string
    {
        return $body;
    }

    private function extractDecisionReferences(string $decision): array
    {
        $clean = trim(preg_replace('/\s+/', ' ', $decision) ?? '');
        if ($clean === '') {
            return ['the proposal details', 'the public response'];
        }

        $parts = preg_split('/[,.!?;:]/', $clean) ?: [];
        $phrases = array_values(array_filter(array_map(fn ($p) => trim($p), $parts), fn ($p) => mb_strlen($p) >= 8));
        if (count($phrases) >= 2) {
            return [mb_substr($phrases[0], 0, 70), mb_substr($phrases[1], 0, 70)];
        }

        $words = array_values(array_filter(explode(' ', strtolower($clean)), fn ($w) => mb_strlen($w) > 4));

        return [
            mb_substr($phrases[0] ?? ($words[0] ?? 'policy step'), 0, 70),
            mb_substr($words[1] ?? ($words[0] ?? 'federal plan'), 0, 70),
        ];
    }

    private function generateFallbackNews(
        Game $game,
        string $decision,
        array $references,
        int $approvalDelta,
        int $stabilityDelta,
        int $partyDelta
    ): array {
        $overallBad = ($approvalDelta + $stabilityDelta + $partyDelta) < 0;
        $toneWord = $overallBad ? 'intense' : 'mixed';
        $refA = $references[0] ?? 'the policy rollout';
        $refB = $references[1] ?? 'the operational details';

        return [
            'source' => 'fallback',
            'outlets' => [
                'left' => [
                    'name' => "The People's Herald",
                    'headline' => 'Workers Question White House Crisis Plan',
                    'body' => "{$game->president_name}'s response drew {$toneWord} scrutiny over equity outcomes. Analysts highlighted key decision points for vulnerable communities.",
                    'critique_reason' => 'equity_impact',
                ],
                'center' => [
                    'name' => 'The Civic Report',
                    'headline' => 'Analysts Debate Feasibility Of Response',
                    'body' => 'Policy desks focused on execution risk and practical trade-offs while weighing costs against expected results.',
                    'critique_reason' => 'feasibility_tradeoffs',
                ],
                'right' => [
                    'name' => 'The Patriot Post',
                    'headline' => 'Leadership Faces Security Credibility Test',
                    'body' => 'Conservative voices framed the move around national strength and domestic industry durability as key leadership benchmarks.',
                    'critique_reason' => 'security_industry',
                ],
            ],
        ];
    }

    private function generateVoterReactions(
        Game $game,
        string $decision,
        array $newsPackage,
        int $approvalDelta,
        int $stabilityDelta,
        int $partyDelta,
        string $crisisTitle = '',
        string $crisisDescription = ''
    ): array {
        $ai = $this->generateVoterReactionsWithAnthropic($game, $decision, $approvalDelta, $stabilityDelta, $partyDelta, $crisisTitle, $crisisDescription);
        if ($ai !== null) {
            return $ai;
        }

        return $this->generateFallbackVoterReactions($decision, $approvalDelta, $stabilityDelta, $partyDelta, $crisisTitle, $crisisDescription);
    }

    private function generateVoterReactionsWithAnthropic(
        Game $game,
        string $decision,
        int $approvalDelta,
        int $stabilityDelta,
        int $partyDelta,
        string $crisisTitle,
        string $crisisDescription
    ): ?array {
        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return null;
        }

        $groups = self::VOTER_GROUPS;

        $prompt = "Generate voter group reactions for a presidential decision game.\n".
            "CRISIS: {$crisisTitle}\n".
            "DESCRIPTION: {$crisisDescription}\n".
            "DECISION: {$decision}\n".
            "APPROVAL DELTA: {$approvalDelta} | STABILITY DELTA: {$stabilityDelta} | PARTY DELTA: {$partyDelta}\n\n".
            "Generate reactions for exactly 12 voter groups. Each group should react based on how the decision addresses (or fails to address) the specific crisis.\n".
            "Output strict JSON only in this format:\n".
            '{"groups":['.
            '{"id":"students","name":"Student Activists","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"yuppie","name":"Young Urban Professionals","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"young_conservatives","name":"Young Conservatives","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"working_class","name":"Working-Class Urban Labor","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"suburban","name":"Suburban Families","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"rural","name":"Rural Farmers","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"small_business","name":"Small Business Owners","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"corporate","name":"Corporate Executives","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"public_sector","name":"Public Sector Workers","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"retirees","name":"Retirees & Seniors","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"minorities","name":"Minority Communities","support":50,"reaction":"1-2 sentence reaction"},'.
            '{"id":"independents","name":"Independent Voters","support":50,"reaction":"1-2 sentence reaction"}'.
            "]}\n".
            "Rules:\n".
            "1. Support scores should be 0-100 based on how the decision impacts each group's concerns.\n".
            "2. If decision is nonsensical/off-topic, give very low support (10-30) and confused/angry reactions.\n".
            "3. If decision is good for crisis, give higher support (55-80).\n".
            "4. If decision fails the crisis, give lower support (25-45).\n".
            "5. Each reaction must be 1-2 sentences specific to the crisis and decision.\n".
            '6. Support scores must be unique across all groups.';

        try {
            $response = $this->callAnthropic([
                'model' => config('services.anthropic.model') ?: 'claude-sonnet-4-20250514',
                'max_tokens' => 4096,
                'temperature' => 0.4,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if (! $response || ! $response->successful()) {
                return null;
            }

            $text = data_get($response->json(), 'content.0.text');
            if (! is_string($text) || $text === '') {
                return null;
            }

            $decoded = $this->decodeJsonFromText($text);
            if (! is_array($decoded) || ! isset($decoded['groups']) || ! is_array($decoded['groups'])) {
                return null;
            }

            $result = [];
            $usedSupports = [];
            foreach ($groups as $group) {
                $id = $group['id'];
                $aiGroup = null;
                foreach ($decoded['groups'] as $g) {
                    if (($g['id'] ?? '') === $id) {
                        $aiGroup = $g;
                        break;
                    }
                }

                if (! $aiGroup) {
                    return null;
                }

                $support = isset($aiGroup['support']) ? (int) $aiGroup['support'] : 50;
                $support = max(0, min(100, $support));

                while (in_array($support, $usedSupports, true)) {
                    $support = min(100, $support + 1);
                    if ($support === 100) {
                        $support = max(0, $support - 1);
                        while (in_array($support, $usedSupports, true) && $support > 0) {
                            $support--;
                        }
                        break;
                    }
                }
                $usedSupports[] = $support;

                $result[] = [
                    'id' => $id,
                    'name' => $group['name'],
                    'issues' => $group['issues'],
                    'emoji' => $group['emoji'],
                    'pastel' => $group['pastel'],
                    'support' => $support,
                    'reaction' => trim($aiGroup['reaction'] ?? 'No reaction recorded.'),
                ];
            }

            return count($result) === 12 ? $result : null;
        } catch (Throwable $e) {
            Log::warning('Anthropic voter reactions exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function generateFallbackVoterReactions(string $decision, int $approvalDelta = 0, int $stabilityDelta = 0, int $partyDelta = 0, string $crisisTitle = '', string $crisisDescription = ''): array
    {
        $text = strtolower($decision);
        $crisisText = strtolower($crisisTitle.' '.$crisisDescription);
        $overall = $approvalDelta + $stabilityDelta + $partyDelta;

        if ($this->isNonsensicalDecision($decision)) {
            return $this->generateConfusedVoterReactions($decision);
        }

        $tone = $overall > 0 ? 'positive' : ($overall < 0 ? 'negative' : 'neutral');

        $issueToGroups = [
            'education' => ['students'],
            'climate' => ['students'],
            'social' => ['students'],
            'debt' => ['students'],
            'career' => ['yuppie'],
            'housing' => ['yuppie'],
            'economy' => ['yuppie', 'working_class', 'small_business', 'corporate'],
            'tech' => ['yuppie'],
            'government' => ['young_conservatives', 'public_sector'],
            'free market' => ['young_conservatives'],
            'gun' => ['young_conservatives', 'rural'],
            'tradition' => ['young_conservatives'],
            'job' => ['working_class', 'yuppie', 'public_sector'],
            'wage' => ['working_class'],
            'union' => ['working_class', 'public_sector'],
            'trade' => ['working_class'],
            'worker' => ['working_class', 'public_sector'],
            'labor' => ['working_class'],
            'school' => ['suburban', 'students'],
            'safety' => ['suburban', 'rural'],
            'tax' => ['suburban', 'young_conservatives', 'small_business'],
            'family' => ['suburban'],
            'farm' => ['rural'],
            'agriculture' => ['rural'],
            'rural' => ['rural'],
            'business' => ['small_business', 'corporate'],
            'regulation' => ['small_business', 'corporate', 'young_conservatives'],
            'profit' => ['corporate'],
            'market' => ['corporate'],
            'industry' => ['corporate'],
            'funding' => ['public_sector'],
            'public' => ['public_sector'],
            'healthcare' => ['retirees'],
            'social security' => ['retirees'],
            'senior' => ['retirees'],
            'medicare' => ['retirees'],
            'equity' => ['minorities'],
            'discrimination' => ['minorities'],
            'minority' => ['minorities'],
            'compromise' => ['independents'],
            'bipartisan' => ['independents'],
            'practical' => ['independents'],
            'budget' => ['working_class', 'suburban', 'public_sector', 'retirees'],
            'congress' => ['independents', 'suburban'],
            'shutdown' => ['working_class', 'public_sector', 'suburban'],
            'border' => ['young_conservatives', 'rural', 'working_class'],
            'immigration' => ['minorities', 'working_class', 'young_conservatives'],
            'asylum' => ['minorities'],
            'cyber' => ['corporate', 'public_sector'],
            'energy' => ['corporate', 'rural'],
            'oil' => ['corporate', 'rural'],
            'climate' => ['students', 'yuppie'],
            'renewable' => ['students', 'yuppie'],
        ];

        $baseSupport = [
            'students' => 48, 'yuppie' => 52, 'young_conservatives' => 45,
            'working_class' => 50, 'suburban' => 55, 'rural' => 42,
            'small_business' => 50, 'corporate' => 55, 'public_sector' => 50,
            'retirees' => 52, 'minorities' => 48, 'independents' => 50,
        ];

        $output = [];
        foreach (self::VOTER_GROUPS as $group) {
            $id = $group['id'];
            $support = $baseSupport[$id] ?? 50;
            $reaction = '';

            $matchedGroups = [];
            foreach ($issueToGroups as $issue => $groups) {
                if (str_contains($text, $issue)) {
                    $matchedGroups = array_merge($matchedGroups, $groups);
                }
            }

            if (in_array($id, $matchedGroups, true)) {
                $support += ($tone === 'positive' ? 10 : ($tone === 'negative' ? -10 : 0));
                $reaction = $this->getGroupReactionForTone($id, $tone, $matchedGroups);
            } else {
                $support += intdiv($overall, 3);
                $reaction = $this->getGroupReactionForTone($id, $tone, []);
            }

            $support = max(0, min(100, $support));

            $output[] = [
                'id' => $group['id'],
                'name' => $group['name'],
                'issues' => $group['issues'],
                'emoji' => $group['emoji'],
                'pastel' => $group['pastel'],
                'support' => $support,
                'reaction' => $reaction,
            ];
        }

        return $output;
    }

    private function generateConfusedVoterReactions(string $decision): array
    {
        $confusedReactions = [
            'students' => 'Baffled by your response. Students are asking what chicken nuggets have to do with the crisis at hand.',
            'yuppie' => 'Stunned by the disconnect. Young professionals question your grip on reality.',
            'young_conservatives' => 'Alarmed by the erratic response. This seems like leadership in decline.',
            'working_class' => 'Angry and confused. Workers needed a serious solution, not a publicity stunt.',
            'suburban' => 'Disturbed by the lack of seriousness. Families expect competent leadership.',
            'rural' => 'Rural communities are scratching their heads wondering what this has to do with anything.',
            'small_business' => 'Small business owners are baffled - this does nothing for their concerns.',
            'corporate' => 'Corporate leaders are questioning your judgment and fitness for office.',
            'public_sector' => 'Public workers are confused - where is the actual plan to fix the crisis?',
            'retirees' => 'Seniors are worried about your mental state. This response makes no sense.',
            'minorities' => 'Minority communities expected serious leadership, not a PR stunt.',
            'independents' => 'Independents are horrified. This is exactly why they dont trust politicians.',
        ];

        $output = [];
        foreach (self::VOTER_GROUPS as $group) {
            $id = $group['id'];
            $base = [
                'students' => 30, 'yuppie' => 32, 'young_conservatives' => 25,
                'working_class' => 28, 'suburban' => 35, 'rural' => 22,
                'small_business' => 30, 'corporate' => 35, 'public_sector' => 25,
                'retirees' => 30, 'minorities' => 28, 'independents' => 20,
            ];

            $output[] = [
                'id' => $group['id'],
                'name' => $group['name'],
                'issues' => $group['issues'],
                'emoji' => $group['emoji'],
                'pastel' => $group['pastel'],
                'support' => $base[$id] ?? 30,
                'reaction' => $confusedReactions[$id] ?? 'Baffled by your completely off-topic response.',
            ];
        }

        return $output;
    }

    private function getGroupReactionForTone(string $groupId, string $tone, array $matchedGroups): string
    {
        $reactions = [
            'positive' => [
                'students' => 'Applauds your focus on education and youth priorities. Finally sees leadership addressing their concerns.',
                'yuppie' => 'Appreciates your approach to economic and career concerns that affect urban professionals.',
                'young_conservatives' => 'Supports your commitment to limited government and conservative principles.',
                'working_class' => 'Welcomes actions that protect jobs, wages, and worker interests.',
                'suburban' => 'Pleased with policies that address family concerns about schools, safety, and taxes.',
                'rural' => 'Appreciates recognition of rural community needs and agricultural interests.',
                'small_business' => 'Supports measures that reduce regulatory burden and help small businesses thrive.',
                'corporate' => 'Encouraged by policies that support profits, markets, and economic growth.',
                'public_sector' => 'Supportive of government funding and job protections for public workers.',
                'retirees' => 'Grateful for actions that strengthen healthcare and Social Security.',
                'minorities' => 'Encouraged by steps toward equity and addressing community concerns.',
                'independents' => 'Sees practical, results-oriented leadership that gets things done.',
            ],
            'negative' => [
                'students' => 'Disappointed by lack of action on education and issues students care about.',
                'yuppie' => 'Frustrated that your policies dont address career, housing, and economic concerns.',
                'young_conservatives' => 'Concerned about government overreach and departure from conservative principles.',
                'working_class' => 'Angry that your response fails to protect jobs, wages, and worker interests.',
                'suburban' => 'Worried your policies dont adequately address family, school, and safety concerns.',
                'rural' => 'Feels ignored on rural and agricultural issues important to their communities.',
                'small_business' => 'Concerned about regulatory burdens and costs that hurt small businesses.',
                'corporate' => 'Worried your policies could harm profits, markets, and economic growth.',
                'public_sector' => 'Anxious about potential cuts to government funding and public sector jobs.',
                'retirees' => 'Alarmed by policies that could weaken healthcare and Social Security.',
                'minorities' => 'Disappointed by lack of progress on equity and discrimination issues.',
                'independents' => 'Skeptical of partisan approaches that dont deliver practical results.',
            ],
            'neutral' => [
                'students' => 'Watching carefully to see if your policies actually address education concerns.',
                'yuppie' => 'Waiting to see concrete outcomes before forming an opinion on economic policy.',
                'young_conservatives' => 'Cautiously monitoring whether you uphold conservative principles.',
                'working_class' => 'Remaining neutral until they see real impact on jobs and wages.',
                'suburban' => 'Evaluating your family policies while weighing trade-offs carefully.',
                'rural' => 'Looking for more specifics on how you will address rural community needs.',
                'small_business' => 'Assessing the impact of your policies on business conditions.',
                'corporate' => 'Analyzing how your approach affects market conditions and profitability.',
                'public_sector' => 'Monitoring government funding decisions before committing support.',
                'retirees' => 'Concerned about healthcare and benefits but waiting for more details.',
                'minorities' => 'Seeking concrete actions on equity before offering full support.',
                'independents' => 'Want to see practical results before endorsing your approach.',
            ],
        ];

        return $reactions[$tone][$groupId] ?? $reactions[$tone]['independents'];
    }

    private function forceUniqueSupports(array $groups): array
    {
        $used = [];
        foreach ($groups as $idx => $group) {
            $support = (int) $group['support'];
            while (in_array($support, $used, true)) {
                $support = min(100, $support + 1);
                if (in_array($support, $used, true) && $support === 100) {
                    $support = max(0, $group['support'] - 1);
                    while (in_array($support, $used, true) && $support > 0) {
                        $support--;
                    }
                    break;
                }
            }
            $groups[$idx]['support'] = $support;
            $used[] = $support;
        }

        return $groups;
    }

    private function isAntiBusinessDecision(string $decision): bool
    {
        $text = strtolower($decision);
        $antiBusinessTerms = ['raise corporate tax', 'windfall tax', 'break up', 'strict regulation', 'antitrust crackdown', 'price controls'];
        foreach ($antiBusinessTerms as $term) {
            if (str_contains($text, $term)) {
                return true;
            }
        }

        return false;
    }

    private function generateStateReactions(Game $game, string $decision, int $approvalDelta, int $stabilityDelta, int $partyDelta, string $crisisTitle = '', string $crisisDescription = ''): array
    {
        $profiles = $this->buildStateProfiles($game);
        $isDemPresident = str_contains(strtolower($game->president_party), 'dem');
        $tags = $this->extractPolicyTags($decision);
        $decisionStance = $this->getDecisionStance($tags);
        $preset = $game->preset ?? '';

        $polarizationKey = $preset === 'Custom' ? ($game->ideology ?? '') : $preset;

        $age = $game->age ?? null;

        $aiGenerated = $this->generateStateReactionsWithAnthropic($decision, $profiles, $crisisTitle, $crisisDescription, $isDemPresident);
        if ($aiGenerated !== null) {
            return $this->enforceIdeologicalTradeoffs(
                $this->applyAgeNudge($this->applyPolarization($aiGenerated, $polarizationKey, $decisionStance), $age),
                $decisionStance
            );
        }

        $globalShift = intdiv($approvalDelta + $stabilityDelta + $partyDelta, 2);
        $responses = [];

        foreach ($profiles as $profile) {
            $score = $profile['base_support'] + random_int(-6, 6) + intdiv($globalShift, 3);

            foreach ($tags as $tag) {
                $weight = (int) ($profile['tag_weights'][$tag] ?? 0);
                if (in_array($tag, $profile['policy_bias'], true)) {
                    $score += (4 + $weight);
                } else {
                    $score -= max(1, intdiv($weight, 2));
                }
            }

            if ($profile['political_color'] === 'red') {
                if (in_array('border', $tags, true) || in_array('fossil_fuels', $tags, true) || in_array('deregulation', $tags, true)) {
                    $score += 6;
                }
                if (in_array('climate', $tags, true) || in_array('immigration_rights', $tags, true)) {
                    $score -= 4;
                }
            }

            if ($profile['political_color'] === 'blue') {
                if (in_array('climate', $tags, true) || in_array('workers', $tags, true) || in_array('immigration_rights', $tags, true)) {
                    $score += 6;
                }
                if (in_array('fossil_fuels', $tags, true) || in_array('deregulation', $tags, true)) {
                    $score -= 4;
                }
            }

            if ($profile['political_color'] === 'swing') {
                $score = max(40, min(60, $score));
            }

            $responses[] = $this->formatStateReaction($profile, max(0, min(100, $score)));
        }

        return $this->enforceIdeologicalTradeoffs(
            $this->applyAgeNudge($this->applyPolarization($responses, $polarizationKey, $decisionStance), $age),
            $decisionStance
        );
    }

    private function getDecisionStance(array $tags): string
    {
        $rightTags = ['border', 'fossil_fuels', 'deregulation', 'security'];
        $leftTags  = ['climate', 'workers', 'immigration_rights', 'healthcare', 'education'];

        $rightCount = count(array_intersect($tags, $rightTags));
        $leftCount  = count(array_intersect($tags, $leftTags));

        if ($rightCount > $leftCount) {
            return 'right';
        }
        if ($leftCount > $rightCount) {
            return 'left';
        }

        return 'center';
    }

    private function applyPolarization(array $reactions, string $preset, string $decisionStance): array
    {
        // Tier 3 = highly polarizing (Trump, AOC)
        // Tier 2 = medium-high (DeSantis, Harris)
        // Tier 1 = medium (Vance, Biden)
        // Tier 0 = centrist/crossover appeal (Haley, Newsom)
        // Tier -1 = custom president, no adjustment
        $tiers = [
            // Presets
            'Trump'    => 3,
            'AOC'      => 3,
            'DeSantis' => 2,
            'Harris'   => 2,
            'Vance'    => 1,
            'Biden'    => 1,
            'Haley'    => 0,
            'Newsom'   => 0,
            // Custom ideologies
            'Hardcore'    => 3,
            'Traditional' => 2,
            'Moderate'    => 1,
            'Swing'       => 0,
        ];

        $tier = $tiers[$preset] ?? -1;
        if ($tier === -1) {
            return $reactions;
        }

        // Amplification multipliers per tier (applied to deviation from 50)
        $amplifiers = [1 => 1.10, 2 => 1.20, 3 => 1.35];

        return array_map(function (array $reaction) use ($tier, $decisionStance, $amplifiers): array {
            $color     = $reaction['political_color'];
            $score     = $reaction['score'];
            $deviation = $score - 50;

            if ($tier === 0) {
                // Haley / Newsom: swing-state appeal — slight boost for competitive states only
                if ($reaction['is_competitive']) {
                    $score = (int) round(50 + $deviation * 1.15);
                }
            } elseif ($decisionStance !== 'center') {
                // Tiers 1–3: amplify based on how the state's ideology aligns with the decision stance
                $aligned  = ($decisionStance === 'right' && $color === 'red')
                         || ($decisionStance === 'left'  && $color === 'blue');
                $opposing = ($decisionStance === 'left'  && $color === 'red')
                         || ($decisionStance === 'right' && $color === 'blue');

                if ($aligned || $opposing) {
                    $score = (int) round(50 + $deviation * $amplifiers[$tier]);
                }
                // Swing states and center-stance decisions: no amplification
            }

            $score = max(0, min(100, $score));
            $reaction['score']    = $score;
            $reaction['band']     = $this->scoreBand($score);
            $reaction['reaction'] = $score >= 55 ? 'Support' : ($score < 45 ? 'Oppose' : 'Neutral');

            return $reaction;
        }, $reactions);
    }

    private function enforceIdeologicalTradeoffs(array $reactions, string $decisionStance): array
    {
        if ($decisionStance === 'center') {
            // Center decisions still face genuine resistance — cap to prevent universally loved outcomes
            return array_map(function (array $reaction): array {
                $score = min(74, $reaction['score']); // nobody "Strongly Supports" a centrist hedge
                if ($score !== $reaction['score']) {
                    $reaction['score'] = $score;
                    $reaction['band'] = $this->scoreBand($score);
                    $reaction['reaction'] = $score >= 55 ? 'Support' : ($score < 45 ? 'Oppose' : 'Neutral');
                }

                return $reaction;
            }, $reactions);
        }

        // Left decisions: non-swing red states can reach at most "Leans Support"
        // Right decisions: non-swing blue states can reach at most "Leans Support"
        $capColor = $decisionStance === 'left' ? 'red' : 'blue';

        return array_map(function (array $reaction) use ($capColor): array {
            if ($reaction['political_color'] === $capColor && ! $reaction['is_competitive']) {
                $score = min(58, $reaction['score']); // hard cap: opposing ideology can't "Support" or "Strongly Support"
                if ($score !== $reaction['score']) {
                    $reaction['score'] = $score;
                    $reaction['band'] = $this->scoreBand($score);
                    $reaction['reaction'] = $score >= 55 ? 'Support' : ($score < 45 ? 'Oppose' : 'Neutral');
                }
            }

            return $reaction;
        }, $reactions);
    }

    private function applyAgeNudge(array $reactions, ?string $age): array
    {
        if (! $age) {
            return $reactions;
        }

        return array_map(function (array $reaction) use ($age): array {
            $color  = $reaction['political_color'];
            $isSwing = $reaction['is_competitive'];
            $nudge  = 0;

            switch ($age) {
                case '40s':
                    // Energetic but unproven — resonates with younger/urban/media-sensitive environments
                    if ($color === 'blue' || $isSwing) {
                        $nudge = 2;
                    } elseif ($color === 'red') {
                        $nudge = -1;
                    }
                    break;

                case '50s':
                    // Prime leadership — broadly neutral, slight edge in competitive environments
                    if ($isSwing) {
                        $nudge = 1;
                    }
                    break;

                case '60s':
                    // Experienced but aging — institutional credibility in traditional states, slight drag in progressive areas
                    if ($color === 'red') {
                        $nudge = 2;
                    } elseif ($color === 'blue') {
                        $nudge = -2;
                    }
                    // Swing states: neutral
                    break;

                case '70s':
                    // Old guard — very loyal base states appreciate it, swing/media-sensitive environments less so
                    if ($color === 'red' && ! $isSwing) {
                        $nudge = 2;
                    } elseif ($isSwing) {
                        $nudge = -3;
                    } elseif ($color === 'blue') {
                        $nudge = -2;
                    }
                    break;
            }

            if ($nudge === 0) {
                return $reaction;
            }

            $score = max(0, min(100, $reaction['score'] + $nudge));
            $reaction['score']    = $score;
            $reaction['band']     = $this->scoreBand($score);
            $reaction['reaction'] = $score >= 55 ? 'Support' : ($score < 45 ? 'Oppose' : 'Neutral');

            return $reaction;
        }, $reactions);
    }

    private function buildStateProfiles(Game $game): array
    {
        $blueStates = ['CA', 'NY', 'MA', 'WA', 'OR', 'CT', 'NJ', 'MD', 'VT', 'RI', 'IL', 'HI', 'DE'];
        $redStates = ['AL', 'AR', 'ID', 'IN', 'KS', 'KY', 'LA', 'MS', 'MO', 'MT', 'ND', 'OK', 'SC', 'SD', 'TN', 'TX', 'UT', 'WV', 'WY', 'NE'];
        $allSwing = self::SWING_STATES;

        $isDemPresident = str_contains(strtolower($game->president_party), 'dem');
        $profiles = [];
        foreach (self::STATES as $state) {
            $abbr = $state['abbr'];
            $color = in_array($abbr, $allSwing, true) ? 'swing' : (in_array($abbr, $blueStates, true) ? 'blue' : (in_array($abbr, $redStates, true) ? 'red' : 'swing'));

            $base = $color === 'blue' ? ($isDemPresident ? 61 : 45) : ($color === 'red' ? ($isDemPresident ? 44 : 61) : 52);
            $policyBias = match ($color) {
                'blue' => ['strong_support', 'workers', 'climate', 'immigration_rights'],
                'red' => ['strong_support', 'border', 'fossil_fuels', 'deregulation', 'security'],
                default => ['lean_support', 'economy', 'jobs', 'stability'],
            };

            $profiles[] = [
                'state' => $state['name'],
                'abbr' => $abbr,
                'political_color' => $color,
                'policy_bias' => $policyBias,
                'identity' => [
                    'overview' => "{$state['name']} electorate balances local economy and national governance concerns.",
                    'priorities' => $color === 'blue'
                        ? ['workers', 'equity', 'climate', 'public services']
                        : ($color === 'red' ? ['security', 'domestic industry', 'energy', 'border'] : ['cost of living', 'jobs', 'stability']),
                ],
                'base_support' => $base,
                'tag_weights' => [
                    'border' => $color === 'red' ? 4 : 1,
                    'fossil_fuels' => $color === 'red' ? 4 : 1,
                    'deregulation' => $color === 'red' ? 3 : 1,
                    'climate' => $color === 'blue' ? 4 : 1,
                    'workers' => $color === 'blue' ? 3 : 2,
                    'immigration_rights' => $color === 'blue' ? 4 : 1,
                    'security' => $color === 'red' ? 3 : 2,
                    'economy' => 3,
                    'healthcare' => 2,
                    'education' => 2,
                ],
            ];
        }

        return $profiles;
    }

    private function isNonsensicalDecision(string $decision): bool
    {
        $clean = trim($decision);
        if (mb_strlen($clean) < 8) {
            return true;
        }

        preg_match_all('/[A-Za-z]{3,}/', $clean, $matches);

        return count($matches[0] ?? []) < 3;
    }

    private function extractPolicyTags(string $decision): array
    {
        $text = strtolower($decision);
        $dict = [
            'border' => ['border', 'immigration enforcement', 'crossing'],
            'fossil_fuels' => ['oil', 'gas', 'pipeline', 'drilling', 'fossil'],
            'deregulation' => ['deregulation', 'cut regulation', 'roll back rules'],
            'climate' => ['climate', 'emission', 'clean energy', 'renewable'],
            'workers' => ['workers', 'labor', 'union', 'wages'],
            'immigration_rights' => ['asylum', 'migrant rights', 'pathway to citizenship'],
            'security' => ['security', 'national guard', 'military', 'defense', 'satellite', 'cyber'],
            'economy' => ['economy', 'jobs', 'inflation', 'growth', 'tariff', 'trade', 'bank', 'financial'],
            'healthcare' => ['healthcare', 'medicare', 'insurance', 'overdose', 'fentanyl', 'opioid', 'treatment', 'pandemic', 'vaccination'],
            'education' => ['school', 'education', 'student', 'retraining'],
            'diplomacy' => ['ceasefire', 'sanctions', 'treaty', 'negotiat', 'diplomatic', 'peace talks', 'un-mediated', 'un mediated', 'multilateral'],
            'intervention' => ['deploy military', 'weapons transfer', 'military advisor', 'armed forces', 'combat'],
        ];

        $tags = [];
        foreach ($dict as $tag => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($text, $needle)) {
                    $tags[] = $tag;
                    break;
                }
            }
        }

        return array_values(array_unique($tags));
    }

    private function generateStateReactionsWithAnthropic(string $decision, array $profiles, string $crisisTitle = '', string $crisisDescription = '', bool $isDemPresident = false): ?array
    {
        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return null;
        }

        $isNonsensical = $this->isNonsensicalDecision($decision);

        $partyLabel = $isDemPresident ? 'Democrat' : 'Republican';
        $homeColor = $isDemPresident ? 'blue' : 'red';
        $opposingColor = $isDemPresident ? 'red' : 'blue';

        try {
            $prompt = 'Return strict JSON only. Generate 50 state support scores 0-100 based on presidential decision response to crisis.'."\n".
                "CRISIS: {$crisisTitle}\n".
                "DESCRIPTION: {$crisisDescription}\n".
                "PRESIDENT PARTY: {$partyLabel}\n".
                "DECISION: {$decision}\n\n".
                'Step 1 — Assess relevance: Does the decision meaningfully address the crisis? '.
                'Relevant means the decision engages with the crisis subject — even if controversial, minimal, or indirect. '.
                'Irrelevant means the decision ignores the crisis entirely and does something completely unrelated (personal activities, unrelated hobbies, non sequiturs, pure silence). '.
                'Set "decision_relevant" to false ONLY for genuinely irrelevant decisions. A politically weak, minimal, or unpopular-but-real policy response is still relevant.'."\n".
                'Step 2 — Score each state:'."\n".
                "1. If decision_relevant is false: scores must be very low. {$homeColor} states may score up to 42. {$opposingColor} states score 5-34. Swing states score 5-25. No state exceeds 42.\n".
                '2. CRITICAL RULE: Every decision in this game is a real political choice. Never treat a politically legitimate but controversial stance as a failure. Instead, score it as divisive — states that ideologically align with that stance score high (60-80), states that oppose it score low (30-44). This creates natural political split maps, not uniform punishment.'."\n".
                '3. A decisive, well-matched response earns strong scores for ideologically aligned states (60-85), but opposing-ideology states will still resist (20-50). No decision is universally popular — every real political choice creates ideological winners and losers.'."\n".
                '4. A cautious, minimal, or indirect response earns mixed scores (32-62) across the board, with clear pushback from opposing-ideology states.'."\n".
                "5. {$homeColor} states always lean more forgiving toward a {$partyLabel} president; {$opposingColor} states lean more critical.\n".
                '6. Swing states (PA, MI, AZ, GA, WI, NV, NC, NH, FL) sit in the middle.'."\n".
                '7. POLITICAL DIVIDE GUIDE — apply these splits for divisive decisions:'."\n".
                '   GUN POLICY: Gun control/safety legislation → blue states support (60-75), red states oppose (30-44). Deferring to state authority on guns → red states support (62-78), blue states oppose (30-44).'."\n".
                '   ECONOMIC INTERVENTION: Federal bailout/spending → blue and swing states lean support; red states skeptical. Allowing market forces / no bailout → red/libertarian states support (60-75), blue states oppose (32-44).'."\n".
                '   DRUG POLICY: Law enforcement crackdown on drugs → red states support (62-78), blue states mixed (45-58). Harm reduction / decriminalization → blue states support (60-74), red states oppose (30-44). Treatment funding is broadly supported across all states (52-70).'."\n".
                '   ENERGY/CLIMATE: Clean energy pivot → blue states support (62-76), red states skeptical (35-48). Releasing reserves or subsidizing fuel → red and swing states support (58-72), blue states mixed (44-58).'."\n".
                '   FOREIGN POLICY: Military intervention/weapons → hawkish states support, non-interventionist states oppose. Isolationism / non-intervention → red states often support (no foreign wars), blue states often oppose (abandoning allies). Sanctions-only is broadly acceptable. Diplomatic/UN solutions → blue states support (60-74), red states skeptical (36-50).'."\n".
                '   FEDERAL POWER: Federal mandates/emergency powers → blue states generally accept, red states resist. Deferring to states → red states support, blue states frustrated.'."\n".
                '   TECH/AI: AI moratorium or heavy tech regulation → blue states lean support, corporate-heavy and red states oppose. Light-touch / industry-led → red and corporate-lean states support, blue states opposed.'."\n".
                '8. Each state score must be unique (no duplicates).'."\n".
                '9. MANDATORY TRADEOFF — No decision is universally good. For left-leaning decisions (climate, workers, immigration rights, gun safety, federal spending): red non-swing states must score 20-48, most blue states 55-82. For right-leaning decisions (border, deregulation, fossil fuels, military, law enforcement): blue non-swing states must score 20-48, most red states 55-82. For center/mixed decisions: at least 15 states must score below 50, spread across both ideological camps.'."\n".
                'State profiles: '.json_encode($profiles, JSON_UNESCAPED_SLASHES).'. '.
                'Format: {"decision_relevant": true, "states":{"AL":72,"AK":45,"CA":30,...}}';

            $response = $this->callAnthropic([
                'model' => config('services.anthropic.model') ?: 'claude-sonnet-4-20250514',
                'max_tokens' => 500,
                'temperature' => $isNonsensical ? 0.1 : 0.3,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if (! $response || ! $response->successful()) {
                return null;
            }

            $text = data_get($response->json(), 'content.0.text');
            if (! is_string($text) || $text === '') {
                return null;
            }

            $decoded = $this->decodeJsonFromText($text);
            $stateScores = data_get($decoded, 'states');
            if (! is_array($stateScores)) {
                return null;
            }

            $decisionRelevant = (bool) data_get($decoded, 'decision_relevant', true);
            $irrelevant = $isNonsensical || ! $decisionRelevant;

            $byAbbr = [];
            foreach ($profiles as $profile) {
                $abbr = $profile['abbr'];
                $raw = $stateScores[$abbr] ?? null;
                if (! is_numeric($raw)) {
                    return null;
                }
                $score = (int) max(0, min(100, round((float) $raw)));

                if ($irrelevant) {
                    $color = $profile['political_color'];
                    $isSwing = in_array($abbr, self::SWING_STATES, true);
                    $isHomeState = ! $isSwing && (
                        ($isDemPresident && $color === 'blue') ||
                        (! $isDemPresident && $color === 'red')
                    );

                    if ($isSwing) {
                        // Swing states are unforgiving regardless of party
                        $score = max(5, min(25, $score));
                    } elseif ($isHomeState) {
                        // Same-party states give a bit of rope: Leans Oppose at best
                        $score = min(42, $score);
                    } else {
                        // Opposing-party states are harsh: Opposes at best
                        $score = min(34, $score);
                    }
                } else {
                    // Relevant decision: floors vary by ideological alignment.
                    // Opposing-ideology states can genuinely oppose; home states have a soft floor.
                    $isSwing = in_array($abbr, self::SWING_STATES, true);
                    $isHomeColor = ($isDemPresident && $profile['political_color'] === 'blue')
                        || (! $isDemPresident && $profile['political_color'] === 'red');

                    if ($isSwing) {
                        $score = max(38, min(62, $score));
                    } elseif ($isHomeColor) {
                        $score = max(25, $score); // same-party states: floor at "Opposes"
                    } else {
                        $score = max(10, $score); // opposing states: can reach "Strongly Opposes"
                    }
                }

                $byAbbr[] = $this->formatStateReaction($profile, $score);
            }

            return count($byAbbr) === 50 ? $byAbbr : null;
        } catch (Throwable $e) {
            Log::warning('Anthropic state generation exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function formatStateReaction(array $profile, int $score): array
    {
        return [
            'state' => $profile['state'],
            'abbr' => $profile['abbr'],
            'score' => $score,
            'band' => $this->scoreBand($score),
            'is_competitive' => in_array($profile['abbr'], self::SWING_STATES, true),
            'political_color' => $profile['political_color'],
            'reaction' => $score >= 55 ? 'Support' : ($score < 45 ? 'Oppose' : 'Neutral'),
        ];
    }

    private function scoreBand(int $score): string
    {
        return match (true) {
            $score >= 75 => 'Strongly Supports',
            $score >= 65 => 'Supports',
            $score >= 55 => 'Leans Support',
            $score >= 45 => 'Neutral',
            $score >= 35 => 'Leans Oppose',
            $score >= 25 => 'Opposes',
            default => 'Strongly Opposes',
        };
    }

    private function callAnthropic(array $payload)
    {
        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->withoutVerifying()->timeout(120)->post('https://api.anthropic.com/v1/messages', $payload);

            if (! $response->successful()) {
                Log::warning('Anthropic API non-success', [
                    'status' => $response->status(),
                    'body' => mb_substr((string) $response->body(), 0, 400),
                ]);
            }

            return $response;
        } catch (Throwable $e) {
            Log::warning('Anthropic request transport error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function generateNewsOnDemand(Game $game, Turn $turn): array
    {
        $decision = $turn->decision;
        $approvalDelta = $turn->approval_delta ?? 0;
        $stabilityDelta = $turn->stability_delta ?? 0;
        $partyDelta = $turn->party_support_delta ?? 0;

        $references = $this->extractDecisionReferences($decision);
        $references = $references ?: ['the policy details', 'the implementation plan'];

        $payload = $this->generateNewsWithAnthropic(
            $game,
            $decision,
            $references,
            $approvalDelta,
            $stabilityDelta,
            $partyDelta,
            $turn->crisis_title,
            $turn->crisis_description
        );

        if (! $payload) {
            $payload = $this->generateFallbackNews($game, $decision, $references, $approvalDelta, $stabilityDelta, $partyDelta);
        }

        return $payload;
    }

    public function generateStateReactionsOnDemand(Game $game, Turn $turn): array
    {
        $decision = $turn->decision;
        $approvalDelta = $turn->approval_delta ?? 0;
        $stabilityDelta = $turn->stability_delta ?? 0;
        $partyDelta = $turn->party_support_delta ?? 0;
        $crisisTitle = $turn->crisis_title ?? '';
        $crisisDescription = $turn->crisis_description ?? '';

        return $this->generateStateReactions($game, $decision, $approvalDelta, $stabilityDelta, $partyDelta, $crisisTitle, $crisisDescription);
    }

    public function generateVoterReactionsOnDemand(Game $game, Turn $turn): array
    {
        $decision = $turn->decision;
        $approvalDelta = $turn->approval_delta ?? 0;
        $stabilityDelta = $turn->stability_delta ?? 0;
        $partyDelta = $turn->party_support_delta ?? 0;
        $crisisTitle = $turn->crisis_title ?? '';
        $crisisDescription = $turn->crisis_description ?? '';

        $newsPackage = $turn->news_payload ?? [];

        return $this->generateVoterReactions($game, $decision, $newsPackage, $approvalDelta, $stabilityDelta, $partyDelta, $crisisTitle, $crisisDescription);
    }
}
