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
        Schema::table('games', function (Blueprint $table) {
            $table->json('last_news_payload')->nullable()->after('last_news');
        });

        Schema::table('turns', function (Blueprint $table) {
            $table->json('news_payload')->nullable()->after('news');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('last_news_payload');
        });

        Schema::table('turns', function (Blueprint $table) {
            $table->dropColumn('news_payload');
        });
    }
};
