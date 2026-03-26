<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('session_id')->nullable()->change();
        });
        
        // Drop the unique index
        \DB::statement('ALTER TABLE games DROP INDEX games_session_id_unique');
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('session_id')->unique()->change();
        });
    }
};
