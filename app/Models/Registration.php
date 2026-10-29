<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['competition_class_id', 'participant_id', 'bird_id', 'registration_number', 'gantangan_number', 'registration_fee_snapshot', 'status', 'registered_at', 'verified_at', 'disqualified_at'])]
class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'registration_fee_snapshot' => 'integer',
            'status' => RegistrationStatus::class,
            'registered_at' => 'datetime',
            'verified_at' => 'datetime',
            'disqualified_at' => 'datetime',
        ];
    }

    public function competitionClass(): BelongsTo
    {
        return $this->belongsTo(CompetitionClass::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function bird(): BelongsTo
    {
        return $this->belongsTo(Bird::class);
    }

    public function paymentSubmissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class);
    }

    public function judgeEvaluations(): HasMany
    {
        return $this->hasMany(JudgeEvaluation::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function rankingResults(): HasMany
    {
        return $this->hasMany(RankingResult::class);
    }
}
