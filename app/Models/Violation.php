<?php

namespace App\Models;

use App\Enums\ViolationAction;
use App\Enums\ViolationReasonCode;
use Database\Factories\ViolationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['registration_id', 'judge_id', 'action', 'reason_code', 'reason', 'note'])]
class Violation extends Model
{
    /** @use HasFactory<ViolationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['action' => ViolationAction::class, 'reason_code' => ViolationReasonCode::class];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }
}
