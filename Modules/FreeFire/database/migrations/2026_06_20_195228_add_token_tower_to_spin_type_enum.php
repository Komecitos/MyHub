<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE freefire_spin_sessions MODIFY COLUMN spin_type ENUM('token_ring', 'faded_wheel', 'token_tower') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE freefire_spin_sessions MODIFY COLUMN spin_type ENUM('token_ring', 'faded_wheel') NOT NULL");
    }
};
