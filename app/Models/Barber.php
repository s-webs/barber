<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Barber extends Model
{
    protected $fillable = [
        'name',
        'level',
        'photo',
        'socials',
        'telegram_chat_id',
        'phone',
        'is_enabled',
        'created_at',
        'updated_at',
        'branch_id'
    ];
    protected $casts = [
        'socials' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function ($barber) {
            if (empty($barber->auth_token)) {
                $barber->auth_token = Str::uuid();
            }
        });
    }

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
