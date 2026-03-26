<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presidents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->enum('party', ['democrat', 'republican']);
            $table->enum('age_group', ['40s', '50s', '60-70']);
            $table->enum('background', ['military', 'business', 'law', 'governor', 'senator', 'congress', 'outsider']);
            $table->enum('home_region', ['southern', 'west_coast', 'east_coast', 'rural', 'midwest', 'latino', 'urban']);
            $table->enum('ideology', ['hardcore', 'traditional', 'swing']);
            $table->enum('support_strength', ['landslide', 'comfortable', 'razor_thin', 'electoral']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presidents');
    }
};
