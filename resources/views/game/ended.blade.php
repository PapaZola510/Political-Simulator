@extends('layouts.app')

@section('content')
@php
    $reason = $game->loss_reason ?? 'Impeachment';
    $configs = [
        'Impeachment' => [
            'title'      => 'IMPEACHED',
            'heading'    => 'Congress Has Impeached the President',
            'body'       => 'Your approval rating dropped to 25%. With public confidence completely eroded, Congress has moved to impeach and remove you from office.',
            'stat_label' => 'Approval Rating',
            'stat_value' => $game->approval,
        ],
        'Overthrown' => [
            'title'      => 'OVERTHROWN',
            'heading'    => 'The Government Has Been Overthrown',
            'body'       => 'Your government stability has collapsed to 25%. With the government in chaos and institutions failing, a coup has overthrown your administration.',
            'stat_label' => 'Government Stability',
            'stat_value' => $game->stability,
        ],
        '25th Amendment' => [
            'title'      => '25TH AMENDMENT INVOKED',
            'heading'    => 'The Vice President Takes Office',
            'body'       => 'Your party support has plummeted to 25%. With your own party abandoning you, the Cabinet has invoked the 25th Amendment. Your Vice President has assumed the presidency.',
            'stat_label' => 'Party Support',
            'stat_value' => $game->party_support,
        ],
    ];
    $config = $configs[$reason] ?? $configs['Impeachment'];
@endphp

    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-card mx-4 max-w-md rounded-2xl border-2 border-red-500 shadow-2xl">
            <div class="bg-red-600 px-6 py-7">
                <h2 class="text-center text-3xl font-bold text-white">{{ $config['title'] }}</h2>
            </div>
            <div class="px-6 py-12 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <span class="text-3xl">🚨</span>
                </div>
                <h3 class="mb-2 text-xl font-bold">{{ $config['heading'] }}</h3>
                <p class="mb-4 text-muted-foreground">{{ $config['body'] }}</p>
                <div class="mb-6 inline-flex items-center gap-2 rounded-lg bg-red-100 px-4 py-2 text-red-700 dark:bg-red-900/30 dark:text-red-300">
                    <span class="font-semibold">{{ $config['stat_label'] }}:</span>
                    <span class="font-bold">{{ $config['stat_value'] }}%</span>
                </div>
                <div class="border-t pt-4">
                    <a href="{{ route('game.index') }}" class="inline-flex h-12 w-full items-center justify-center rounded-md bg-red-600 text-lg font-semibold text-white hover:bg-red-700">
                        Start New Presidency
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
