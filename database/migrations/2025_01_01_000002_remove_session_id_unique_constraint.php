<?php

use Illuminate\Support\Facades\DB;

public function up(): void
{
    if (DB::getDriverName() !== 'sqlite') {
        DB::statement('ALTER TABLE games DROP INDEX games_session_id_unique');
    }
}