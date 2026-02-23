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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('stage', ['lead', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost'])->default('lead');
            $table->decimal('value', 12, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->integer('probability')->default(0);
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->text('lost_reason')->nullable();
            $table->text('won_note')->nullable();
            $table->text('notes')->nullable();
            $table->enum('source', ['website', 'referral', 'social_media', 'campaign', 'direct', 'other'])->nullable();
            $table->timestamps();
            
            $table->index(['stage', 'assigned_to']);
            $table->index('contact_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
