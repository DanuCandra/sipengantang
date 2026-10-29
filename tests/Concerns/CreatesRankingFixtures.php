<?php

namespace Tests\Concerns;

use App\Enums\RegistrationStatus;
use App\Models\EventAdmin;
use App\Models\JudgeAssignment;
use App\Models\JudgeEvaluation;
use App\Models\Registration;
use App\Models\User;

trait CreatesRankingFixtures
{
    use CreatesJudgingFixtures;

    private function rankingFixture(bool $tied = false): array
    {
        [$judge, $event, $class, $first, $criteria] = $this->judgingFixture();
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->create(['user_id' => $admin->id]);
        $judges = collect([$judge]);
        foreach (range(1, 2) as $unused) {
            $other = User::factory()->judge()->create();
            JudgeAssignment::factory()->for($class, 'competitionClass')->for($other, 'judge')->create();
            $judges->push($other);
        }
        $second = Registration::factory()->for($class, 'competitionClass')->create(['status' => RegistrationStatus::Verified, 'verified_at' => now()]);
        foreach ([$first, $second] as $index => $registration) {
            foreach ($judges as $assigned) {
                $evaluation = JudgeEvaluation::factory()->for($registration)->for($assigned, 'judge')->submitted()->create();
                $evaluation->scores()->createMany($criteria->map(fn ($criterion) => ['criterion_id' => $criterion->id, 'score' => $tied ? 8 : ($index === 0 ? 9 : 7)])->all());
            }
        }

        return compact('admin', 'event', 'class', 'first', 'second', 'judges', 'criteria');
    }
}
