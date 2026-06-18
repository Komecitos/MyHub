<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freefire_spin_sessions', function (Blueprint $table) {
            $table->integer('starting_token')->default(0)->after('token_needed');
        });
    }

    public function down(): void
    {
        Schema::table('freefire_spin_sessions', function (Blueprint $table) {
            $table->dropColumn('starting_token');
        });
    }
};
