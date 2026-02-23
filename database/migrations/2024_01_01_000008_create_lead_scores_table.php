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
        Schema::create('lead_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->integer('engagement_score')->default(0);
            $table->integer('demographic_score')->default(0);
            $table->integer('behavior_score')->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique('contact_id');
            $table->index('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_scores');
    }
};
