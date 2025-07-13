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

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function schedules(): \Illuminate\Database\Eloquent\Relations\HasMany|Barber
    {
        return $this->hasMany(Schedule::class);
    }
}
