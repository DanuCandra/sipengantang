<?php

namespace App\Models;

use App\Enums\RankingRunStatus;
use App\Enums\RankingRunType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

#[Fillable(['competition_class_id', 'type', 'status', 'algorithm', 'algorithm_version', 'source_hash', 'criteria_snapshot', 'input_snapshot', 'calculation_snapshot', 'calculated_at', 'finalized_at', 'created_by'])]
class RankingRun extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['type' => RankingRunType::class, 'status' => RankingRunStatus::class, 'criteria_snapshot' => 'array', 'input_snapshot' => 'array', 'calculation_snapshot' => 'array', 'calculated_at' => 'datetime', 'finalized_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        // Runs are append-only, including provisional snapshots used as draw evidence.
        static::updating(fn () => throw new LogicException('Snapshot ranking tidak dapat diubah.'));
        static::deleting(fn () => throw new LogicException('Riwayat ranking tidak dapat dihapus.'));
    }

    public function competitionClass(): BelongsTo
    {
        return $this->belongsTo(CompetitionClass::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(RankingResult::class);
    }

    public function tieResolutions(): HasMany
    {
        return $this->hasMany(RankingTieResolution::class);
    }
}
