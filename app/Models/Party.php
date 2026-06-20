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
        'invited_to_extras',
        'dietary_restrictions',
        'dietary_notes',
        'song_requests',
    ];

    protected $casts = [
        'local' => 'boolean',
        'invited_to_extras' => 'boolean',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
