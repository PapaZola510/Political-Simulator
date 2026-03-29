@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-blue-500 bg-card shadow-2xl">
        <div class="rounded-t-2xl bg-gradient-to-r from-red-600 via-purple-600 to-blue-600 px-6 py-4">
            <h2 class="text-center text-3xl font-bold text-white">{{ $game->status === 'won' ? 'COMPLETE TERM' : 'MIDTERM SEASON' }}</h2>
        </div>
        <div class="p-6 text-center">
        @if($game->status === 'won')
            <h1 class="mb-2 text-xl font-bold">Complete Term Victory</h1>
            <p class="mb-4 text-muted-foreground">You survived all 48 turns and remained in office.</p>
        @else
            <h1 class="mb-2 text-xl font-bold">Midterm Screen (Turn 24)</h1>
            <p class="mb-4 text-muted-foreground">You reached the midpoint of your presidency.</p>
        @endif

        <p class="mb-1"><strong>President:</strong> {{ $game->president_name }}</p>
        <p class="mb-5"><strong>Approval:</strong> {{ $game->approval }} | <strong>Stability:</strong> {{ $game->stability }} | <strong>Party Support:</strong> {{ $game->party_support }}</p>

        <div class="flex items-center justify-center gap-2">
            <a class="inline-flex h-11 items-center rounded-md bg-primary px-5 text-sm font-semibold text-primary-foreground" href="{{ route('game.dashboard', $game) }}">Return To Dashboard</a>
            <a class="inline-flex h-11 items-center rounded-md border px-5 text-sm font-semibold hover:bg-muted" href="{{ route('game.index') }}">New / Load Game</a>
        </div>
        </div>
    </div>
@endsection
