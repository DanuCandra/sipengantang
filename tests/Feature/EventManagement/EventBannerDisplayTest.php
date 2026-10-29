<?php

namespace Tests\Feature\EventManagement;

use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventBannerDisplayTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_upload_and_assigned_admin_update_display_actual_banner_on_both_detail_pages(): void
    {
        Storage::fake('public');
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $payload = ['name' => 'Event Banner QA', 'location_name' => 'Arena', 'address' => 'Alamat arena', 'start_at' => now()->addDay()->toDateTimeString()];
        $this->actingAs($owner)->post(route('owner.events.store'), [...$payload, 'banner' => UploadedFile::fake()->image('banner.png')])->assertSessionHasNoErrors();
        $event = Event::query()->sole();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        $original = $event->banner_path;
        Storage::disk('public')->assertExists($original);
        foreach ([[$owner, 'owner'], [$admin, 'admin']] as [$user, $area]) {
            $this->actingAs($user)->get(route($area.'.events.show', $event))->assertOk()->assertSee(asset('storage/'.$original))->assertSee('Banner event Event Banner QA')->assertSee('Ganti banner')->assertSee(route($area.'.events.edit', $event).'#banner');
        }
        $this->actingAs($admin)->put(route('admin.events.update', $event), [...$payload, 'banner' => UploadedFile::fake()->image('replacement.jpg')])->assertSessionHasNoErrors();
        $event->refresh();
        Storage::disk('public')->assertExists($event->banner_path);
        Storage::disk('public')->assertMissing($original);
        $this->get(route('admin.events.show', $event))->assertSee(asset('storage/'.$event->banner_path))->assertDontSee(asset('storage/'.$original));
    }

    public function test_empty_banner_has_upload_guidance_and_unrelated_admin_cannot_view_private_event(): void
    {
        $event = Event::factory()->create(['banner_path' => null]);
        $this->actingAs($event->owner)->get(route('owner.events.show', $event))->assertSee('Banner event belum diunggah')->assertSee('Unggah banner')->assertSee(route('owner.events.edit', $event).'#banner');
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.events.show', $event))->assertForbidden();
    }

    public function test_banner_is_rendered_on_every_event_listing_detail_edit_and_featured_surface(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('poster.png')->store('event-banners', 'public');
        $event = Event::factory()->publishedUpcoming()->create(['name' => 'Poster Semua Halaman', 'banner_path' => $path]);
        $admin = User::factory()->admin()->create();
        $participant = User::factory()->participant()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        $url = asset('storage/'.$path);

        foreach ([route('home'), route('events.index'), route('events.show', $event)] as $page) {
            $this->get($page)->assertOk()->assertSee($url)->assertSee('alt="Banner event Poster Semua Halaman"', false);
        }
        foreach ([
            [$event->owner, [route('owner.events.index'), route('owner.events.show', $event), route('owner.events.edit', $event), route('owner.workspace', ['section' => 'admins']), route('owner.workspace', ['section' => 'monitoring'])]],
            [$admin, [route('admin.events.index'), route('admin.events.show', $event), route('admin.events.edit', $event)]],
            [$participant, [route('participant.dashboard'), route('participant.events.index')]],
        ] as [$user, $pages]) {
            foreach ($pages as $page) {
                $this->actingAs($user)->get($page)->assertOk()->assertSee($url)->assertSee('alt="Banner event Poster Semua Halaman"', false);
            }
        }
        Storage::disk('public')->assertExists($path);
    }

    public function test_missing_banner_has_a_clear_public_fallback_and_draft_banner_stays_private(): void
    {
        $event = Event::factory()->publishedUpcoming()->create(['banner_path' => null]);
        $draft = Event::factory()->create(['banner_path' => 'event-banners/private-draft-banner.png']);
        foreach ([route('home'), route('events.index'), route('events.show', $event)] as $page) {
            $this->get($page)->assertOk()->assertSee('Banner belum tersedia')->assertDontSee($draft->banner_path);
        }
        $this->get(route('events.show', $draft))->assertNotFound();
    }
}
