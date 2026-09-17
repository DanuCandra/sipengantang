<?php

namespace App\Models;

use App\Enums\UserRole as UserRoleEnum;
use Database\Factories\UserRoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    /** @use HasFactory<UserRoleFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRoleEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
