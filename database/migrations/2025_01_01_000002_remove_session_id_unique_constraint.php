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
            DB::statement('ALTER TABLE games DROP CONSTRAINT IF EXISTS games_session_id_unique');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE games ADD UNIQUE KEY games_session_id_unique (session_id)');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE games ADD CONSTRAINT games_session_id_unique UNIQUE (session_id)');
        }
    }
};