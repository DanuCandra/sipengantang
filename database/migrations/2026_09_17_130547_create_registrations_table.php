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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('participant_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('bird_id')->constrained()->restrictOnDelete();
            $table->string('registration_number', 60)->unique();
            $table->string('gantangan_number', 50)->nullable()->index();
            $table->unsignedBigInteger('registration_fee_snapshot');
            $table->string('status', 40);
            $table->dateTime('registered_at');
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('disqualified_at')->nullable();
            $table->timestamps();

            $table->index(['competition_class_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
