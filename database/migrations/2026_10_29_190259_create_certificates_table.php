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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->foreignId('competition_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('registration_id')->constrained()->restrictOnDelete();
            $table->foreignId('ranking_result_id')->unique()->constrained()->restrictOnDelete();
            $table->unsignedInteger('rank');
            $table->string('file_path', 500);
            $table->string('template_version', 30);
            $table->json('metadata_snapshot');
            $table->dateTime('generated_at');
            $table->timestamps();
            $table->index(['event_id', 'competition_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
