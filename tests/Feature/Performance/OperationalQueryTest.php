<?php

namespace Tests\Feature\Performance;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class OperationalQueryTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    private function countQueriesFor(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        try {
            $this->get($url)->assertOk();

            return count(DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    public function test_public_progress_query_count_stays_bounded_when_class_grows(): void
    {
        $f = $this->rankingFixture();
        $url = route('events.classes.live', [$f['event'], $f['class']]);
        $small = $this->countQueriesFor($url);
        Registration::factory()->for($f['class'], 'competitionClass')->count(50)->create(['status' => RegistrationStatus::Verified]);

        $large = $this->countQueriesFor($url);

        $this->assertLessThanOrEqual($small + 1, $large, 'Progress must use aggregates rather than query each registration.');
        $this->getJson($url)->assertJsonPath('eligible', 52)->assertJsonPath('expected', 156);
    }

    public function test_judge_participant_page_is_paginated_and_has_no_per_participant_query_growth(): void
    {
        $f = $this->rankingFixture();
        $this->actingAs($f['judges'][0]);
        $url = route('judge.classes.show', $f['class']);
        $small = $this->countQueriesFor($url);
        Registration::factory()->for($f['class'], 'competitionClass')->count(30)->create(['status' => RegistrationStatus::Verified]);

        $large = $this->countQueriesFor($url);

        $this->assertLessThanOrEqual($small + 2, $large, 'Eager-loaded relations must not cause one query per card.');
        $this->get($url)->assertViewHas('registrations', fn ($page) => $page->total() === 32 && $page->count() === 15);
    }
}
