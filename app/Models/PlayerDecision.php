<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerDecision extends Model
{
    protected $fillable = [
        'game_id',
        'turn_number',
        'scenario_title',
        'decision_text',
        'decision_tags',
        'stat_changes',
    ];

    protected $casts = [
        'decision_tags' => 'array',
        'stat_changes' => 'array',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
