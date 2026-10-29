<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

#[Fillable(['ranking_run_id', 'registration_id', 'entry_snapshot', 'criterion_averages', 'positive_distance', 'negative_distance', 'preference_value', 'rank', 'is_tied'])]
class RankingResult extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['entry_snapshot' => 'array', 'criterion_averages' => 'array', 'positive_distance' => 'decimal:10', 'negative_distance' => 'decimal:10', 'preference_value' => 'decimal:10', 'rank' => 'integer', 'is_tied' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Hasil ranking tidak dapat diubah.'));
        static::deleting(fn () => throw new LogicException('Hasil ranking tidak dapat dihapus.'));
    }

    public function rankingRun(): BelongsTo
    {
        return $this->belongsTo(RankingRun::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }
}
