@extends('layouts.app')

@section('content')
    <h1 class="mb-2 text-3xl font-bold">Create Custom President</h1>
    <p class="mb-6 text-sm text-muted-foreground">Fill your profile and start your presidency.</p>

    <div class="rounded-xl border border-sidebar-border/70 bg-card p-6">
        <form method="POST" action="{{ route('game.store') }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            <input type="hidden" name="preset" value="Custom">

            <div>
                <label class="mb-2 block text-sm font-medium">Name</label>
                <input class="w-full rounded-lg border bg-background p-3" name="president_name" required>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Party</label>
                <select class="w-full rounded-lg border bg-background p-3" name="president_party" required>
                    <option value="">Select Party</option>
                    <option value="Republican">Republican</option>
                    <option value="Democrat">Democrat</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-2 block text-sm font-medium">Gender</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="gender" value="Male" required> Male
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="gender" value="Female" required> Female
                        </label>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">Background</label>
                    <select class="w-full rounded-lg border bg-background p-3" name="background" required>
                        <option value="">Select</option>
                        <option value="Senator">Senator</option>
                        <option value="Governor">Governor</option>
                        <option value="Mayor">Mayor</option>
                        <option value="Business">Business</option>
                        <option value="Mogul">Mogul</option>
                        <option value="Military">Military</option>
                        <option value="Law">Law</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Ideology</label>
                <select class="w-full rounded-lg border bg-background p-3" name="ideology" id="ideology-select" required>
                    <option value="">Select Ideology</option>
                    <option value="Hardcore">Hardcore — deeply polarizing, extreme base loyalty</option>
                    <option value="Traditional">Traditional — strong party alignment, clear partisan lean</option>
                    <option value="Moderate">Moderate — mild party pull, some crossover appeal</option>
                    <option value="Swing">Swing — built for the middle, resonates with swing states</option>
                </select>
                <p id="ideology-hint" class="mt-1 hidden text-xs text-muted-foreground"></p>
                <script>
                    (function () {
                        var hints = {
                            Hardcore:    'Base states react strongly for or against you depending on your decisions.',
                            Traditional: 'Party-aligned states respond noticeably to your decisions.',
                            Moderate:    'Small boost from own party, small drag from opposing — balanced.',
                            Swing:       'Swing states give you extra credit; partisan states treat you normally.',
                        };
                        var sel = document.getElementById('ideology-select');
                        var hint = document.getElementById('ideology-hint');
                        sel.addEventListener('change', function () {
                            var h = hints[sel.value];
                            if (h) { hint.textContent = h; hint.classList.remove('hidden'); }
                            else   { hint.textContent = '';  hint.classList.add('hidden'); }
                        });
                    })();
                </script>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Age</label>
                <select class="w-full rounded-lg border bg-background p-3" name="age" required>
                    <option value="">Select Age</option>
                    <option value="40s">40s</option>
                    <option value="50s">50s</option>
                    <option value="60s">60s</option>
                    <option value="70s">70s</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Party Support</label>
                <select class="w-full rounded-lg border bg-background p-3" name="party_support_hint" required>
                    <option value="">Select Support</option>
                    <option value="Landslide">Landslide — starts at 65, your party is firmly behind you</option>
                    <option value="Comfortable">Comfortable — starts at 60, solid but not unshakeable</option>
                    <option value="Razor-thin">Razor-thin — starts at 55, one bad turn could fracture it</option>
                </select>
                <p class="mt-1 text-xs text-muted-foreground">Drops below 25 and your own party invokes the 25th Amendment.</p>
            </div>

            <div class="md:col-span-2 flex items-center gap-2">
                <button class="inline-flex h-11 items-center rounded-md bg-primary px-5 text-sm font-semibold text-primary-foreground" type="submit">Start Game</button>
                <a class="inline-flex h-11 items-center rounded-md border px-5 text-sm font-semibold hover:bg-muted" href="{{ route('game.index') }}">Back</a>
            </div>
        </form>
    </div>
@endsection
