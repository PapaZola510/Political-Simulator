<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turns', function (Blueprint $table) {
            $table->text('news')->nullable()->change();
            $table->json('voter_reactions')->nullable()->change();
            $table->json('state_reactions')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('turns', function (Blueprint $table) {
            $table->text('news')->nullable(false)->change();
            $table->json('voter_reactions')->nullable(false)->change();
            $table->json('state_reactions')->nullable(false)->change();
        });
    }
};
