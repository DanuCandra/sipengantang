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
        Schema::create('ranking_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_class_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->string('status', 20);
            $table->string('algorithm', 30);
            $table->string('algorithm_version', 30);
            $table->char('source_hash', 64);
            $table->json('criteria_snapshot');
            $table->json('input_snapshot');
            $table->json('calculation_snapshot');
            $table->dateTime('calculated_at');
            $table->dateTime('finalized_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['competition_class_id', 'type', 'calculated_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_runs');
    }
};
