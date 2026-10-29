<?php

namespace App\Domain\Judging\Services;

use App\Enums\EvaluationStatus;
use App\Models\CompetitionClass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EvaluationScoreValidator
{
    /** @param array<int|string, mixed> $scores @return array<int, int> */
    public function validate(CompetitionClass $competitionClass, array $scores, EvaluationStatus $intent): array
    {
        $criteria = $competitionClass->criteria()->get();
        if ($criteria->count() !== 5) {
            throw ValidationException::withMessages(['judging' => 'Snapshot lima kriteria kelas belum lengkap.']);
        }
        if (array_diff(array_map('strval', array_keys($scores)), $criteria->modelKeys())) {
            throw ValidationException::withMessages(['scores' => 'Terdapat kriteria yang bukan milik kelas ini.']);
        }

        $rules = ['scores' => ['array']];
        $attributes = [];
        foreach ($criteria as $criterion) {
            $key = 'scores.'.$criterion->id;
            $rules[$key] = [$intent === EvaluationStatus::Submitted ? 'required' : 'nullable', 'integer', 'between:'.$criterion->min_score.','.$criterion->max_score];
            $attributes[$key] = $criterion->name;
        }
        $validated = Validator::make(['scores' => $scores], $rules, [
            'required' => 'Nilai :attribute wajib diisi sebelum submit.',
            'integer' => 'Nilai :attribute harus berupa angka bulat.',
            'between' => 'Nilai :attribute harus berada pada rentang :min sampai :max.',
        ], $attributes)->validate();

        return collect($validated['scores'])->reject(fn ($score) => $score === null || $score === '')
            ->mapWithKeys(fn ($score, $id) => [(int) $id => (int) $score])->all();
    }
}
