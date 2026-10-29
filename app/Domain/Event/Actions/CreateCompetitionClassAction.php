<?php

namespace App\Domain\Event\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\CriterionType;
use App\Enums\EventStatus;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateCompetitionClassAction
{
    private const CRITERIA = [
        ['code' => 'C1', 'name' => 'Irama Lagu', 'weight' => '0.400000', 'sort_order' => 1],
        ['code' => 'C2', 'name' => 'Durasi Kerja / Kestabilan', 'weight' => '0.200000', 'sort_order' => 2],
        ['code' => 'C3', 'name' => 'Volume', 'weight' => '0.200000', 'sort_order' => 3],
        ['code' => 'C4', 'name' => 'Gaya', 'weight' => '0.100000', 'sort_order' => 4],
        ['code' => 'C5', 'name' => 'Fisik', 'weight' => '0.100000', 'sort_order' => 5],
    ];

    public function __construct(private ActivityRecorder $activityRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(User $actor, Event $event, array $data, ?string $ipAddress = null): CompetitionClass
    {
        return DB::transaction(function () use ($actor, $event, $data, $ipAddress): CompetitionClass {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            if (in_array($event->status, [EventStatus::Completed, EventStatus::Cancelled], true)) {
                throw ValidationException::withMessages(['event' => 'Kelas baru tidak dapat ditambahkan pada event yang sudah ditutup.']);
            }
            $competitionClass = $event->competitionClasses()->create([
                ...$data,
                'slug' => $this->uniqueSlug($event, $data['name']),
                'status' => CompetitionClassStatus::Upcoming,
            ]);

            $competitionClass->criteria()->createMany(array_map(fn (array $criterion): array => [
                ...$criterion,
                'type' => CriterionType::Benefit,
                'min_score' => 1,
                'max_score' => 10,
            ], self::CRITERIA));

            $this->activityRecorder->record($actor, ActivityAction::CompetitionClassCreated, $competitionClass, ['event_id' => $event->id], $ipAddress);

            return $competitionClass->load('criteria');
        });
    }

    private function uniqueSlug(Event $event, string $name): string
    {
        $base = Str::slug($name) ?: 'kelas';
        $slug = $base;
        $suffix = 2;

        while ($event->competitionClasses()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
