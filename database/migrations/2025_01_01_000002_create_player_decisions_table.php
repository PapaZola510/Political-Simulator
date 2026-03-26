<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->integer('turn_number');
            $table->string('scenario_title')->nullable();
            $table->text('decision_text');
            $table->json('decision_tags')->nullable();
            $table->json('stat_changes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_decisions');
    }
};
