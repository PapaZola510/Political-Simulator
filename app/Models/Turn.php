<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turn extends Model
{
    protected $fillable = [
        'game_id',
        'turn_number',
        'crisis_title',
        'crisis_description',
        'decision',
        'used_custom_response',
        'is_zen_month',
        'approval_delta',
        'stability_delta',
        'party_support_delta',
        'news',
        'news_payload',
        'voter_reactions',
        'state_reactions',
    ];

    protected $casts = [
        'used_custom_response' => 'boolean',
        'is_zen_month' => 'boolean',
        'news_payload' => 'array',
        'voter_reactions' => 'array',
        'state_reactions' => 'array',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
