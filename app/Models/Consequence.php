<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consequence extends Model
{
    protected $fillable = [
        'game_id',
        'title',
        'description',
        'trigger_turn',
        'is_resolved',
        'is_shown',
        'trigger_tags',
    ];

    protected $casts = [
        'trigger_tags' => 'array',
        'is_resolved' => 'boolean',
        'is_shown' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
