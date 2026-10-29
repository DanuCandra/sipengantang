<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['ranking_run_id', 'group_key', 'ordered_registration_ids', 'note', 'created_by'])]
class RankingTieResolution extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['ordered_registration_ids' => 'array'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Hasil undian tidak dapat ditimpa.'));
        static::deleting(fn () => throw new LogicException('Hasil undian tidak dapat dihapus.'));
    }

    public function rankingRun(): BelongsTo
    {
        return $this->belongsTo(RankingRun::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
