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
        Schema::create('ranking_tie_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ranking_run_id')->constrained()->restrictOnDelete();
            $table->char('group_key', 64);
            $table->json('ordered_registration_ids');
            $table->text('note');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['ranking_run_id', 'group_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_tie_resolutions');
    }
};
