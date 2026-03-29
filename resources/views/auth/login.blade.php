@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md">
        <h1 class="mb-1 text-2xl font-bold">Log in to your account</h1>
        <p class="mb-6 text-sm text-muted-foreground">Enter your email and password below.</p>

        <form class="space-y-4 rounded-xl border border-sidebar-border/70 bg-card p-6">
            <div>
                <label class="mb-2 block text-sm font-medium">Email address</label>
                <input type="email" class="w-full rounded-lg border bg-background p-3" placeholder="email@example.com">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Password</label>
                <input type="password" class="w-full rounded-lg border bg-background p-3" placeholder="Password">
            </div>
            <button type="button" class="inline-flex h-11 w-full items-center justify-center rounded-md bg-primary px-5 text-sm font-semibold text-primary-foreground">Log in</button>
            <p class="text-center text-xs text-muted-foreground">Auth backend is not connected in this prototype.</p>
        </form>
    </div>
@endsection
