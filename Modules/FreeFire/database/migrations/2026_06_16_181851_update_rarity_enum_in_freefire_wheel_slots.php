<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE freefire_wheel_slots MODIFY COLUMN rarity ENUM('epic', 'legendary', 'artifact') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE freefire_wheel_slots MODIFY COLUMN rarity ENUM('common', 'rare', 'epic', 'artifact') NULL");
    }
};