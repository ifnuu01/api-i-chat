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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations', 'id');
            $table->foreignId('sender_id')->constrained('users', 'id');
            $table->foreignId('reply_to_id')->nullable()->constrained('messages', 'id')->onDelete('set null');
            $table->text('content')->nullable();
            $table->enum('type', ['text', 'image', 'file'])->default('text');
            $table->string('file_url')->nullable();
            $table->decimal('file_size', 10, 2)->nullable();
            $table->boolean('is_edited')->nullable();
            $table->dateTime('edited_at')->nullable();
            $table->boolean('is_deleted')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index('sender_id');
            $table->index('reply_to_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
