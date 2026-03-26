<?php

namespace App\Services;

use Anthropic\Client;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key') ?? '';
        
        if (empty($this->apiKey)) {
            throw new \Exception('ANTHROPIC_API_KEY is not set in environment');
        }
        
        $this->model = env('ANTHROPIC_MODEL', 'claude-sonnet-4-6');
    }

    protected array $scenarioFraming = [
        'Oil Prices Surge' => [
    'left' => [
        'tone' => 'sympathetic to workers, critical of corporations, urgent about relief',
        'focus' => 'rising fuel costs hurting workers and low-income households, burden on daily commuting, suspicion of corporate profiteering, need for immediate consumer relief',
        'praise' => 'direct financial relief for consumers, fuel subsidies, price caps, windfall taxes on oil companies',
        'criticism' => 'policies that benefit oil companies over consumers, lack of immediate relief, ignoring price gouging or corporate accountability',
    ],
    'center' => [
        'tone' => 'analytical, measured, focused on economic tradeoffs',
        'focus' => 'causes of price surge, supply chain disruption, global market factors, economic impact on inflation and households, feasibility of policy responses',
        'praise' => 'balanced and realistic policy responses, short-term relief with long-term planning',
        'criticism' => 'unclear or inconsistent strategy, policies with unintended economic consequences, lack of detailed implementation',
    ],
    'right' => [
        'tone' => 'pro-domestic production, critical of regulation, focused on energy independence',
        'focus' => 'need for domestic oil production, reducing reliance on foreign energy, regulatory barriers, long-term energy security',
        'praise' => 'expanding domestic drilling, pipeline approvals, reducing restrictions on energy production',
        'criticism' => 'over-regulation, reliance on foreign oil, policies that restrict production or prioritize green energy at the cost of affordability',
    ],
],
        'Border Crisis' => [
    'left' => [
        'tone' => 'empathetic toward migrants, critical of enforcement-heavy responses, urgent on human rights',
        'focus' => 'treatment of asylum seekers, family safety, conditions in detention, humanitarian obligations, root causes of migration',
        'praise' => 'humane processing, expanded asylum access, protections for families and children, addressing root causes in origin countries',
        'criticism' => 'detention conditions, family separations, aggressive enforcement, militarization of the border, dehumanizing rhetoric',
    ],
    'center' => [
        'tone' => 'measured, policy-focused, concerned with system capacity and practicality',
        'focus' => 'border system capacity, legal process for asylum, coordination between agencies, resource allocation, policy tradeoffs',
        'praise' => 'balanced and enforceable policy, improved processing systems, cooperation across agencies',
        'criticism' => 'overloaded system, lack of clear implementation, inconsistent policy, political gridlock',
    ],
    'right' => [
        'tone' => 'firm, security-focused, emphasizes law enforcement and national sovereignty',
        'focus' => 'border control, illegal entry, strain on public resources, crime concerns, national sovereignty',
        'praise' => 'strict enforcement, increased border security, physical barriers, faster deportation processes',
        'criticism' => 'weak enforcement, catch-and-release policies, sanctuary policies, incentives for illegal migration',
    ],
],
        'Healthcare Crisis' => [
    'left' => [
        'tone' => 'urgent, patient-focused, critical of private healthcare profits',
        'focus' => 'access to care for uninsured populations, high prescription drug costs, coverage gaps, healthcare as a basic right, burden on low-income families',
        'praise' => 'expanding public coverage, lowering drug prices, strengthening Medicaid and Medicare, increasing affordability for patients',
        'criticism' => 'prioritizing insurance or pharmaceutical companies, lack of real patient relief, high out-of-pocket costs, profit-driven care',
    ],
    'center' => [
        'tone' => 'analytical, policy-focused, concerned with feasibility and cost',
        'focus' => 'coverage expansion numbers, cost implications, system efficiency, implementation timeline, tradeoffs between public and private options',
        'praise' => 'balanced and practical reforms, incremental improvements, cost-aware solutions',
        'criticism' => 'unrealistic proposals, lack of funding clarity, implementation challenges, unintended system strain',
    ],
    'right' => [
        'tone' => 'market-oriented, critical of government expansion, focused on individual choice',
        'focus' => 'free market healthcare solutions, reducing regulation, competition among providers, preserving doctor-patient autonomy',
        'praise' => 'market-based reforms, reducing government control, increasing competition and choice',
        'criticism' => 'government overreach, expansion of public healthcare programs, mandates, reduced efficiency due to bureaucracy',
    ],
],
        'Tech Layoffs' => [
    'left' => [
        'tone' => 'worker-focused, critical of corporate decisions, urgent about job security',
        'focus' => 'mass layoffs impact on workers and families, job security, severance fairness, need for retraining and protections, corporate accountability',
        'praise' => 'expanded worker support, strong severance protections, retraining programs, labor protections and union support',
        'criticism' => 'corporate layoffs prioritizing profits, lack of worker protections, insufficient support for displaced employees, unchecked tech company power',
    ],
    'center' => [
        'tone' => 'analytical, data-driven, focused on economic implications',
        'focus' => 'labor market trends, scale of layoffs, retraining effectiveness, impact on local economies and housing, broader tech sector adjustments',
        'praise' => 'practical workforce transition programs, targeted economic support, balanced policy responses',
        'criticism' => 'limited effectiveness of programs, unclear long-term outcomes, gaps in implementation or funding',
    ],
    'right' => [
        'tone' => 'pro-business, focused on competitiveness and economic flexibility',
        'focus' => 'business conditions in the tech sector, cost pressures, need for flexibility in hiring and layoffs, maintaining innovation and global competitiveness',
        'praise' => 'policies that support business growth, reduce regulatory burden, and encourage innovation',
        'criticism' => 'overregulation, restrictions on companies, policies that discourage investment or make the US less competitive',
    ],
],
        'Stock Market Crash' => [
    'left' => [
        'tone' => 'concerned about inequality, focused on protecting workers and retirees',
        'focus' => 'impact on retirement savings and 401ks, worker layoffs, economic inequality, role of Wall Street behavior',
        'praise' => 'protecting worker savings, stronger financial regulation, direct relief for affected households',
        'criticism' => 'bailing out large financial institutions, prioritizing investors over workers, lack of protections for ordinary people',
    ],
    'center' => [
        'tone' => 'measured, focused on economic stability and policy evaluation',
        'focus' => 'market dynamics, economic indicators, policy response options, risk of recession and broader economic impact',
        'praise' => 'calm and coordinated response, evidence-based policy decisions, efforts to stabilize markets',
        'criticism' => 'overreaction or insufficient action, unclear policy direction, potential unintended consequences',
    ],
    'right' => [
        'tone' => 'pro-market, focused on confidence and limiting government interference',
        'focus' => 'investor confidence, business stability, risks of government intervention, long-term economic growth',
        'praise' => 'policies that restore market confidence, support businesses, and avoid excessive regulation',
        'criticism' => 'government overreach, heavy regulation, policies that create uncertainty or discourage investment',
    ],
],
        'Foreign Policy Crisis' => [
    'left' => [
        'tone' => 'cautious about escalation, focused on diplomacy and humanitarian impact',
        'focus' => 'civilian impact, risk of war, diplomatic solutions, role of international institutions, long-term stability',
        'praise' => 'negotiations, coalition building, humanitarian aid, de-escalation efforts',
        'criticism' => 'military escalation, unilateral action, ignoring allies, aggressive rhetoric',
    ],
    'center' => [
        'tone' => 'strategic, analytical, focused on balance and execution',
        'focus' => 'national interests, geopolitical risks, alliance commitments, military and diplomatic options, policy tradeoffs',
        'praise' => 'measured and coordinated response, clear strategic planning, maintaining alliances',
        'criticism' => 'unclear objectives, inconsistent strategy, lack of coordination or planning',
    ],
    'right' => [
        'tone' => 'strength-focused, assertive, emphasizes national security and deterrence',
        'focus' => 'military readiness, deterrence, defending national interests, credibility with allies, projecting strength',
        'praise' => 'decisive action, strong military posture, supporting allies, firm stance against adversaries',
        'criticism' => 'weakness, hesitation, reliance on international bodies, policies that undermine deterrence',
    ],
],
        'Free Month - No Crisis' => [
    'left' => [
        'tone' => 'forward-looking, focused on advancing domestic reforms and social protections',
        'focus' => 'opportunity to push worker protections, expand social programs, address inequality, and prioritize working families',
        'praise' => 'progressive initiatives, expanding benefits, investments in public services, stronger labor protections',
        'criticism' => 'lack of bold action, prioritizing corporate interests, missed opportunity to address inequality',
    ],
    'center' => [
        'tone' => 'pragmatic, policy-oriented, focused on governance and legislative progress',
        'focus' => 'policy agenda setting, bipartisan opportunities, legislative feasibility, alignment with voter priorities',
        'praise' => 'clear and realistic priorities, incremental progress, bipartisan cooperation',
        'criticism' => 'lack of direction, political gridlock, overly ambitious or unfocused proposals',
    ],
    'right' => [
        'tone' => 'fiscally conservative, focused on limiting government and promoting growth',
        'focus' => 'economic agenda, reducing regulation, lowering spending, strengthening private sector growth',
        'praise' => 'pro-growth policies, tax relief, deregulation, reducing government intervention',
        'criticism' => 'expanding government programs, increased spending, higher taxes, regulatory overreach',
    ],
    ],
    ];

    protected function getScenarioFraming(string $eventTitle): ?array
    {
        return $this->scenarioFraming[$eventTitle] ?? null;
    }

    protected function buildScenarioFramingBlock(?array $framing): string
    {
        if (!$framing) {
            return "For this scenario, apply the outlet definitions above based on which issues the decision actually addresses.";
        }

        $lines = [];

        if (isset($framing['left'])) {
            $tone = $framing['left']['tone'] ?? '';
            $lines[] = "LEFT-LEANING MEDIA [Tone: {$tone}]: Focus on {$framing['left']['focus']}";
            if (isset($framing['left']['praise'])) {
                $lines[] = "  - PRAISE: {$framing['left']['praise']}";
            }
            if (isset($framing['left']['criticism'])) {
                $lines[] = "  - CRITICIZE: {$framing['left']['criticism']}";
            }
        }

        if (isset($framing['center'])) {
            $tone = $framing['center']['tone'] ?? '';
            $lines[] = "CENTER MEDIA [Tone: {$tone}]: Focus on {$framing['center']['focus']}";
            if (isset($framing['center']['praise'])) {
                $lines[] = "  - PRAISE: {$framing['center']['praise']}";
            }
            if (isset($framing['center']['criticism'])) {
                $lines[] = "  - CRITICIZE: {$framing['center']['criticism']}";
            }
        }

        if (isset($framing['right'])) {
            $tone = $framing['right']['tone'] ?? '';
            $lines[] = "RIGHT-LEANING MEDIA [Tone: {$tone}]: Focus on {$framing['right']['focus']}";
            if (isset($framing['right']['praise'])) {
                $lines[] = "  - PRAISE: {$framing['right']['praise']}";
            }
            if (isset($framing['right']['criticism'])) {
                $lines[] = "  - CRITICIZE: {$framing['right']['criticism']}";
            }
        }

        return implode("\n", $lines);
    }

    public function generateNewsReactions(string $decision, string $eventTitle, ?array $president = null): array
    {
        $prompt = $this->buildNewsPrompt($decision, $eventTitle, $president);
        
        $response = $this->callAPI($prompt);
        
        return $this->parseNewsResponse($response);
    }

    public function generateZenNewsReactions(string $decision, ?array $president = null): array
    {
        $prompt = $this->buildZenNewsPrompt($decision, $president);
        
        $response = $this->callAPI($prompt);
        
        return $this->parseNewsResponse($response);
    }

    public function generateVoterReactions(string $decision, string $eventTitle, array $newsReactions, ?array $president = null): array
    {
        $prompt = $this->buildVoterPrompt($decision, $eventTitle, $newsReactions, $president);
        
        $response = $this->callAPI($prompt);
        
        return $this->parseVoterResponse($response);
    }

    public function analyzePlayerResponse(string $response, string $eventTitle, ?array $president = null): array
    {
        $prompt = $this->buildAnalysisPrompt($response, $eventTitle, $president);
        
        $apiResponse = $this->callAPI($prompt);
        
        return $this->parseAnalysisResponse($apiResponse);
    }

    public function analyzeZenResponse(string $response, ?array $president = null): array
    {
        $prompt = $this->buildZenAnalysisPrompt($response, $president);
        
        $apiResponse = $this->callAPI($prompt);
        
        return $this->parseAnalysisResponse($apiResponse);
    }

    protected function buildNewsPrompt(string $decision, string $eventTitle, ?array $president = null): string
    {
        $presidentInfo = $president ? "\nPresident: {$president['name']} ({$president['party']})" : '';
        $scenarioFraming = $this->getScenarioFraming($eventTitle);
        $scenarioBlock = $this->buildScenarioFramingBlock($scenarioFraming);
        
        return <<<EOT
You must follow the rules below exactly.

SCENARIO: {$eventTitle}

PRESIDENT: "{$decision}"{$presidentInfo}

==================================================
FIRST RULE: UNDERSTAND THE DECISION BEFORE WRITING
==================================================

Silently analyze the decision and determine:
1. Is the decision: directly related, partially related, symbolic, vague, contradictory, unrealistic, nonsensical, or distracting/off-topic?
2. What policy components are present? (subsidy, tax cut, welfare aid, deregulation, military action, enforcement, deportation, amnesty, infrastructure, domestic production, renewable investment, diplomacy, policing, education reform, healthcare, symbolic gesture only, etc.)
3. Who is most likely helped, hurt, reassured, ignored, or angered?
4. Is the decision: short-term relief, long-term reform, mixed, mostly political symbolism, or a failure to address the issue?

==================================================
STRICT RULE: DO NOT HALLUCINATE
==================================================

You MUST strictly follow these rules:
- ONLY write about what the president EXPLICITLY said in their response
- Do NOT invent, assume, or add: tax cuts, deregulation, new spending, new programs, or any policy not mentioned
- Do NOT make up details about what the president "really means" or "will do next"
- If the president said "release oil reserves" - only write about releasing oil reserves. Nothing else.
- If the president said "increase border patrol" - only write about increasing border patrol. Nothing else.
- If the president gave vague answers - write about the vagueness. Do not fill in specifics.

CRITICAL: Each outlet MUST reference AT LEAST 2 specific elements from the president's actual response. If the response only has 1 element, all outlets must note this and comment on limitations.

==================================================
SECOND RULE: NEVER MAKE ALL 3 OUTLETS SOUND THE SAME
==================================================

They must differ in: what part they focus on, how they frame competence, how they judge priorities, how they interpret consequences, and what language they use.

Even when all 3 agree a decision is bad, they must criticize it for DIFFERENT reasons.

==================================================
OUTLET DEFINITIONS
==================================================

1) LEFT-LEANING MEDIA
Priorities: workers, poor families, vulnerable groups, minorities, immigrants, ordinary citizens; fairness and equity; government responsibility and competence; whether real people are materially helped; whether policy benefits elites/corporations more than the public; public welfare, services, long-term social harm

