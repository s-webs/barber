<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'branch_id',
        'barber_id',
        'date',
        'time',
        'start_time',
        'end_time',
        'total_duration',
        'total_price',
        'client_name',
        'client_phone',
        'comment',
    ];

    protected array $dates = ['date', 'time'];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function barber(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'appointment_services')
            ->withPivot(['price', 'duration'])
            ->withTimestamps();
    }
}
