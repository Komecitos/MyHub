<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freefire_wheel_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('freefire_spin_sessions')->onDelete('cascade');
            $table->enum('type', ['token', 'item']);
            $table->integer('token_value')->nullable(); // nilai token (1,2,3,5,10,20,30,100)
            $table->string('item_name')->nullable(); // nama item hadiah
            $table->enum('rarity', ['common', 'rare', 'epic', 'artifact'])->nullable();
            $table->integer('slot_count')->default(1); // berapa slot di wheel
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freefire_wheel_slots');
    }
};
