<?php

namespace App\Models;

use App\Enums\EvaluationStatus;
use Database\Factories\JudgeEvaluationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['registration_id', 'judge_id', 'status', 'submitted_at'])]
class JudgeEvaluation extends Model
{
    /** @use HasFactory<JudgeEvaluationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['status' => EvaluationStatus::class, 'submitted_at' => 'datetime'];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(JudgeScore::class);
    }

    #[Scope]
    protected function submitted(Builder $query): Builder
    {
        return $query->where('status', EvaluationStatus::Submitted);
    }
}
