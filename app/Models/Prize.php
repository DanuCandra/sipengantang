<?php

namespace App\Models;

use App\Enums\PrizeType;
use Database\Factories\PrizeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['competition_class_id', 'rank', 'type', 'title', 'description', 'cash_amount'])]
class Prize extends Model
{
    /** @use HasFactory<PrizeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['rank' => 'integer', 'type' => PrizeType::class, 'cash_amount' => 'integer'];
    }

    public function competitionClass(): BelongsTo
    {
        return $this->belongsTo(CompetitionClass::class);
    }
}
