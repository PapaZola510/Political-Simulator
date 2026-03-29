<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('president_name');
            $table->string('president_party');
            $table->string('preset')->nullable();
            $table->unsignedTinyInteger('turn_number')->default(0);
            $table->unsignedTinyInteger('approval')->default(55);
            $table->unsignedTinyInteger('stability')->default(55);
            $table->unsignedTinyInteger('party_support')->default(55);
            $table->unsignedSmallInteger('pressure_score')->default(0);
            $table->string('status')->default('active');
            $table->string('loss_reason')->nullable();
            $table->boolean('midterm_seen')->default(false);
            $table->boolean('last_turn_zen')->default(false);
            $table->string('active_crisis_title')->nullable();
            $table->text('active_crisis_description')->nullable();
            $table->json('active_crisis_options')->nullable();
            $table->text('last_decision')->nullable();
            $table->text('last_news')->nullable();
            $table->json('last_voter_reactions')->nullable();
            $table->json('last_state_reactions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
