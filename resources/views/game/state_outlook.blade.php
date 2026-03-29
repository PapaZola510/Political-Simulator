@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold">State Outlook</h1>
            <p class="text-sm text-muted-foreground">How all 50 states reacted to your decision this turn.</p>
        </div>

        <div class="flex flex-col gap-6 lg:flex-row">
            <div class="flex-1">
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-6">
                    <svg id="state-outlook-map" viewBox="0 0 960 600" class="max-h-[450px] w-full"></svg>
                    <div id="state-tooltip" class="mt-4 hidden rounded-lg border bg-card p-4 text-sm"></div>

                    <div class="mt-6 grid grid-cols-2 gap-2 text-center md:grid-cols-7">
                        @php
                            $bands = [
                                ['Strongly Supports', 'bg-green-900/30', 'text-green-500'],
                                ['Supports', 'bg-green-700/30', 'text-green-400'],
                                ['Leans Support', 'bg-lime-700/30', 'text-lime-400'],
                                ['Neutral', 'bg-yellow-700/30', 'text-yellow-400'],
                                ['Leans Oppose', 'bg-orange-700/30', 'text-orange-400'],
                                ['Opposes', 'bg-orange-900/30', 'text-orange-500'],
                                ['Strongly Opposes', 'bg-red-900/30', 'text-red-500'],
                            ];
                        @endphp
                        @foreach($bands as [$label, $bg, $text])
                            <div class="rounded-lg border p-2 {{ $bg }}">
                                <p class="text-lg font-bold {{ $text }}">{{ $bandCounts[$label] ?? 0 }}</p>
                                <p class="text-xs text-muted-foreground">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-center gap-3 text-xs text-muted-foreground">
                        <span class="inline-flex items-center gap-1"><span class="h-3 w-5 rounded-sm bg-green-900"></span>Strongly Supports</span>
                        <span class="inline-flex items-center gap-1"><span class="h-3 w-5 rounded-sm bg-green-500"></span>Supports</span>
                        <span class="inline-flex items-center gap-1"><span class="h-3 w-5 rounded-sm bg-lime-500"></span>Leans Support</span>
                        <span class="inline-flex items-center gap-1"><span class="h-3 w-5 rounded-sm bg-yellow-400"></span>Neutral</span>
                        <span class="inline-flex items-center gap-1"><span class="h-3 w-5 rounded-sm bg-orange-500"></span>Leans Oppose</span>
                        <span class="inline-flex items-center gap-1"><span class="h-3 w-5 rounded-sm bg-orange-700"></span>Opposes</span>
                        <span class="inline-flex items-center gap-1"><span class="h-3 w-5 rounded-sm bg-red-600"></span>Strongly Opposes</span>
                    </div>
                </div>
            </div>

            <div class="w-full space-y-4 lg:w-72">
                <div class="rounded-xl border border-green-600/40 bg-green-900/10 p-4">
                    <h2 class="mb-3 text-sm font-semibold text-green-400">Top 5 Supporters</h2>
                    <div class="space-y-2 text-sm">
                        @foreach($topSupporters as $idx => $state)
                            <div class="flex items-center justify-between">
                                <span>{{ $idx + 1 }}. {{ $state['state'] }}</span>
                                <span class="font-bold text-green-400">{{ $state['score'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-xl border border-red-600/40 bg-red-900/10 p-4">
                    <h2 class="mb-3 text-sm font-semibold text-red-400">Top 5 Opposers</h2>
                    <div class="space-y-2 text-sm">
                        @foreach($topOpposers as $idx => $state)
                            <div class="flex items-center justify-between">
                                <span>{{ $idx + 1 }}. {{ $state['state'] }}</span>
                                <span class="font-bold text-red-400">{{ $state['score'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @php
                    $avgScore = count($stateReactions) ? round(array_sum(array_column($stateReactions, 'score')) / count($stateReactions)) : 0;
                    $avgColor = $avgScore >= 55 ? 'text-green-400' : ($avgScore >= 45 ? 'text-yellow-400' : 'text-red-400');
                @endphp
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-4 text-center">
                    <p class="text-xs text-muted-foreground">National Average Score</p>
                    <p class="mt-1 text-2xl font-bold {{ $avgColor }}">{{ $avgScore }}</p>
                    <p class="text-xs text-muted-foreground">out of 100</p>
                </div>
            </div>
        </div>

        <a class="inline-flex h-11 w-full items-center justify-center rounded-md bg-primary px-5 text-sm font-semibold text-primary-foreground" href="{{ route('game.voter_reaction', $game) }}" data-loading="Checking Twitter and Facebook...">
            View Voter Reactions
        </a>
    </div>

    <script type="module">
        import { geoAlbersUsa, geoPath } from 'https://esm.sh/d3-geo@3';
        import { feature } from 'https://esm.sh/topojson-client@3';

        const reactions = @json($stateReactions);
        const byAbbr = Object.fromEntries((reactions || []).map((s) => [s.abbr, s]));
        const fipsToAbbr = {'01':'AL','02':'AK','04':'AZ','05':'AR','06':'CA','08':'CO','09':'CT','10':'DE','11':'DC','12':'FL','13':'GA','15':'HI','16':'ID','17':'IL','18':'IN','19':'IA','20':'KS','21':'KY','22':'LA','23':'ME','24':'MD','25':'MA','26':'MI','27':'MN','28':'MS','29':'MO','30':'MT','31':'NE','32':'NV','33':'NH','34':'NJ','35':'NM','36':'NY','37':'NC','38':'ND','39':'OH','40':'OK','41':'OR','42':'PA','44':'RI','45':'SC','46':'SD','47':'TN','48':'TX','49':'UT','50':'VT','51':'VA','53':'WA','54':'WV','55':'WI','56':'WY'};

        const colorForBand = (band) => ({
            'Strongly Supports': '#14532d',
            'Supports': '#22c55e',
            'Leans Support': '#84cc16',
            'Neutral': '#facc15',
            'Leans Oppose': '#f97316',
            'Opposes': '#c2410c',
            'Strongly Opposes': '#dc2626',
        }[band] || '#facc15');

        const svg = document.getElementById('state-outlook-map');
        const tooltip = document.getElementById('state-tooltip');

        fetch('https://cdn.jsdelivr.net/npm/us-atlas@3/states-10m.json')
            .then((r) => r.json())
            .then((topology) => {
                const states = feature(topology, topology.objects.states);
                const projection = geoAlbersUsa().fitSize([960, 600], states);
                const path = geoPath().projection(projection);

                svg.innerHTML = '';
                states.features.forEach((f) => {
                    const fips = String(f.id).padStart(2, '0');
                    const abbr = fipsToAbbr[fips] || '';
                    const info = byAbbr[abbr] || { score: 50, band: 'Neutral', is_competitive: false, state: f.properties.name };
                    const p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    p.setAttribute('d', path(f) || '');
                    p.setAttribute('fill', colorForBand(info.band));
                    p.setAttribute('stroke', '#fff');
                    p.setAttribute('stroke-width', '1');
                    p.style.cursor = 'pointer';
                    p.addEventListener('mouseenter', () => {
                        tooltip.classList.remove('hidden');
                        tooltip.innerHTML = `<div class="flex items-center gap-2"><strong>${info.state} (${abbr})</strong>${info.is_competitive ? '<span class="rounded bg-orange-600/20 px-2 py-0.5 text-xs text-orange-300">Competitive</span>' : ''}</div><div class="mt-2 flex gap-4"><span>Score: <strong>${info.score}</strong></span><span>Band: <strong>${info.band}</strong></span></div>`;
                    });
                    p.addEventListener('mouseleave', () => tooltip.classList.add('hidden'));
                    svg.appendChild(p);
                });
            });
    </script>
@endsection
