<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users', 'id')->onDelete('cascade');
            $table->dateTime('user1_last_read_at')->nullable();
            $table->dateTime('user2_last_read_at')->nullable();
            $table->dateTime('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['user1_id', 'user2_id']);
            $table->index(['user1_id', 'user2_id']);
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
