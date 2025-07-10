<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    public function barber(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}
