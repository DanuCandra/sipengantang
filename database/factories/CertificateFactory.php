<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\RankingResult;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Certificate> */
class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return ['ranking_result_id' => RankingResult::factory(),
            'event_id' => fn (array $a) => RankingResult::query()->findOrFail($a['ranking_result_id'])->rankingRun->competitionClass->event_id,
            'competition_class_id' => fn (array $a) => RankingResult::query()->findOrFail($a['ranking_result_id'])->rankingRun->competition_class_id,
            'registration_id' => fn (array $a) => RankingResult::query()->findOrFail($a['ranking_result_id'])->registration_id,
            'rank' => 1, 'file_path' => 'certificates/fixtures/'.Str::uuid().'.pdf', 'template_version' => '1.0.0',
            'metadata_snapshot' => ['number' => 'GS-FIXTURE', 'participant_name' => 'Peserta Contoh', 'bird_name' => 'Panglima', 'bird_type' => 'Murai Batu', 'event_name' => 'Event Contoh', 'class_name' => 'Kelas Utama', 'event_date' => '29 Oktober 2026', 'organizer_name' => 'GantangScore', 'rank' => 1],
            'generated_at' => now()];
    }
}