Framing: asks who is helped and who is left behind; critical of neglect, inequality, performative politics, empty symbolism; may support state intervention if it helps ordinary people; skeptical of corporate favoritism, deregulation, harsh crackdowns; if unserious/irrelevant, frame as irresponsible governance during a serious issue.

Do NOT auto-oppose everything. If policy helps workers, struggling families, or public welfare, acknowledge it.

2) CENTER MEDIA
Priorities: relevance to actual issue; feasibility, implementation, cost, trade-offs, measurable effect; public reaction across multiple sides; institutional stability and seriousness; whether decision is practical, coherent, likely to work

Framing: calm, analytical, moderate; does NOT sound activist or ideological; evaluates what decision does and does not do; highlights trade-offs without being partisan; if silly/irrelevant, describe as failing to address crisis and raising questions about priorities/competence.

Do NOT make center secretly left or right-leaning. Focus on relevance, practicality, consequences.

3) RIGHT-LEANING MEDIA
Priorities: leadership strength, seriousness, national order, security, stability; limited government, fiscal restraint, skepticism of bureaucracy; domestic industry, national self-reliance, law and order, strong executive action; whether decision protects citizens, rewards work, avoids government excess; national credibility and executive competence

Framing: direct, sharper, forceful; favorable to strong action, domestic production, border enforcement, policing, military strength, economic nationalism; skeptical of subsidies, bureaucracy, excessive spending, weak symbolic politics, government overreach; if unserious/irrelevant, frame as weak leadership, embarrassing misprioritization, failure to govern seriously

