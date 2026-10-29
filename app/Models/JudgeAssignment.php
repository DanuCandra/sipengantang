<?php

namespace App\Models;

use Database\Factories\JudgeAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['competition_class_id', 'judge_id', 'replaced_at', 'replacement_assignment_id', 'replacement_note'])]
class JudgeAssignment extends Model
{
    /** @use HasFactory<JudgeAssignmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['replaced_at' => 'datetime'];
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->whereNull('replaced_at');
    }

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replacement_assignment_id');
    }

    public function competitionClass(): BelongsTo
    {
        return $this->belongsTo(CompetitionClass::class);
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }
}
