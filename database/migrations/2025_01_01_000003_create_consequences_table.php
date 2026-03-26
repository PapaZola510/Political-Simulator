<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->integer('trigger_turn');
            $table->boolean('is_resolved')->default(false);
            $table->boolean('is_shown')->default(false);
            $table->json('trigger_tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consequences');
    }
};
