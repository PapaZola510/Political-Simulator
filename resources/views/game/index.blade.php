@extends('layouts.app')

@section('content')
    <h1 class="mb-2 text-3xl font-bold">Choose Your President</h1>
    <p class="mb-6 text-sm text-muted-foreground">Pick a preset or create a custom president.</p>
    @if(session('saved'))
        <div class="mb-4 rounded-lg border border-green-500/40 bg-green-500/10 p-3 text-sm text-green-400">{{ session('saved') }}</div>
    @endif

    <div class="mb-6 rounded-xl border border-sidebar-border/70 bg-card p-6">
        <h2 class="mb-4 text-xl font-semibold">Select President</h2>

        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-blue-400">Democrat</p>
        <div class="mb-5 flex flex-wrap justify-center gap-4">
            @foreach([
                ['AOC',    'Alexandria Ocasio-Cortez', 'Democratic', 'border-blue-500 bg-blue-500/10 hover:bg-blue-500/20', '+++ own party states', '--- opposing party states'],
                ['Harris', 'Kamala Harris',            'Democratic', 'border-blue-500 bg-blue-500/10 hover:bg-blue-500/20', '++ own party states',  '-- opposing party states'],
                ['Biden',  'Joe Biden',                'Democratic', 'border-blue-500 bg-blue-500/10 hover:bg-blue-500/20', '+ own party states',   '- opposing party states'],
                ['Newsom', 'Gavin Newsom',             'Democratic', 'border-blue-500 bg-blue-500/10 hover:bg-blue-500/20', '+ swing states',       'neutral elsewhere'],
            ] as [$pval, $pname, $pparty, $cls, $pol1, $pol2])
                <form method="POST" action="{{ route('game.store') }}">
                    @csrf
                    <input type="hidden" name="preset" value="{{ $pval }}">
                    <input type="hidden" name="president_name" value="{{ $pname }}">
                    <input type="hidden" name="president_party" value="{{ $pparty }}">
                    <button type="submit" class="flex h-32 w-44 flex-col items-center justify-center rounded-lg border-2 p-3 text-center transition {{ $cls }}">
                        <span class="text-sm font-bold leading-snug">{{ $pname }}</span>
                        <span class="mt-1 text-xs text-muted-foreground">{{ $pparty }}</span>
                        <span class="mt-2 text-xs text-muted-foreground">{{ $pol1 }}</span>
                        <span class="text-xs text-muted-foreground">{{ $pol2 }}</span>
                    </button>
                </form>
            @endforeach
        </div>

        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-red-400">Republican</p>
        <div class="mb-5 flex flex-wrap justify-center gap-4">
            @foreach([
                ['Trump',    'Donald Trump',  'Republican', 'border-red-500 bg-red-500/10 hover:bg-red-500/20', '+++ own party states', '--- opposing party states'],
                ['DeSantis', 'Ron DeSantis',  'Republican', 'border-red-500 bg-red-500/10 hover:bg-red-500/20', '++ own party states',  '-- opposing party states'],
                ['Vance',    'JD Vance',      'Republican', 'border-red-500 bg-red-500/10 hover:bg-red-500/20', '+ own party states',   '- opposing party states'],
                ['Haley',    'Nikki Haley',   'Republican', 'border-red-500 bg-red-500/10 hover:bg-red-500/20', '+ swing states',       'neutral elsewhere'],
            ] as [$pval, $pname, $pparty, $cls, $pol1, $pol2])
                <form method="POST" action="{{ route('game.store') }}">
                    @csrf
                    <input type="hidden" name="preset" value="{{ $pval }}">
                    <input type="hidden" name="president_name" value="{{ $pname }}">
                    <input type="hidden" name="president_party" value="{{ $pparty }}">
                    <button type="submit" class="flex h-32 w-44 flex-col items-center justify-center rounded-lg border-2 p-3 text-center transition {{ $cls }}">
                        <span class="text-sm font-bold leading-snug">{{ $pname }}</span>
                        <span class="mt-1 text-xs text-muted-foreground">{{ $pparty }}</span>
                        <span class="mt-2 text-xs text-muted-foreground">{{ $pol1 }}</span>
                        <span class="text-xs text-muted-foreground">{{ $pol2 }}</span>
                    </button>
                </form>
            @endforeach
        </div>

        <div class="my-4 text-center text-sm text-muted-foreground">---- OR ----</div>
        <div class="flex justify-center">
            <a href="{{ route('game.custom_president') }}" class="rounded-lg border bg-muted/50 px-6 py-2 text-sm font-semibold hover:bg-muted">Create Custom President</a>
        </div>
    </div>

    <div class="rounded-xl border border-sidebar-border/70 bg-card p-6">
        <h2 class="mb-4 text-xl font-semibold">Load Save</h2>
        @if($saves->isEmpty())
            <p class="text-sm text-muted-foreground">No saves yet.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                <tr>
                    <th class="border-b p-2 text-left">President</th>
                    <th class="border-b p-2 text-left">Date</th>
                    <th class="border-b p-2 text-left">Status</th>
                    <th class="border-b p-2 text-left">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($saves as $save)
                    <tr>
                        <td class="border-b p-2">{{ $save->president_name }} ({{ $save->president_party }})</td>
                        <td class="border-b p-2">{{ $save->date_label }}</td>
                        <td class="border-b p-2">{{ ucfirst($save->status) }}</td>
                        <td class="border-b p-2">
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('game.load', $save) }}">
                                    @csrf
                                    <button class="inline-flex h-9 items-center rounded-md border px-3 text-xs font-semibold hover:bg-muted" type="submit">Load</button>
                                </form>
                                <form method="POST" action="{{ route('game.destroy', $save) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex h-9 items-center rounded-md border border-red-500/50 px-3 text-xs font-semibold text-red-400 hover:bg-red-500/10" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
