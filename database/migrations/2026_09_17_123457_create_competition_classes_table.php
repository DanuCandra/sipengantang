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
        Schema::create('competition_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->string('name', 180);
            $table->string('slug', 200);
            $table->string('bird_type', 120)->index();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('registration_fee');
            $table->unsignedInteger('quota')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('status', 30);
            $table->timestamps();

            $table->unique(['event_id', 'slug']);
            $table->index(['event_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_classes');
    }
};
