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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['nps', 'satisfaction', 'feedback'])->default('nps');
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->json('questions')->nullable();
            $table->boolean('send_to_all_contacts')->default(false);
            $table->json('contact_segments')->nullable();
            $table->boolean('send_via_whatsapp')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('type');
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('nps_score')->nullable();
            $table->decimal('satisfaction_score', 3, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->json('answers')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['survey_id', 'contact_id']);
            $table->index('survey_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('surveys');
    }
};
