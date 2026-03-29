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
        Schema::create('turns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('turn_number');
            $table->string('crisis_title');
            $table->text('crisis_description');
            $table->text('decision');
            $table->boolean('used_custom_response')->default(false);
            $table->boolean('is_zen_month')->default(false);
            $table->integer('approval_delta');
            $table->integer('stability_delta');
            $table->integer('party_support_delta');
            $table->text('news');
            $table->json('voter_reactions');
            $table->json('state_reactions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turns');
    }
};
