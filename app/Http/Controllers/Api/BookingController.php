<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function findAppointments($phone)
    {
        $appointments = Appointment::with('services')
        ->where('client_phone', $phone)
            ->get();

        if ($appointments->isEmpty()) {
            return response()->json(['error' => 'Записи не найдены'], 404);
        }

        return response()->json($appointments);
    }

}
