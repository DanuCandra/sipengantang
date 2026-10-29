<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole as UserRoleEnum;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'status', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    /**
     * @return HasMany<UserRole, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * @return HasOne<ParticipantProfile, $this>
     */
    public function participantProfile(): HasOne
    {
        return $this->hasOne(ParticipantProfile::class);
    }

    /** @return HasMany<Event, $this> */
    public function ownedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'owner_id');
    }

    /** @return HasMany<EventAdmin, $this> */
    public function eventAssignments(): HasMany
    {
        return $this->hasMany(EventAdmin::class);
    }

    /** @return BelongsToMany<Event, $this> */
    public function administeredEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_admins')->withTimestamps();
    }

    /** @return HasMany<Bird, $this> */
    public function birds(): HasMany
    {
        return $this->hasMany(Bird::class, 'owner_id');
    }

    /** @return HasMany<Registration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'participant_id');
    }

    public function hasRole(UserRoleEnum $role): bool
    {
        return $this->roles->contains(
            fn (UserRole $userRole): bool => $userRole->role === $role,
        );
    }

    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class, 'judge_id');
    }

    public function judgeEvaluations(): HasMany
    {
        return $this->hasMany(JudgeEvaluation::class, 'judge_id');
    }

    public function violationsIssued(): HasMany
    {
        return $this->hasMany(Violation::class, 'judge_id');
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }
}
