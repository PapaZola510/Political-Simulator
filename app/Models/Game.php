<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Game extends Model
{
    protected $fillable = [
        'session_id',
        'president_name',
        'president_party',
        'president_ideology',
        'current_turn',
        'current_month',
        'current_year',
        'approval',
        'stability',
        'party_support',
        'current_phase',
        'is_active',
        'game_state',
        'used_events',
    ];

    protected $casts = [
        'game_state' => 'array',
        'used_events' => 'array',
        'is_active' => 'boolean',
    ];

    public function decisions(): HasMany
    {
        return $this->hasMany(PlayerDecision::class);
    }

    public function consequences(): HasMany
    {
        return $this->hasMany(Consequence::class);
    }

    public function saves(): HasMany
    {
        return $this->hasMany(GameSave::class);
    }
}