Do NOT use left-wing anti-corporate language. Right criticism should sound like: weak leadership, government waste, lack of seriousness, overreach, misplaced priorities, failure to secure nation/economy. NOT generic progressive rhetoric.

Do NOT auto-oppose everything. If decision includes stronger enforcement, domestic energy, national industry, tougher security, executive resolve, acknowledge positively.

==================================================
SCENARIO-SPECIFIC FRAMING: {$eventTitle}
==================================================
{$scenarioBlock}
==================================================
THIRD RULE: HANDLE MIXED DECISIONS CORRECTLY
==================================================

Identify ALL major components. Let each outlet focus on parts it cares about most, but reflect the WHOLE decision accurately. Do not reduce hybrid decisions to one element.

==================================================
FOURTH RULE: HANDLE NONSENSICAL DECISIONS CORRECTLY
==================================================

If absurd, irrelevant, symbolic, comedic, or unrelated:
- Left: irresponsible, neglectful, harmful distraction from real suffering
- Center: irrelevant, unserious, disconnected from policy problem
- Right: weak, embarrassing, unserious leadership, failure to govern

==================================================
FIFTH RULE: STAY TIED TO THE SCENARIO
==================================================

Every article must clearly connect to: the actual crisis/scenario, the actual player decision, likely consequences. Do not write generic commentary or invent unrelated facts.

