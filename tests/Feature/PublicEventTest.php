<?php

namespace Tests\Feature;

use App\Models\CompetitionClass;
use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PublicEventTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_listing_only_shows_published_events(): void
    {
        $published = Event::factory()->publishedUpcoming()->create(['name' => 'Piala Nusantara']);
        $draft = Event::factory()->create(['name' => 'Draft Rahasia']);

        $response = $this->get(route('events.index'));

        $response->assertSee($published->name)->assertDontSee($draft->name);
    }

    public function test_public_event_detail_renders_classes_without_private_owner_data(): void
    {
        $event = Event::factory()->publishedUpcoming()->create();
        $class = CompetitionClass::factory()->for($event)->create(['name' => 'Murai Batu Utama']);
        $ownerEmail = $event->owner()->value('email');

        $response = $this->get(route('events.show', $event));

        $response->assertSee($event->name)
            ->assertSee($class->name)
            ->assertDontSee($ownerEmail);
    }

    public function test_draft_event_detail_returns_not_found(): void
    {
        $event = Event::factory()->create();

        $this->get(route('events.show', $event))->assertNotFound();
    }
}
