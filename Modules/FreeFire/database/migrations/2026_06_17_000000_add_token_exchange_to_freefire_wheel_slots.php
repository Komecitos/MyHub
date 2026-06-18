<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freefire_wheel_slots', function (Blueprint $table) {
            $table->unsignedInteger('token_exchange')->nullable()->after('item_name');
        });
    }

    public function down(): void
    {
        Schema::table('freefire_wheel_slots', function (Blueprint $table) {
            $table->dropColumn('token_exchange');
        });
    }
};
