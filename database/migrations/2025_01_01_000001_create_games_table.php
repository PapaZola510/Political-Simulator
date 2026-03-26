<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('president_name');
            $table->string('president_party');
            $table->string('president_ideology');
            $table->integer('current_turn')->default(1);
            $table->integer('current_month')->default(1);
            $table->integer('current_year')->default(2025);
            $table->integer('approval')->default(50);
            $table->integer('stability')->default(50);
            $table->integer('party_support')->default(50);
            $table->string('current_phase')->default('dashboard');
            $table->boolean('is_active')->default(true);
            $table->json('game_state')->nullable();
            $table->json('used_events')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
