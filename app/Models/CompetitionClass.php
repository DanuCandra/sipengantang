<?php

namespace App\Models;

use App\Enums\CompetitionClassStatus;
use Database\Factories\CompetitionClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['event_id', 'name', 'slug', 'bird_type', 'description', 'registration_fee', 'quota', 'start_at', 'end_at', 'status'])]
class CompetitionClass extends Model
{
    /** @use HasFactory<CompetitionClassFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['registration_fee' => 'integer', 'quota' => 'integer', 'start_at' => 'datetime', 'end_at' => 'datetime', 'status' => CompetitionClassStatus::class];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(CompetitionClassCriterion::class)->orderBy('sort_order');
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(Prize::class)->orderBy('rank');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }

    public function rankingRuns(): HasMany
    {
        return $this->hasMany(RankingRun::class);
    }
}
