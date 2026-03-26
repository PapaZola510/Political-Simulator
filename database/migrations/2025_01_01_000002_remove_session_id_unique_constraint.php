<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE games DROP INDEX games_session_id_unique');
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS games_session_id_unique');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE games ADD UNIQUE KEY games_session_id_unique (session_id)');
        } elseif ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX games_session_id_unique ON games (session_id)');
        }
    }
};