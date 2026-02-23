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
        // Segments Table (must be before contacts)
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#22c55e');
            $table->json('criteria')->nullable(); // For dynamic segments
            $table->boolean('is_dynamic')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Tags Table (must be before contacts)
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6b7280');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Contacts Table
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_picture')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->foreignId('segment_id')->nullable()->constrained()->nullOnDelete();
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['phone', 'status']);
            $table->index('segment_id');
        });

        // Contact Tag Pivot
        Schema::create('contact_tag', function (Blueprint $table) {
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->primary(['contact_id', 'tag_id']);
        });

        // Orders Table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->json('items')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['order_number', 'status']);
            $table->index('contact_id');
        });

        // Tickets Table
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('category', ['general', 'support', 'complaint', 'sales', 'feedback'])->default('general');
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('response_time')->nullable(); // in minutes
            $table->decimal('satisfaction_rating', 3, 2)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['ticket_number', 'status']);
            $table->index('contact_id');
            $table->index('assigned_to');
        });

        // Ticket Messages Table
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_from_customer')->default(false);
            $table->timestamps();
            
            $table->index('ticket_id');
        });

        // Message Templates Table (must be before campaigns)
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['text', 'image', 'document', 'location', 'contact'])->default('text');
            $table->string('category')->default('general'); // order_confirmation, payment_reminder, shipping_notification, etc.
            $table->text('content');
            $table->string('media_url')->nullable();
            $table->json('variables')->nullable(); // Define template variables
            $table->json('buttons')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category', 'is_approved']);
        });

        // Campaigns Table
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['broadcast', 'sequence', 'trigger'])->default('broadcast');
            $table->enum('status', ['draft', 'scheduled', 'running', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->nullOnDelete();
            $table->json('target_segments')->nullable();
            $table->json('target_tags')->nullable();
            $table->json('excluded_contacts')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('read_count')->default(0);
            $table->integer('replied_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'scheduled_at']);
        });

        // Automations Table
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('trigger_type', [
                'contact_created',
                'contact_tagged',
                'order_created',
                'order_status_changed',
                'ticket_created',
                'ticket_status_changed',
                'message_received',
                'keyword_detected',
                'scheduled',
                'webhook'
            ]);
            $table->json('trigger_config')->nullable();
            $table->json('conditions')->nullable();
            $table->json('actions'); // Array of actions to execute
            $table->boolean('is_active')->default(true);
            $table->integer('execution_count')->default(0);
            $table->timestamp('last_executed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['trigger_type', 'is_active']);
        });

        // Automation Logs Table
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('trigger_type');
            $table->json('trigger_data')->nullable();
            $table->json('executed_actions')->nullable();
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['automation_id', 'created_at']);
        });

        // Chatbots Table
        Schema::create('chatbots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->json('flows'); // Chatbot flow definition
            $table->json('keywords')->nullable(); // Trigger keywords
            $table->json('default_response')->nullable();
            $table->json('fallback_response')->nullable();
            $table->boolean('handover_enabled')->default(true);
            $table->foreignId('handover_to')->nullable()->constrained('users')->nullOnDelete();
            $table->json('working_hours')->nullable();
            $table->string('session_id')->nullable(); // WhatsApp session ID (string, not FK)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status']);
        });

        // Chatbot Sessions Table
        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->unique();
            $table->string('current_node')->nullable();
            $table->json('context')->nullable(); // Session context/variables
            $table->json('history')->nullable(); // Conversation history
            $table->enum('status', ['active', 'completed', 'handed_over', 'expired'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['chatbot_id', 'contact_id', 'status']);
        });

        // Interactions Table (Chat History)
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('channel', ['whatsapp', 'email', 'sms', 'webchat'])->default('whatsapp');
            $table->enum('type', ['text', 'image', 'document', 'audio', 'video', 'location', 'contact', 'other']);
            $table->text('content')->nullable();
            $table->json('media')->nullable();
            $table->string('message_id')->nullable(); // WhatsApp message ID
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Agent who sent
            $table->boolean('is_automated')->default(false);
            $table->boolean('is_from_bot')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['contact_id', 'created_at']);
            $table->index(['direction', 'channel']);
        });

        // Analytics Table (Aggregated Stats)
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric_type'); // messages_sent, messages_received, tickets_created, etc.
            $table->string('dimension')->nullable(); // contact_segment, campaign, etc.
            $table->string('dimension_value')->nullable();
            $table->integer('value')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['date', 'metric_type', 'dimension', 'dimension_value']);
            $table->index(['date', 'metric_type']);
        });

        // Products Table (for E-commerce)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->integer('stock')->default(0);
            $table->string('image_url')->nullable();
            $table->string('category')->nullable();
            $table->json('attributes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['sku', 'is_active']);
        });

        // Notifications Table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_read']);
        });

        // Webhooks Table
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('secret')->nullable();
            $table->json('events');
            $table->boolean('is_active')->default(true);
            $table->integer('failure_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Quick Replies Table
        Schema::create('quick_replies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content');
            $table->string('category')->default('general');
            $table->json('attachments')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('category');
        });

        // Agent Status Table
        Schema::create('agent_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['online', 'away', 'busy', 'offline'])->default('offline');
            $table->integer('active_tickets')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_statuses');
        Schema::dropIfExists('quick_replies');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('products');
        Schema::dropIfExists('analytics');
        Schema::dropIfExists('interactions');
        Schema::dropIfExists('chatbot_sessions');
        Schema::dropIfExists('chatbots');
        Schema::dropIfExists('automation_logs');
        Schema::dropIfExists('automations');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('contact_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('segments');
    }
};
