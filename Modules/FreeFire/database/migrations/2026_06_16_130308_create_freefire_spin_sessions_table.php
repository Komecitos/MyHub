<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freefire_spin_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->enum('spin_type', ['token_ring', 'faded_wheel']);
            $table->integer('token_needed')->nullable(); // untuk token ring
            $table->integer('luck_percentage')->default(50); // slider keberuntungan
            $table->integer('discount_percentage')->default(0); // diskon faded wheel
            $table->integer('modal_diamond')->default(0); // modal awal
            $table->integer('spent_diamond')->default(0); // total terpakai
            $table->integer('current_spin')->default(0); // sudah spin ke berapa
            $table->integer('current_token')->default(0); // token terkumpul (token ring)
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freefire_spin_sessions');
    }
};