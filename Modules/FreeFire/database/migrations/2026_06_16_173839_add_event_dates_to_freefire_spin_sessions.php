<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freefire_spin_sessions', function (Blueprint $table) {
            $table->date('event_start')->nullable()->after('status');
            $table->date('event_end')->nullable()->after('event_start');
        });
    }

    public function down(): void
    {
        Schema::table('freefire_spin_sessions', function (Blueprint $table) {
            $table->dropColumn(['event_start', 'event_end']);
        });
    }
};
