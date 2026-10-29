<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicates = DB::table('registrations')->whereNotNull('gantangan_number')
            ->select('competition_class_id')->groupBy('competition_class_id')
            ->groupByRaw("TRIM(LEADING '0' FROM gantangan_number)")->havingRaw('COUNT(*) > 1')->exists();
        if ($duplicates) {
            throw new RuntimeException('Migrasi dihentikan: ada nomor gantangan duplikat dalam satu kelas. Tinjau data terlebih dahulu; riwayat tidak diubah otomatis.');
        }
        Schema::table('registrations', function (Blueprint $table) {
            $table->unique(['competition_class_id', 'gantangan_number'], 'registrations_class_gantangan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropUnique('registrations_class_gantangan_unique');
        });
    }
};
