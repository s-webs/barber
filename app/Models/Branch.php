<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'image',
        'name',
        'address',
        'phone',
        'map_link',
    ];

    public function barbers(): \Illuminate\Database\Eloquent\Relations\HasMany|Branch
    {
        return $this->hasMany(Barber::class);
    }

    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany|Branch
    {
        return $this->hasMany(Appointment::class);
    }
}
