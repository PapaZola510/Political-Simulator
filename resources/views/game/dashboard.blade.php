@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $game->president_name }}'s White House - {{ $monthName }} {{ $year }}</h1>
            <p class="text-sm text-muted-foreground">{{ $monthsUntilMidterm }} months before midterms</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('game.save', $game) }}">
                @csrf
                <button class="inline-flex h-10 items-center rounded-md border px-4 text-sm font-semibold hover:bg-muted" type="submit">Save</button>
            </form>
            <a class="inline-flex h-10 items-center rounded-md border px-4 text-sm font-semibold hover:bg-muted" href="{{ route('game.index') }}">Load</a>
        </div>
    </div>
    @if(session('saved'))
        <div class="mb-4 rounded-lg border border-green-500/40 bg-green-500/10 p-3 text-sm text-green-400">{{ session('saved') }}</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-12">
        <div class="space-y-4 lg:col-span-4">
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-4">
                <h2 class="mb-3 text-lg font-semibold">Core Lines</h2>
                @php
                    $stats = [
                        ['label' => 'Approval Rating', 'value' => $game->approval, 'delta' => $latestTurn?->approval_delta ?? 0, 'bar' => 'bg-green-500', 'bg' => 'bg-green-950', 'text' => 'text-green-500'],
                        ['label' => 'Government Stability', 'value' => $game->stability, 'delta' => $latestTurn?->stability_delta ?? 0, 'bar' => 'bg-blue-500', 'bg' => 'bg-blue-950', 'text' => 'text-blue-500'],
                        ['label' => 'Party Support', 'value' => $game->party_support, 'delta' => $latestTurn?->party_support_delta ?? 0, 'bar' => 'bg-purple-500', 'bg' => 'bg-purple-950', 'text' => 'text-purple-500'],
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach($stats as $stat)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">{{ $stat['label'] }}</span>
                                <div class="flex items-center gap-2">
                                    @if($stat['delta'] !== 0)
                                        <span class="font-medium {{ $stat['delta'] > 0 ? 'text-green-500' : 'text-red-500' }}">
                                            {{ $stat['delta'] > 0 ? '🟢' : '🔴' }} {{ $stat['delta'] > 0 ? '+' : '' }}{{ $stat['delta'] }}%
                                        </span>
                                    @endif
                                    <span class="font-semibold {{ $stat['text'] }}">{{ $stat['value'] }}%</span>
                                </div>
                            </div>
                            <div class="h-3 w-full rounded-full {{ $stat['bg'] }}">
                                <div class="h-full rounded-full {{ $stat['bar'] }}" style="width: {{ $stat['value'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 bg-card p-4">
                <h2 class="mb-3 text-lg font-semibold">Voter Opinions</h2>
                <div class="max-h-[230px] space-y-2 overflow-y-auto pr-2 text-sm">
                    @php
                        $voters = [
                            ['Student Activists','text-pink-600','Demand bold progressive action and strong moral leadership. Quickly turn against leaders seen as too moderate or performative.'],
                            ['Young Urban Professionals','text-purple-600','Prioritize economic growth, career opportunities, and stability. Support innovation but push back if policies threaten income or upward mobility.'],
                            ['Young Conservatives','text-indigo-600','Favor low taxes, limited government, and personal responsibility. Oppose heavy regulation and react strongly to policies seen as overreach.'],
                            ['Working-Class Urban Labor','text-orange-600','Focused on wages, job security, and cost of living. Support direct economic benefits but turn if they feel left behind or replaced.'],
                            ['Suburban Families','text-teal-600','Care about schools, safety, and affordability. Highly swing-oriented and shift based on real-life impact rather than ideology.'],
                            ['Rural Farmers','text-green-600','Concerned with trade, subsidies, fuel costs, and regulations. Distrust federal control but depend on support that protects their livelihood.'],
                            ['Small Business Owners','text-amber-600','Watch taxes, regulation, and consumer demand closely. Support pro-business policy but resist anything that increases operating pressure.'],
                            ['Corporate Executives','text-gray-500','Focus on market stability, profitability, and long-term predictability. Favor deregulation and react negatively to uncertainty or aggressive policy shifts.'],
                            ['Public Sector Workers','text-cyan-600','Depend on government funding, benefits, and job stability. Support expansion but oppose cuts, privatization, or restructuring threats.'],
                            ['Retirees & Seniors','text-red-600','Prioritize healthcare, Social Security, and financial stability. Strongly resist policies that risk benefits or increase living costs.'],
                            ['Minority Communities','text-violet-600','Value equity, access, and representation. Respond strongly to fairness issues and are sensitive to both policy impact and tone.'],
                            ['Independent Voters','text-slate-500','Outcome-driven and not tied to ideology. Support whoever delivers results and shift quickly when performance drops.'],
                        ];
                    @endphp
                    @foreach($voters as [$name, $color, $desc])
                        <div class="rounded-lg bg-muted/30 p-2.5">
                            <p class="font-bold {{ $color }}">{{ $name }}</p>
                            <p class="mt-1 text-muted-foreground">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-4 lg:col-span-8">
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-4">
                <h2 class="mb-3 text-lg font-semibold">Electoral Map</h2>
                <svg id="us-map" viewBox="0 0 960 600" class="max-h-[330px] w-full"></svg>
                <div id="hover-state" class="mt-4 hidden rounded-lg border bg-card p-4 text-sm"></div>
                <div class="mt-4 flex items-center gap-3 text-center">
                    <div class="flex-1 rounded-lg border border-blue-300 bg-blue-50 p-2.5 dark:border-blue-800 dark:bg-blue-950/30"><p id="dem-votes" class="text-xl font-bold text-blue-600">0</p><p class="text-xs sm:text-sm">Democrat</p></div>
                    <div class="flex-1 rounded-lg border border-yellow-300 bg-yellow-50 p-2.5 dark:border-yellow-800 dark:bg-yellow-950/30"><p id="swing-votes" class="text-xl font-bold text-yellow-600">0</p><p class="text-xs sm:text-sm">Swing</p></div>
                    <div class="flex-1 rounded-lg border border-red-300 bg-red-50 p-2.5 dark:border-red-800 dark:bg-red-950/30"><p id="rep-votes" class="text-xl font-bold text-red-600">0</p><p class="text-xs sm:text-sm">Republican</p></div>
                </div>
            </div>

            @php
                $isDem = str_contains(strtolower($game->president_party), 'dem');
                $buttonClass = $isDem ? 'bg-blue-600 hover:bg-blue-700' : 'bg-red-600 hover:bg-red-700';
            @endphp
            @if($midtermPopup)
                <div class="flex gap-3">
                    <a class="inline-flex h-11 flex-1 items-center justify-center rounded-md border px-5 text-base font-semibold hover:bg-muted" href="{{ route('game.index') }}">New / Load Game</a>
                </div>
            @else
                <a class="inline-flex h-11 w-full items-center justify-center rounded-md {{ $buttonClass }} px-5 text-base font-semibold text-white" href="{{ route('game.situation', $game) }}">Advance to Next Month</a>
            @endif
        </div>
    </div>

    @if($midtermPopup)
    <div id="midterm-popup" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-card rounded-2xl border-2 border-blue-500 shadow-2xl max-w-lg mx-4">
            <div class="bg-gradient-to-r from-red-600 via-purple-600 to-blue-600 px-6 py-7">
                <h2 class="text-3xl font-bold text-white text-center">MIDTERM SEASON</h2>
            </div>
            <div class="px-8 py-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-red-100 to-blue-100 dark:from-red-900/30 dark:to-blue-900/30 flex items-center justify-center">
                    <span class="text-3xl">🏆</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Your First Term Has Ended</h3>
                <p class="text-muted-foreground mb-4">Your party is now preparing for the midterm elections. Candidates are convening and planning their campaigns across the nation.</p>
                <div class="p-4 rounded-lg bg-muted/50 mb-6">
                    <p class="text-sm font-medium mb-2">Your Final Standing:</p>
                    <div class="flex justify-center gap-6 text-sm">
                        <div><span class="text-muted-foreground">Approval:</span><span class="font-bold ml-1">{{ $game->approval }}%</span></div>
                        <div><span class="text-muted-foreground">Stability:</span><span class="font-bold ml-1">{{ $game->stability }}%</span></div>
                        <div><span class="text-muted-foreground">Party:</span><span class="font-bold ml-1">{{ $game->party_support }}%</span></div>
                    </div>
                </div>
                <div class="border-t pt-4 space-y-3">
                    <a href="{{ route('game.score', $game) }}" class="w-full inline-flex h-12 items-center justify-center rounded-md bg-gradient-to-r from-red-600 to-blue-600 hover:opacity-90 text-white text-sm font-semibold gap-2">
                        <span>&#9733;</span> View Your Presidential Score
                    </a>
                    <div class="flex gap-3">
                        <button onclick="document.getElementById('midterm-popup').classList.add('hidden')" class="flex-1 inline-flex h-10 items-center justify-center rounded-md border text-sm font-semibold hover:bg-muted">View Dashboard</button>
                        <a href="{{ route('game.index') }}" class="flex-1 inline-flex h-10 items-center justify-center rounded-md bg-muted hover:bg-muted/80 text-sm font-semibold">New Presidency</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script type="module">
        import { geoAlbersUsa, geoPath } from 'https://esm.sh/d3-geo@3';
        import { feature } from 'https://esm.sh/topojson-client@3';

        const mapEl = document.getElementById('us-map');
        const hoverEl = document.getElementById('hover-state');
        const demVotesEl = document.getElementById('dem-votes');
        const swingVotesEl = document.getElementById('swing-votes');
        const repVotesEl = document.getElementById('rep-votes');

        const fipsToAbbr = {'01':'AL','02':'AK','04':'AZ','05':'AR','06':'CA','08':'CO','09':'CT','10':'DE','11':'DC','12':'FL','13':'GA','15':'HI','16':'ID','17':'IL','18':'IN','19':'IA','20':'KS','21':'KY','22':'LA','23':'ME','24':'MD','25':'MA','26':'MI','27':'MN','28':'MS','29':'MO','30':'MT','31':'NE','32':'NV','33':'NH','34':'NJ','35':'NM','36':'NY','37':'NC','38':'ND','39':'OH','40':'OK','41':'OR','42':'PA','44':'RI','45':'SC','46':'SD','47':'TN','48':'TX','49':'UT','50':'VT','51':'VA','53':'WA','54':'WV','55':'WI','56':'WY'};
        const stateData = @json($stateElectoralData);

        const alignment = (fips) => {
            const d = stateData[fips];
            if (!d) return 'Swing';
            const diff = d.dem - d.rep;
            if (diff > 5) return 'Democrat';
            if (diff < -5) return 'Republican';
            return 'Swing';
        };
        const color = (fips) => alignment(fips) === 'Democrat' ? '#3b82f6' : alignment(fips) === 'Republican' ? '#ef4444' : '#eab308';

        const calcVotes = () => {
            let dem = 0, swing = 0, rep = 0;
            Object.keys(stateData).forEach((fips) => {
                const a = alignment(fips);
                if (a === 'Democrat') dem += stateData[fips].votes;
                if (a === 'Republican') rep += stateData[fips].votes;
                if (a === 'Swing') swing += stateData[fips].votes;
            });
            demVotesEl.textContent = String(dem);
            swingVotesEl.textContent = String(swing);
            repVotesEl.textContent = String(rep);
        };
        calcVotes();

        fetch('https://cdn.jsdelivr.net/npm/us-atlas@3/states-10m.json')
            .then((r) => r.json())
            .then((topology) => {
                const states = feature(topology, topology.objects.states);
                const projection = geoAlbersUsa().fitSize([960, 600], states);
                const path = geoPath().projection(projection);

                mapEl.innerHTML = '';
                states.features.forEach((f) => {
                    const fips = String(f.id).padStart(2, '0');
                    const p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    p.setAttribute('d', path(f) || '');
                    p.setAttribute('fill', color(fips));
                    p.setAttribute('stroke', '#fff');
                    p.setAttribute('stroke-width', '1');
                    p.style.cursor = 'pointer';
                    p.addEventListener('mouseenter', () => {
                        const abbr = fipsToAbbr[fips] || '';
                        const data = stateData[fips] || { votes: 0, dem: 0, rep: 0 };
                        hoverEl.classList.remove('hidden');
                        hoverEl.innerHTML = `<div class="flex items-center justify-between mb-2"><strong>${f.properties.name} (${abbr})</strong><span>${alignment(fips)}</span></div><div class="flex gap-4"><span class="text-blue-500">Dem ${data.dem}%</span><span class="text-red-500">Rep ${data.rep}%</span><span class="ml-auto font-bold">${data.votes} EV</span></div>`;
                    });
                    p.addEventListener('mouseleave', () => hoverEl.classList.add('hidden'));
                    mapEl.appendChild(p);
                });
            });
    </script>

@endsection
