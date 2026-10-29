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
        Schema::create('judge_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('judge_evaluation_id')->constrained()->restrictOnDelete();
            $table->foreignId('criterion_id')->constrained('competition_class_criteria')->restrictOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();
            $table->unique(['judge_evaluation_id', 'criterion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('judge_scores');
    }
};
