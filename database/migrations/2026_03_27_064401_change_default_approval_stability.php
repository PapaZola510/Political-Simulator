<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval')->default(50)->change();
            $table->unsignedTinyInteger('stability')->default(50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval')->default(55)->change();
            $table->unsignedTinyInteger('stability')->default(55)->change();
        });
    }
};
