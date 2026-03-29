<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'president_name',
        'president_party',
        'preset',
        'ideology',
        'age',
        'background',
        'gender',
        'party_support_hint',
        'turn_number',
        'approval',
        'stability',
        'party_support',
        'pressure_score',
        'status',
        'loss_reason',
        'midterm_seen',
        'last_turn_zen',
        'active_crisis_title',
        'active_crisis_description',
        'active_crisis_options',
        'last_decision',
        'last_news',
        'last_news_payload',
        'last_voter_reactions',
        'last_state_reactions',
    ];

    protected $casts = [
        'active_crisis_options' => 'array',
        'last_news_payload' => 'array',
        'last_voter_reactions' => 'array',
        'last_state_reactions' => 'array',
        'midterm_seen' => 'boolean',
        'last_turn_zen' => 'boolean',
    ];

    public function turns()
    {
        return $this->hasMany(Turn::class);
    }
}
