<?php

namespace App\Models;

use App\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['owner_id', 'name', 'slug', 'description', 'banner_path', 'location_name', 'address', 'registration_open_at', 'registration_close_at', 'start_at', 'end_at', 'contact_person', 'contact_phone', 'status', 'published_at'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'registration_open_at' => 'datetime',
            'registration_close_at' => 'datetime',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'published_at' => 'datetime',
            'status' => EventStatus::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function adminAssignments(): HasMany
    {
        return $this->hasMany(EventAdmin::class);
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_admins')->withPivot('id')->withTimestamps();
    }

    public function competitionClasses(): HasMany
    {
        return $this->hasMany(CompetitionClass::class);
    }
}
