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
        Schema::create('judge_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('judge_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['competition_class_id', 'judge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('judge_assignments');
    }
};
