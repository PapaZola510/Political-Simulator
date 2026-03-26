<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSave extends Model
{
    protected $fillable = [
        'game_id',
        'save_name',
        'state_snapshot',
    ];

    protected $casts = [
        'state_snapshot' => 'array',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
