<?php

namespace Database\Factories;

use App\Models\CompetitionClassCriterion;
use App\Models\JudgeEvaluation;
use App\Models\JudgeScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JudgeScore> */
class JudgeScoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'judge_evaluation_id' => JudgeEvaluation::factory(),
            'criterion_id' => function (array $attributes): int {
                $classId = JudgeEvaluation::query()->findOrFail($attributes['judge_evaluation_id'])->registration()->value('competition_class_id');

                return CompetitionClassCriterion::query()->where('competition_class_id', $classId)->orderBy('id')->value('id')
                    ?? CompetitionClassCriterion::factory()->create(['competition_class_id' => $classId])->id;
            },
            'score' => 8,
        ];
    }
}
