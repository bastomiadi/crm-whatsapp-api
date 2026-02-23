<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chat Conversations Table
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('name')->nullable();
            $table->string('session_id')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->timestamp('last_message_at')->nullable();
            $table->integer('unread_count')->default(0);
            $table->timestamps();
            
            $table->index(['phone', 'status']);
            $table->index('assigned_to');
            $table->index('last_message_at');
        });

        // Chat Messages Table
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->text('message');
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->string('message_id')->nullable()->unique();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->json('metadata')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index('conversation_id');
            $table->index('message_id');
            $table->index('created_at');
        });

        // Add assigned_to column to contacts if not exists
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
