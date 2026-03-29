@extends('layouts.app')

@section('content')
@php
    $gc = $gradeColors[$grade];
    $pt = $tierMeta[$playerTier];

    $presetIdeologies = [
        'Trump'    => 'Hardcore',
        'AOC'      => 'Hardcore',
        'Harris'   => 'Traditional',
        'DeSantis' => 'Traditional',
        'Biden'    => 'Moderate',
        'Vance'    => 'Moderate',
        'Newsom'   => 'Swing',
        'Haley'    => 'Swing',
    ];
    $displayIdeology = $game->ideology
        ?? $presetIdeologies[$game->preset ?? '']
        ?? 'Independent';
@endphp

<div class="mx-auto max-w-4xl px-4 py-10 space-y-10">

    {{-- Header --}}
    <div class="text-center space-y-1">
        <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Presidential Legacy Report</p>
        <h1 class="text-3xl font-bold">{{ $game->president_name }}</h1>
        <p class="text-muted-foreground">{{ $displayIdeology }} {{ $game->president_party }}</p>
    </div>

    {{-- Grade Card --}}
    <div class="rounded-2xl border-2 {{ $gc['border'] }} {{ $gc['bg'] }} px-8 py-10 text-center shadow-lg space-y-4">
        <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Your Rating as President</p>
        <div class="text-9xl font-black {{ $gc['text'] }} leading-none">{{ $grade }}</div>
        <p class="text-lg font-medium max-w-xl mx-auto leading-snug">{{ $gradeDescriptions[$grade] }}</p>

        <div class="flex justify-center gap-8 text-sm pt-2">
            <div class="text-center">
                <div class="text-2xl font-bold">{{ $game->approval }}%</div>
                <div class="text-muted-foreground text-xs uppercase tracking-wide mt-0.5">Approval</div>
            </div>
            <div class="w-px bg-border"></div>
            <div class="text-center">
                <div class="text-2xl font-bold">{{ $game->stability }}%</div>
                <div class="text-muted-foreground text-xs uppercase tracking-wide mt-0.5">Stability</div>
            </div>
            <div class="w-px bg-border"></div>
            <div class="text-center">
                <div class="text-2xl font-bold">{{ $game->party_support }}%</div>
                <div class="text-muted-foreground text-xs uppercase tracking-wide mt-0.5">Party Support</div>
            </div>
        </div>
    </div>

    {{-- Historical placement --}}
    <div class="text-center space-y-1">
        <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Historical Standing</p>
        <p class="text-2xl font-bold leading-snug">
            The American public would place you as the
            <span class="{{ $gc['text'] }}">{{ $ordinal }}</span>
            best president in American history.
        </p>
    </div>

    {{-- Two boxes --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Box 1: Tier View --}}
        <div class="rounded-2xl bg-card shadow-md overflow-hidden">
            <div class="px-5 py-4 border-b">
                <h2 class="font-bold text-base">Tier Ranking</h2>
                <p class="text-xs text-muted-foreground mt-0.5">Where your presidency stands among historical tiers</p>
            </div>
            <div class="divide-y">
                @foreach ($visibleTierKeys as $key)
                @php
                    $meta     = $tierMeta[$key];
                    $isPlayer = ($key === $playerTier);
                    $names    = $groupedByTier[$key] ?? [];
                @endphp
                <div class="px-5 py-4 {{ $isPlayer ? $meta['bg'] . ' ring-2 ring-inset ' . $meta['ring'] : '' }}">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-md text-xs font-black {{ $meta['badge'] }}">{{ $key }}</span>
                        <span class="font-semibold text-sm">{{ $meta['label'] }}</span>
                        @if ($isPlayer)
                            <span class="ml-auto text-xs font-bold {{ $gc['text'] }} uppercase tracking-wide">YOU ARE HERE</span>
                        @endif
                    </div>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        {!! implode(' &bull; ', $names) !!}
                        @if ($isPlayer)
                            &bull; <span class="font-semibold text-foreground">{{ $game->president_name }}</span>
                        @endif
                    </p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Box 2: Individual Ranking --}}
        <div class="rounded-2xl bg-card shadow-md overflow-hidden">
            <div class="px-5 py-4 border-b">
                <h2 class="font-bold text-base">Individual Ranking</h2>
                <p class="text-xs text-muted-foreground mt-0.5">Your place among individual presidents in history</p>
            </div>
            <div class="divide-y">
                @foreach ($window as $entry)
                @php $isYou = $entry['isPlayer']; @endphp
                <div class="flex items-center gap-3 px-10 py-3.5 {{ $isYou ? $pt['bg'] . ' ring-2 ring-inset ' . $pt['ring'] : '' }}">
                    <span class="text-sm font-mono font-bold w-8 shrink-0 {{ $isYou ? $gc['text'] : 'text-muted-foreground' }}">#{{ $entry['rank'] }}</span>
                    <span class="text-sm {{ $isYou ? 'font-bold' : 'font-medium' }}">{{ $entry['name'] }}</span>
                    @if ($isYou)
                        <span class="ml-auto text-xs font-bold {{ $gc['text'] }} uppercase tracking-wide shrink-0">You</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('game.dashboard', $game) }}" class="inline-flex h-11 items-center justify-center rounded-md border px-6 text-sm font-semibold hover:bg-muted">
            Return to Dashboard
        </a>
        <a href="{{ route('game.index') }}" class="inline-flex h-11 items-center justify-center rounded-md bg-gradient-to-r from-red-600 to-blue-600 hover:opacity-90 text-white px-6 text-sm font-semibold">
            Start New Presidency
        </a>
    </div>

    {{-- Attribution --}}
    <p class="text-center text-xs text-muted-foreground pb-4">
        Historical presidential rankings are based on the <span class="font-semibold">C-SPAN Historians Survey</span> and reflect scholarly and public polling consensus.
    </p>

</div>
@endsection
