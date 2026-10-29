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
        Schema::create('competition_class_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_class_id')->constrained()->restrictOnDelete();
            $table->string('code', 20);
            $table->string('name', 120);
            $table->decimal('weight', 8, 6);
            $table->string('type', 20);
            $table->unsignedTinyInteger('min_score')->default(1);
            $table->unsignedTinyInteger('max_score')->default(10);
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();

            $table->unique(['competition_class_id', 'code'], 'class_criteria_class_code_unique');
            $table->unique(['competition_class_id', 'sort_order'], 'class_criteria_class_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_class_criteria');
    }
};
