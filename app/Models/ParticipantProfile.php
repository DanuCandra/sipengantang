<?php

namespace App\Models;

use Database\Factories\ParticipantProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantProfile extends Model
{
    /** @use HasFactory<ParticipantProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'city',
        'province',
        'postal_code',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
