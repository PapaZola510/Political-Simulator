@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold">News Coverage</h1>
            <p class="text-sm text-muted-foreground">Decision: "{{ $turn->decision }}"</p>
            <p class="text-xs text-muted-foreground mt-1">Source: {{ ucfirst($newsPayload['source'] ?? 'fallback') }} @if(($newsPayload['source'] ?? '') === 'fallback') (AI fallback template applied) @endif</p>
        </div>

        @php
            $outlets = $newsPayload['outlets'] ?? [];
            $left = $outlets['left'] ?? null;
            $center = $outlets['center'] ?? null;
            $right = $outlets['right'] ?? null;
        @endphp

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-blue-500">{{ $left['name'] ?? "The People's Herald" }}</p>
                <h3 class="mb-2 text-lg font-bold">{{ $left['headline'] ?? 'Workers Question White House Crisis Plan' }}</h3>
                <p class="text-sm text-muted-foreground">{{ $left['body'] ?? 'Coverage unavailable.' }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $center['name'] ?? 'The Civic Report' }}</p>
                <h3 class="mb-2 text-lg font-bold">{{ $center['headline'] ?? 'Analysts Debate Feasibility Of Response' }}</h3>
                <p class="text-sm text-muted-foreground">{{ $center['body'] ?? 'Coverage unavailable.' }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-red-500">{{ $right['name'] ?? 'The Patriot Post' }}</p>
                <h3 class="mb-2 text-lg font-bold">{{ $right['headline'] ?? 'Leadership Faces Security Credibility Test' }}</h3>
                <p class="text-sm text-muted-foreground">{{ $right['body'] ?? 'Coverage unavailable.' }}</p>
            </div>
        </div>

        <a class="inline-flex h-11 w-full items-center justify-center rounded-md bg-primary px-5 text-sm font-semibold text-primary-foreground" href="{{ route('game.state_outlook', $game) }}" data-loading="Watching local politicians react...">
            View State Outlook
        </a>
    </div>
@endsection
