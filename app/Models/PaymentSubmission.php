<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\PaymentSubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['registration_id', 'amount', 'proof_path', 'status', 'uploaded_at', 'reviewed_by', 'reviewed_at', 'rejection_reason'])]
class PaymentSubmission extends Model
{
    /** @use HasFactory<PaymentSubmissionFactory> */
    use HasFactory;

    protected $hidden = ['proof_path'];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => PaymentStatus::class,
            'uploaded_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
