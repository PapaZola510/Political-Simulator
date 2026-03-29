@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold">Voter Reaction Room</h1>
            <p class="text-sm text-muted-foreground">Decision: "{{ $turn->decision }}"</p>
        </div>

        @php
            $pastel = [
                'pink' => 'bg-pink-500/10 border-pink-500/30',
                'purple' => 'bg-purple-500/10 border-purple-500/30',
                'indigo' => 'bg-indigo-500/10 border-indigo-500/30',
                'orange' => 'bg-orange-500/10 border-orange-500/30',
                'teal' => 'bg-teal-500/10 border-teal-500/30',
                'green' => 'bg-green-500/10 border-green-500/30',
                'amber' => 'bg-amber-500/10 border-amber-500/30',
                'gray' => 'bg-gray-500/10 border-gray-500/30',
                'cyan' => 'bg-cyan-500/10 border-cyan-500/30',
                'red' => 'bg-red-500/10 border-red-500/30',
                'violet' => 'bg-violet-500/10 border-violet-500/30',
                'slate' => 'bg-slate-500/10 border-slate-500/30',
            ];
        @endphp

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($voterReactions as $group)
                @php
                    $support = (int) ($group['support'] ?? 50);
                    $ratingColor = $support < 40 ? 'text-red-500' : ($support < 60 ? 'text-orange-500' : 'text-green-500');
                    $cardTone = $pastel[$group['pastel'] ?? 'slate'] ?? 'bg-slate-500/10 border-slate-500/30';
                @endphp
                <div class="min-h-[180px] rounded-xl border p-4 {{ $cardTone }}">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="text-xl">{{ $group['emoji'] ?? '👤' }}</span>
                        <p class="text-base font-bold">{{ $group['name'] ?? 'Voter Group' }}</p>
                    </div>
                    <p class="mb-2 text-xs text-muted-foreground">{{ $group['issues'] ?? '' }}</p>
                    <div class="mb-2">
                        <span class="text-lg font-bold {{ $ratingColor }}">{{ $support }}/100</span>
                    </div>
                    <p class="text-sm leading-relaxed text-muted-foreground">{{ $group['reaction'] ?? 'No reaction generated.' }}</p>
                </div>
            @endforeach
        </div>

        <a class="inline-flex h-11 w-full items-center justify-center rounded-md bg-primary px-5 text-sm font-semibold text-primary-foreground" href="{{ route('game.dashboard', $game) }}" data-loading="Wasn't that bad was it?">
            Return to Dashboard
        </a>
    </div>
@endsection