==================================================
OUTPUT FORMAT
==================================================

Return valid JSON:
{
    "left": {"headline": "...", "body": "2-3 sentences maximum"},
    "center": {"headline": "...", "body": "2-3 sentences maximum"},
    "right": {"headline": "...", "body": "2-3 sentences maximum"}
}

Style: headlines like media headlines, bodies SHORT and punchy (2-3 sentences max), avoid repetitive wording, avoid cartoonish stereotypes, each outlet distinct but realistic, no bullet points, no analysis notes, just finished media coverage.
EOT;
    }

    protected function buildZenNewsPrompt(string $decision, ?array $president = null): string
    {
        $presidentInfo = $president ? "\nPresident: {$president['name']} ({$president['party']})" : '';
        $scenarioFraming = $this->getScenarioFraming('Free Month - No Crisis');
        $scenarioBlock = $this->buildScenarioFramingBlock($scenarioFraming);
        
        return <<<EOT
You must follow the rules below exactly.

SCENARIO: A calm month - the president is taking initiative on their own terms.

PRESIDENT: "{$decision}"{$presidentInfo}

==================================================
FIRST RULE: UNDERSTAND THE DECISION BEFORE WRITING
==================================================

Silently analyze the decision and determine:
1. Is the decision: directly related to a reasonable initiative, symbolic, vague, contradictory, unrealistic, or just an odd personal choice?
2. What policy components are present? (subsidy, tax cut, welfare aid, deregulation, military action, enforcement, infrastructure, domestic production, renewable investment, diplomacy, education reform, healthcare, symbolic gesture, personal action, etc.)
3. Who is most likely helped, hurt, reassured, ignored, or angered?
4. Is the decision: short-term relief, long-term reform, mixed, mostly political symbolism, or just unusual?

==================================================
STRICT RULE: DO NOT HALLUCINATE
==================================================

You MUST strictly follow these rules:
- ONLY write about what the president EXPLICITLY said in their response
- Do NOT invent, assume, or add: tax cuts, deregulation, new spending, new programs, or any policy not mentioned
- Do NOT make up details about what the president "really means" or "will do next"
- If the president said "launch a study" - only write about launching a study. Nothing else.
- If the president said "visit the border" - only write about visiting the border. Nothing else.
- If the president gave vague answers - write about the vagueness. Do not fill in specifics.

CRITICAL: Each outlet MUST reference AT LEAST 2 specific elements from the president's actual response. If the response only has 1 element, all outlets must note this and comment on limitations.

==================================================
SECOND RULE: NEVER MAKE ALL 3 OUTLETS SOUND THE SAME
==================================================

They must differ in: what part they focus on, how they frame competence, how they judge priorities, how they interpret consequences, and what language they use.

Even when all 3 agree a decision is questionable, they must criticize it for DIFFERENT reasons.

==================================================
OUTLET DEFINITIONS
==================================================

1) LEFT-LEANING MEDIA
Priorities: workers, poor families, vulnerable groups, minorities, immigrants, ordinary citizens; fairness and equity; government responsibility and competence; whether real people are materially helped; whether policy benefits elites/corporations more than the public; public welfare, services, long-term social harm

