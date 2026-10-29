<?php

namespace App\Models;

use Database\Factories\JudgeScoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['judge_evaluation_id', 'criterion_id', 'score'])]
class JudgeScore extends Model
{
    /** @use HasFactory<JudgeScoreFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['score' => 'integer'];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(JudgeEvaluation::class, 'judge_evaluation_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(CompetitionClassCriterion::class, 'criterion_id');
    }
}
