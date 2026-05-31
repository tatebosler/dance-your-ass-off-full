<?php

namespace App\Models;

use Database\Factories\PartyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    /** @use HasFactory<PartyFactory> */
    use HasFactory;

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