Framing: asks who is helped and who is left behind; critical of neglect, inequality, performative politics, empty symbolism; may support state intervention if it helps ordinary people; skeptical of corporate favoritism, deregulation; if unserious/irrelevant, frame as irresponsible governance.

Do NOT auto-oppose everything. If policy helps workers, struggling families, or public welfare, acknowledge it.

2) CENTER MEDIA
Priorities: relevance of the decision; feasibility, implementation, cost, trade-offs, measurable effect; public reaction; institutional stability and seriousness; whether decision is practical, coherent, likely to work

Framing: calm, analytical, moderate; does NOT sound activist or ideological; evaluates what decision does and does not do; highlights trade-offs without being partisan; if silly/irrelevant, describe as raising questions about priorities and competence.

Do NOT make center secretly left or right-leaning. Focus on relevance, practicality, consequences.

3) RIGHT-LEANING MEDIA
Priorities: leadership strength, seriousness, national order, security, stability; limited government, fiscal restraint, skepticism of bureaucracy; domestic industry, national self-reliance, law and order, strong executive action; whether decision protects citizens, rewards work, avoids government excess

Framing: direct, sharper, forceful; favorable to strong action, domestic production, border enforcement, military strength, economic nationalism; skeptical of subsidies, bureaucracy, excessive spending, weak symbolic politics; if unserious/irrelevant, frame as weak leadership, embarrassing misprioritization, failure to govern seriously

Do NOT use left-wing anti-corporate language. Right criticism should sound like: weak leadership, government waste, lack of seriousness, overreach, misplaced priorities. NOT generic progressive rhetoric.

Do NOT auto-oppose everything. If decision includes stronger enforcement, domestic energy, national industry, executive resolve, acknowledge positively.

==================================================
SCENARIO-SPECIFIC FRAMING: Free Month
==================================================
{$scenarioBlock}
==================================================
THIRD RULE: HANDLE MIXED DECISIONS CORRECTLY
==================================================

Identify ALL major components. Let each outlet focus on parts it cares about most, but reflect the WHOLE decision accurately. Do not reduce hybrid decisions to one element.

==================================================
OUTPUT FORMAT
==================================================

Return valid JSON:
{
    "left": {"headline": "...", "body": "2-3 sentences maximum"},
    "center": {"headline": "...", "body": "2-3 sentences maximum"},
    "right": {"headline": "...", "body": "2-3 sentences maximum"}
}

