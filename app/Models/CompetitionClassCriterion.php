<?php

namespace App\Models;

use App\Enums\CriterionType;
use Database\Factories\CompetitionClassCriterionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['competition_class_id', 'code', 'name', 'weight', 'type', 'min_score', 'max_score', 'sort_order'])]
class CompetitionClassCriterion extends Model
{
    /** @use HasFactory<CompetitionClassCriterionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['weight' => 'decimal:6', 'type' => CriterionType::class, 'min_score' => 'integer', 'max_score' => 'integer', 'sort_order' => 'integer'];
    }

    public function competitionClass(): BelongsTo
    {
        return $this->belongsTo(CompetitionClass::class);
    }
}
