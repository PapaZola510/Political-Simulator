@extends('layouts.app')

@section('content')
    <h1 class="mb-1 text-2xl font-bold">Situation Room</h1>
    <p class="mb-6 text-sm text-muted-foreground">{{ $monthName }} {{ $year }}</p>

    <div class="rounded-xl border border-sidebar-border/70 bg-card p-6">
        <h2 class="mb-2 text-xl font-semibold">{{ $game->active_crisis_title }}</h2>
        <p class="mb-5 text-muted-foreground">{{ $game->active_crisis_description }}</p>

        <form method="POST" action="{{ route('game.decision', $game) }}">
            @csrf
            <h3 class="mb-2 mt-1 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Executive Decision</h3>
            <textarea class="w-full rounded-lg border bg-background p-3" name="custom_response" rows="4" placeholder="Write your course of action as President..."></textarea>
            <p class="mt-2 text-xs text-muted-foreground">The public evaluates your actual decision and applies consequences accordingly. Thoughtful, specific responses carry more weight than vague ones.</p>
            @error('custom_response')
                <p class="mt-2 text-sm font-semibold text-red-400">{{ $message }}</p>
            @enderror

            <div class="mt-4 flex gap-2">
                <button class="inline-flex h-11 items-center rounded-md bg-primary px-5 text-sm font-semibold text-primary-foreground" type="submit" data-loading="Getting cameras and presentation ready...">Execute Decision</button>
                <a class="inline-flex h-11 items-center rounded-md border px-5 text-sm font-semibold hover:bg-muted" href="{{ route('game.dashboard', $game) }}" data-loading="Wasn't that bad was it?">Back to Dashboard</a>
            </div>
        </form>
    </div>
@endsection