Style: headlines like media headlines, bodies SHORT and punchy (2-3 sentences max), avoid repetitive wording, avoid cartoonish stereotypes, each outlet distinct but realistic, no bullet points, no analysis notes, just finished media coverage.
EOT;
    }

    protected function buildAnalysisPrompt(string $playerResponse, string $eventTitle, ?array $president = null): string
    {
        $ideologyDesc = $this->getIdeologyDescription($president);
        $partyName = $president['party'] ?? 'unknown';
        
        return <<<EOT
Rate this presidential response. Score from 0 to 10 where:
- 0 = catastrophic (worst possible)
- 5 = neutral/average
- 10 = excellent (best possible)

Examples:
- "Nuke a city" → 0, 0, 0 (catastrophic)
- "Blame victims" → 0, 0, 0 (catastrophic)
- "Chicken nuggets" → 0, 0, 0 (irrelevant)
- "Do nothing" → 1, 1, 1 (very bad)
- "Release oil reserves" → 7, 6, 5 (good response)
- "Strong leadership" → 8, 8, 8 (excellent)

CONTEXT:
- Scenario: "{$eventTitle}"
- Response: "{$playerResponse}"
- Party: {$partyName}

OUTPUT (JSON only):
{"approval": [0-10], "stability": [0-10], "party_support": [0-10], "label": "[brief label]"}
EOT;
    }

    protected function buildZenAnalysisPrompt(string $playerResponse, ?array $president = null): string
    {
        $ideologyDesc = $this->getIdeologyDescription($president);
        $partyName = $president['party'] ?? 'unknown';
        
        return <<<EOT
Rate this presidential action. Score from 0 to 10 where:
- 0 = catastrophic (worst possible)
- 5 = neutral/average
- 10 = excellent (best possible)

Examples:
- "Nuke a city" → 0, 0, 0 (catastrophic)
- "Chicken nuggets" → 0, 0, 0 (irrelevant)
- "Do nothing" → 1, 1, 1 (very bad)
- "Launch education program" → 7, 5, 6 (good)
- "Cut taxes successfully" → 8, 7, 4 (varies)

CONTEXT:
- Response: "{$playerResponse}"
- Party: {$partyName}

OUTPUT (JSON only):
{"approval": [0-10], "stability": [0-10], "party_support": [0-10], "label": "[brief label]"}
EOT;
    }

    protected function getIdeologyDescription(?array $president): string
    {
        $party = $president['party'] ?? '';
        $ideology = $president['ideology'] ?? '';

        // REPUBLICAN IDEOLOGIES
        if ($party === 'republican') {
            return match($ideology) {
                'hardcore' => "HARDCORE REPUBLICAN - Sees politics as conflict, not negotiation. Expects aggressive conservative action. Bold, nationalist framing rewarded. Moderate or cautious tone = weakness. Compromise with opposition = betrayal. Strong, decisive language and direct action please. Overly diplomatic tone punished.",
                'traditional' => "TRADITIONAL REPUBLICAN - Establishment conservative, values stability and order. Expects competent governance over ideology. Balanced, measured responses perform best. Pragmatic solutions rewarded. Extreme rhetoric or reckless actions punished. Values 'responsible leadership' and steady growth.",
                'swing' => "SWING/MODERATE REPUBLICAN - Outcome-driven, politically flexible. Not fully trusted by party base. Big swings in party support. Too far right = loses approval. Too moderate/left = loses party support. Smart positioning and appearing reasonable/reelectable rewarded.",
                default => "TRADITIONAL REPUBLICAN - Establishment conservative, expects competent governance with conservative principles.",
            };
        }

        // DEMOCRAT IDEOLOGIES
        if ($party === 'democrat') {
            return match($ideology) {
                'hardcore' => "HARDCORE DEMOCRAT - Progressive, activist-driven, sees 'moral responsibility to act'. Expects bold progressive action. Bold progressive stance and strong action on climate/inequality/rights please. Centrist compromise or weak action = betrayal. Urgency is expected.",
                'traditional' => "TRADITIONAL DEMOCRAT - Pragmatic, governance-first, ideology-second. 'Make the system work better.' Steady, competent responses perform best. Practical solutions and bipartisan tone rewarded. Radical proposals or indecisive leadership punished.",
                'swing' => "SWING/MODERATE DEMOCRAT - Centrist, electability-focused. Viewed as pragmatic by moderates, unreliable by progressive base. Always balancing political survival. POLICY: Flexible positioning. Leans left = party support up slightly, approval down. Leans center/right = approval up, strong base backlash. BEHAVIOR: Most volatile ideology, constantly under tradeoff pressure, rarely satisfies both party and public. REWARDS: From moderates - balanced, reasonable responses, stability messaging. From party only when leaning left - clear progressive action. PUNISHMENTS: From party - moderate/corporate responses = skepticism, weak progressive action = seen as uncommitted. From public - strong progressive moves = approval drops, overly ideological tone = loses moderate appeal. IDENTITY PENALTY: Inconsistency or over-balancing = inauthentic, trying to please both = reduced impact.",
                default => "TRADITIONAL DEMOCRAT - Pragmatic, governance-first, expects competent leadership with progressive values.",
            };
        }

        return "Moderate - balanced approach, expects competent governance.";
    }

    protected function buildVoterPrompt(string $decision, string $eventTitle, array $newsReactions, ?array $president = null): string
    {
        $presidentInfo = '';
        if ($president) {
            $presidentInfo = "\nPresident: {$president['name']} ({$president['party']})";
        }
        
        return <<<EOT
The president said: "{$decision}"{$presidentInfo}

About: {$eventTitle}

Write voter reactions. Support is 0-100.

IMPORTANT:
- Base the support rating on how THIS specific policy affects each group
- NOT all groups will agree - some may love it, some may hate it, some may be neutral
- Think about what this policy actually means for each demographic
- Corporate executives: only give high ratings if the policy helps business. If policy is anti-business, they should be LOW (20-40)
- Use COMPLETELY DIFFERENT numbers for each group - do NOT cluster them together

Reaction guide:
- Students: care about education, climate, social issues, debt
- Yuppie: care about career, housing, economy, tech
- Young conservatives: care about limited government, free markets, guns, traditional values
- Working class: care about jobs, wages, unions, trade
- Suburban: care about schools, safety, taxes, family
- Rural: care about farming, guns, religion, local communities
- Small business: care about regulations, taxes, customer spending
- Corporate: care about profits, markets, taxes, deregulation. Usually 40-70 range UNLESS policy directly helps them
- Public sector: care about government funding, jobs, unions
- Retirees: care about healthcare, Social Security, stability
- Minorities: care about equity, opportunity, discrimination
- Independents: care about pragmatism, results, not partisan

JSON (support is a NUMBER, not a range):
{
    "students": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "yuppie": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "young_conservatives": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "working_class": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "suburban": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "rural": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "small_business": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "corporate": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "public_sector": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "retirees": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "minorities": {"reaction": "2-3 sentences.", "support": [specific number 0-100]},
    "independents": {"reaction": "2-3 sentences.", "support": [specific number 0-100]}
}
EOT;
    }

    protected function callAPI(string $prompt): string
    {
        try {
            Log::info('Claude API called', ['prompt_length' => strlen($prompt), 'model' => $this->model]);
            
            $client = new Client($this->apiKey);
            
            $response = $client->messages->create(
                512, // max_tokens - keep responses SHORT
                [
                    [
                        'role' => 'user',
                        'content' => 'IMPORTANT: Keep all responses VERY SHORT. Headlines: 5-8 words max. Body text: 2 sentences ONLY, no more. Do not elaborate. Be concise.' . "\n\n" . $prompt
                    ]
                ],
                $this->model,
                ['timeout' => 30]
            );
            
            $content = $response->content[0]->text ?? '';
            Log::info('Claude API response', ['content_length' => strlen($content)]);
            return $content;
        } catch (\Exception $e) {
            Log::error('Claude API error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function parseNewsResponse(string $response): array
    {
        $json = $this->extractJSON($response);
        
        // Check if we have all required fields with content
        if ($json && 
            isset($json['left']['headline'], $json['left']['body']) &&
            isset($json['center']['headline'], $json['center']['body']) &&
            isset($json['right']['headline'], $json['right']['body']) &&
            strlen($json['left']['headline']) > 3 &&
            strlen($json['left']['body']) > 10 &&
            strlen($json['center']['headline']) > 3 &&
            strlen($json['center']['body']) > 10 &&
            strlen($json['right']['headline']) > 3 &&
            strlen($json['right']['body']) > 10
        ) {
            return $json;
        }
        
        return [
            'left' => ['headline' => 'News loading...', 'body' => 'Media coverage is being generated.'],
            'center' => ['headline' => 'News loading...', 'body' => 'Media coverage is being generated.'],
            'right' => ['headline' => 'News loading...', 'body' => 'Media coverage is being generated.'],
        ];
    }

    protected function parseVoterResponse(string $response): array
    {
        $json = $this->extractJSON($response);
        
        if (!$json) {
            return [
                'students' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'yuppie' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'young_conservatives' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'working_class' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'suburban' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'rural' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'small_business' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'corporate' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'public_sector' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'retirees' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'minorities' => ['reaction' => 'Reaction loading...', 'support' => 50],
                'independents' => ['reaction' => 'Reaction loading...', 'support' => 50],
            ];
        }
        
        return $json;
    }

    protected function parseAnalysisResponse(string $response): array
    {
        $json = $this->extractJSON($response);
        
        if ($json) {
            $approval = (int)($json['approval'] ?? 5);
            $stability = (int)($json['stability'] ?? 5);
            $partySupport = (int)($json['party_support'] ?? 5);
            
            return [
                'approval' => max(-10, min(10, ($approval - 5) * 2)),
                'stability' => max(-10, min(10, ($stability - 5) * 2)),
                'party_support' => max(-10, min(10, ($partySupport - 5) * 2)),
                'label' => $json['label'] ?? 'Player Decision',
            ];
        }
        
        return [
            'approval' => 0,
            'stability' => 0,
            'party_support' => 0,
            'label' => 'Player Decision',
        ];
    }

    public function generateConsequence(array $recentDecisions, ?array $president = null): ?array
    {
        $party = $president['party'] ?? 'independent';
        
        $decisionsText = '';
        foreach ($recentDecisions as $decision) {
            $decisionsText .= "- Turn {$decision['turn_number']}: \"{$decision['decision_text']}\" (Tags: " . implode(', ', $decision['decision_tags'] ?? []) . ")\n";
        }
        
        $prompt = <<<EOT
Based on the player's recent presidential decisions, generate a political consequence scenario.

PLAYER'S RECENT DECISIONS:
{$decisionsText}

Party: {$party}

Generate a realistic consequence that emerges from these decisions. This should be a NEW crisis or issue that arises as a direct result of their choices.

Examples:
- Hardline immigration enforcement → Detention center overcrowding scandal
- Oil subsidies → Budget deficit criticism or environmental backlash
- Healthcare expansion → Drug pricing controversy or hospital funding issues
- Tax cuts → Infrastructure funding shortfalls
- Strong foreign policy stance → Diplomatic tensions

Return JSON with:
{
    "title": "Consequence title (10-15 words)",
    "description": "Detailed description of the consequence scenario (2-3 sentences)",
    "tags": ["relevant", "policy", "tags"]
}

If the decisions don't warrant a consequence, return null.

OUTPUT (JSON only):
EOT;
        
        try {
            $response = $this->callAPI($prompt);
            $json = $this->extractJSON($response);
            
            if ($json && isset($json['title'])) {
                return [
                    'title' => $json['title'],
                    'description' => $json['description'] ?? 'A new challenge has emerged from your recent decisions.',
                    'tags' => $json['tags'] ?? [],
                ];
            }
        } catch (\Exception $e) {
            Log::error('AI consequence generation failed', ['error' => $e->getMessage()]);
        }
        
        return null;
    }

    public function generateStateReactions(string $playerResponse, array $president, array $states): array
    {
        $ideology = $president['ideology'] ?? 'moderate';
        $party = $president['party'] ?? 'independent';
        
        $stateDescriptions = [];
        foreach ($states as $state) {
            $bias = $state['policy_bias'] ?? [];
            $identity = $state['identity'] ?? [];
            $overview = $identity['overview'] ?? '';
            $priorities = implode(', ', $identity['priorities'] ?? []);
            
            $strongSupport = implode(', ', $bias['strong_support'] ?? []);
            $leanSupport = implode(', ', $bias['lean_support'] ?? []);
            $strongOppose = implode(', ', $bias['strong_oppose'] ?? []);
            $leanOppose = implode(', ', $bias['lean_oppose'] ?? []);
            
            $biasStr = "";
            if ($strongSupport) $biasStr .= "STRONGLY SUPPORTS: {$strongSupport}. ";
            if ($leanSupport) $biasStr .= "LEAN SUPPORT: {$leanSupport}. ";
            if ($strongOppose) $biasStr .= "STRONGLY OPPOSES: {$strongOppose}. ";
            if ($leanOppose) $biasStr .= "LEAN OPPOSE: {$leanOppose}.";
            
            $stateDescriptions[] = "- {$state['name']} ({$state['abbr']}): {$overview}";
            $stateDescriptions[] = "  Priorities: {$priorities}";
            $stateDescriptions[] = "  {$biasStr}";
        }
        
        $stateList = implode("\n", $stateDescriptions);
        
        $prompt = <<<EOT
You are analyzing how each US state would react to a presidential decision. 

CRITICAL FIRST STEP - RELEVANCE CHECK:
Before scoring, determine if the response is relevant to presidential governance:
- Does it address a real policy issue?
- Is it a coherent presidential action?
- Does it show understanding of the office?

If the response is IRRELEVANT, NONSENSICAL, or INCOMPETENT (e.g., "make angry birds themed party", "declare pizza the national food", gibberish, completely off-topic), then:
- ALL states should score between 5-30
- This represents universal condemnation of an unserious response from a president

If the response IS RELEVANT, then apply the scoring below.

PLAYER'S RESPONSE: "{$playerResponse}"
Party: {$party} | Ideology: {$ideology}

STATE IDENTITIES:
{$stateList}

RELEVANT RESPONSE SCORING:
- 80-100: Perfect alignment with state priorities
- 65-79: Strong support with minor concerns
- 55-64: Leans positive, some concerns
- 45-54: Neutral, mixed views
- 35-44: Leans negative, concerns outweigh benefits
- 20-34: Opposes, significant concerns
- 5-19: Strongly opposes, would campaign against

SCORING FACTORS:
1. Does it address the state's KEY priorities?
2. Does it match what the state STRONGLY SUPPORTS or STRONGLY OPPOSES?
3. Red states lean toward: border enforcement, fossil fuels, deregulation, tax cuts, military
4. Blue states lean toward: climate action, worker protection, public services, immigration rights
5. Swing states (PA, MI, AZ, GA, WI, NV, NC, NH, FL) are persuadable - score 40-60 for most policies

Return JSON: {"CA": 25, "TX": 18, "NY": 12, ...}

IMPORTANT: If the response is nonsense, ALL scores must be 5-30. If relevant, distribute across the full spectrum based on policy alignment.
EOT;
        
        try {
            $response = $this->callAPI($prompt);
            return $this->parseStateReactions($response, $states);
        } catch (\Exception $e) {
            Log::error('AI state reactions failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function parseStateReactions(string $response, array $states): array
    {
        $json = $this->extractJSON($response);
        
        if (!$json) {
            return [];
        }
        
        $reactions = [];
        foreach ($states as $state) {
            $abbr = $state['abbr'];
            if (isset($json[$abbr])) {
                $reactions[$abbr] = max(0, min(100, (int)$json[$abbr]));
            }
        }
        
        return $reactions;
    }
    
    protected function extractJSON(string $text): ?array
    {
        $text = trim($text);
        
        if (str_starts_with($text, '```json')) {
            $text = substr($text, 7);
        }
        if (str_starts_with($text, '```')) {
            $text = substr($text, 3);
        }
        if (str_ends_with($text, '```')) {
            $text = substr($text, 0, -3);
        }
        
        $text = trim($text);
        
        $json = json_decode($text, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }
        
        preg_match('/\{[\s\S]*\}/', $text, $matches);
        if (!empty($matches[0])) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }
        
        return null;
    }
}
