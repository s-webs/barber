<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reception extends Model
{
    protected $fillable = ['name', 'telegram_chat_id', 'auth_token', 'branch_id'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($reception) {
            if (empty($reception->auth_token)) {
                $reception->auth_token = static::generateAuthToken();
            }
        });
    }

    public static function generateAuthToken()
    {
        return Str::uuid();
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
