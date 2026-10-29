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
        Schema::table('judge_assignments', function (Blueprint $table) {
            $table->dateTime('replaced_at')->nullable();
            $table->foreignId('replacement_assignment_id')->nullable()->constrained('judge_assignments')->restrictOnDelete();
            $table->text('replacement_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('judge_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('replacement_assignment_id');
            $table->dropColumn(['replaced_at', 'replacement_note']);
        });
    }
};
