<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freefire_spin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('freefire_spin_sessions')->onDelete('cascade');
            $table->integer('spin_number');
            $table->integer('diamond_spent');
            $table->string('result')->nullable(); // dapat apa
            $table->integer('token_gained')->default(0); // token dapat (token ring)
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freefire_spin_logs');
    }
};