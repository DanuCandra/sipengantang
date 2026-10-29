<?php

namespace App\Domain\Ranking\Services;

use App\Enums\RegistrationStatus;
use App\Models\CompetitionClass;
use Illuminate\Validation\ValidationException;

class RankingInputBuilder
{
    public const MINIMUM_JUDGES = 3;

    public function build(CompetitionClass $competitionClass): array
    {
        $competitionClass->load(['event', 'criteria', 'prizes']);
        $criteria = $competitionClass->criteria->map(fn ($criterion) => [
            'id' => $criterion->id, 'code' => $criterion->code, 'name' => $criterion->name,
            'weight' => $criterion->weight, 'type' => $criterion->type->value,
            'min_score' => $criterion->min_score, 'max_score' => $criterion->max_score,
        ])->all();
        if (count($criteria) !== 5 || array_column($criteria, 'code') !== ['C1', 'C2', 'C3', 'C4', 'C5']) {
            throw ValidationException::withMessages(['ranking' => 'Snapshot lima kriteria kelas tidak valid.']);
        }
        $assignments = $competitionClass->judgeAssignments()->active()->with('judge')->orderBy('judge_id')->get();
        $judgeIds = $assignments->pluck('judge_id')->all();
        $registrations = $competitionClass->registrations()->where('status', RegistrationStatus::Verified)
            ->with(['bird:id,name,bird_type', 'participant:id,name', 'judgeEvaluations' => fn ($query) => $query
                ->submitted()->whereIn('judge_id', $judgeIds)->with('scores')->orderBy('judge_id')])
            ->orderBy('id')->get();
        $alternatives = $entries = $source = [];
        $complete = $registrations->isNotEmpty() && count($judgeIds) >= self::MINIMUM_JUDGES && $assignments->every(fn ($assignment) => $assignment->judge->isActive());
        foreach ($registrations as $registration) {
            $evaluations = [];
            $totals = array_fill_keys(array_column($criteria, 'code'), 0);
            foreach ($registration->judgeEvaluations as $evaluation) {
                $values = [];
                foreach ($criteria as $criterion) {
                    $score = $evaluation->scores->firstWhere('criterion_id', $criterion['id'])?->score;
                    if (! is_int($score) || $score < $criterion['min_score'] || $score > $criterion['max_score']) {
                        throw ValidationException::withMessages(['ranking' => 'Terdapat evaluasi submitted tidak lengkap atau nilai tidak valid.']);
                    }
                    $values[$criterion['code']] = $score;
                    $totals[$criterion['code']] += $score;
                }
                if ($evaluation->scores->count() !== count($criteria)) {
                    throw ValidationException::withMessages(['ranking' => 'Terdapat nilai kriteria di luar snapshot kelas.']);
                }
                $evaluations[] = ['id' => $evaluation->id, 'judge_id' => $evaluation->judge_id, 'submitted_at' => $evaluation->submitted_at?->toIso8601String(), 'scores' => $values];
            }
            $count = count($evaluations);
            $complete = $complete && $count === count($judgeIds);
            $entry = ['bird_name' => $registration->bird->name, 'bird_type' => $registration->bird->bird_type, 'participant_name' => $registration->participant->name,
                'registration_number' => $registration->registration_number, 'gantangan_number' => $registration->gantangan_number];
            $averages = $count === 0 ? [] : array_map(fn ($total) => $total / $count, $totals);
            if ($count > 0) {
                $alternatives[$registration->id] = $averages;
            }
            $entries[$registration->id] = $entry;
            $source[] = ['registration_id' => $registration->id, 'bird_id' => $registration->bird_id, 'entry' => $entry, 'averages' => $averages, 'evaluations' => $evaluations];
        }
        $input = ['minimum_judges' => self::MINIMUM_JUDGES, 'judge_ids' => $judgeIds, 'registrations' => $source,
            'class' => ['name' => $competitionClass->name, 'event_name' => $competitionClass->event->name, 'event_start_at' => $competitionClass->event->start_at->toIso8601String(), 'organizer_name' => config('app.name')],
            'prizes' => $competitionClass->prizes->map(fn ($prize) => ['rank' => $prize->rank, 'title' => $prize->title, 'type' => $prize->type->value, 'cash_amount' => $prize->cash_amount, 'description' => $prize->description])->all()];
        $hash = hash('sha256', json_encode([TopsisCalculator::VERSION, $criteria, $input], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));

        return compact('criteria', 'input', 'alternatives', 'entries', 'complete', 'hash');
    }

    public function requireComplete(array $source): void
    {
        if (! $source['complete']) {
            throw ValidationException::withMessages(['ranking' => 'Finalisasi dan pencatatan draw membutuhkan minimal 3 Juri aktif, peserta eligible, serta seluruh Juri menyelesaikan penilaian setiap peserta eligible.']);
        }
    }
}
