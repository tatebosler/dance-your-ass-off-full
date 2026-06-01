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

    protected $fillable = [
        'name',
        'local',
        'dietary_restrictions',
        'dietary_notes',
        'song_requests',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
