<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_saves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('save_name');
            $table->json('state_snapshot');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_saves');
    }
};
