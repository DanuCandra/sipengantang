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
        Schema::create('judge_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->restrictOnDelete();
            $table->foreignId('judge_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 30);
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['registration_id', 'judge_id']);
            $table->index(['judge_id', 'status']);
            $table->index(['registration_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('judge_evaluations');
    }
};
