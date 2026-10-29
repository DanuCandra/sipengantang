<?php

namespace Tests\Concerns;

use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\CompetitionClass;
use App\Models\CompetitionClassCriterion;
use App\Models\Event;
use App\Models\JudgeAssignment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

trait CreatesJudgingFixtures
{
    /** @return array{User, Event, CompetitionClass, Registration, Collection} */
    private function judgingFixture(): array
    {
        $event = Event::factory()->publishedUpcoming()->create(['status' => EventStatus::Ongoing]);
        $competitionClass = CompetitionClass::factory()->for($event)->create(['status' => CompetitionClassStatus::Ongoing]);
        $criteria = CompetitionClassCriterion::factory()->for($competitionClass)->count(5)->sequence(
            ['code' => 'C1', 'name' => 'Irama Lagu', 'weight' => '0.400000', 'sort_order' => 1],
            ['code' => 'C2', 'name' => 'Durasi Kerja', 'weight' => '0.200000', 'sort_order' => 2],
            ['code' => 'C3', 'name' => 'Volume', 'weight' => '0.200000', 'sort_order' => 3],
            ['code' => 'C4', 'name' => 'Gaya', 'weight' => '0.100000', 'sort_order' => 4],
            ['code' => 'C5', 'name' => 'Fisik', 'weight' => '0.100000', 'sort_order' => 5],
        )->create();
        $registration = Registration::factory()->for($competitionClass)->create(['status' => RegistrationStatus::Verified, 'verified_at' => now()]);
        $judge = User::factory()->judge()->create();
        JudgeAssignment::factory()->for($competitionClass)->for($judge, 'judge')->create();

        return [$judge, $event, $competitionClass, $registration, $criteria];
    }

    private function scoresFor(Collection $criteria, int $score = 8): array
    {
        return $criteria->mapWithKeys(fn ($criterion) => [$criterion->id => $score])->all();
    }
}
