<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['event_id', 'competition_class_id', 'registration_id', 'ranking_result_id', 'rank', 'file_path', 'template_version', 'metadata_snapshot', 'generated_at'])]
class Certificate extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['rank' => 'integer', 'metadata_snapshot' => 'array', 'generated_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Piagam historis tidak dapat ditimpa.'));
        static::deleting(fn () => throw new LogicException('Riwayat piagam tidak dapat dihapus.'));
    }

    public function rankingResult(): BelongsTo
    {
        return $this->belongsTo(RankingResult::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function competitionClass(): BelongsTo
    {
        return $this->belongsTo(CompetitionClass::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
