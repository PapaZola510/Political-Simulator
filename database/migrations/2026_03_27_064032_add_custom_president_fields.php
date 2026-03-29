<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('ideology')->nullable();
            $table->string('age')->nullable();
            $table->string('background')->nullable();
            $table->string('party_support_hint')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['ideology', 'age', 'background', 'party_support_hint']);
        });
    }
};
