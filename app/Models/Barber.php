<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    protected $casts = [
        'socials' => 'array',
    ];

    public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }
}
