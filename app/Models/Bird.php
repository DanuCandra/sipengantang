<?php

namespace App\Models;

use Database\Factories\BirdFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['owner_id', 'name', 'bird_type', 'photo_path', 'description'])]
class Bird extends Model
{
    /** @use HasFactory<BirdFactory> */
    use HasFactory;

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
