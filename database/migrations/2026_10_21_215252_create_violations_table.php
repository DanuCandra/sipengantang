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
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->restrictOnDelete();
            $table->foreignId('judge_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 30);
            $table->string('reason_code', 60);
            $table->string('reason');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['registration_id', 'action']);
            $table->index('reason_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
