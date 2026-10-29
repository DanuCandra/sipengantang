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
        Schema::create('ranking_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ranking_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->restrictOnDelete();
            $table->json('entry_snapshot');
            $table->json('criterion_averages');
            $table->decimal('positive_distance', 16, 10);
            $table->decimal('negative_distance', 16, 10);
            $table->decimal('preference_value', 16, 10);
            $table->unsignedInteger('rank')->nullable();
            $table->boolean('is_tied');
            $table->timestamps();
            $table->unique(['ranking_run_id', 'registration_id']);
            $table->index(['ranking_run_id', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_results');
    }
};
