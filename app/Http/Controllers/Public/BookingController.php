<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function index()
    {
//        $barbers = Barber::query()->findOrFail(3);
//        dd($barbers->shedules);
        return view('pages.booking.index');
    }

    // API: Получить список филиалов
    public function branches()
    {
        return Branch::select('id', 'name', 'image', 'address')->get();
    }

    // API: Получить все услуги
    public function services()
    {
        return Service::select('id', 'name', 'price')->get();
    }

    // API: Получить мастеров по филиалу
    public function barbersByBranch($branchId)
    {
        return Barber::where('branch_id', $branchId)
            ->where('is_enabled', true)
            ->get(['id', 'name', 'photo']);
    }

    // API: Получить мастеров по услуге и филиалу
    public function barbersByService(Request $request, $serviceId)
    {
        $branchId = $request->get('branch_id');

        return Barber::whereHas('services', function ($q) use ($serviceId) {
            $q->where('services.id', $serviceId);
        })
            ->where('branch_id', $branchId)
            ->where('is_enabled', true)
            ->get(['id', 'name', 'photo']);
    }

    // API: Получить услуги, которые делает мастер в этом филиале
    public function servicesByBarber(Request $request, $barberId)
    {
        $branchId = $request->get('branch_id');

        return Service::whereHas('barbers', function ($q) use ($barberId) {
            $q->where('barbers.id', $barberId);
        })->get(['id', 'name', 'duration', 'price']);
    }

    // API: Получить доступные временные слоты
    public function availableTimes(Request $request, $barberId)
    {
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required|integer|exists:services,id',
        ]);

        $date = Carbon::parse($request->get('date'))->startOfDay();
        $serviceId = $request->get('service_id');

        $service = Service::findOrFail($serviceId);
        $duration = $service->duration ?? 60;

        $barber = Barber::with('schedules')->findOrFail($barberId);
        $dayOfWeek = strtolower($date->format('l'));
        $schedule = $barber->schedules->firstWhere('day_of_week', $dayOfWeek);

        if (!$schedule) {
            return response()->json([]);
        }

        $startParts = explode(':', $schedule->start_time);
        $endParts = explode(':', $schedule->end_time);

        $workStart = Carbon::createFromTime($startParts[0], $startParts[1]);
        $workEnd = Carbon::createFromTime($endParts[0], $endParts[1]);

        $appointments = Appointment::where('barber_id', $barberId)
            ->whereDate('date', $date)
            ->get();

        $availableSlots = [];

        for ($time = $workStart->copy(); $time->lte($workEnd->copy()->subMinutes($duration)); $time->addMinutes(30)) {
            $slotStart = $date->copy()->setTimeFrom($time);
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            $conflict = $appointments->contains(function ($a) use ($slotStart, $slotEnd) {
                return $slotStart->lt($a->end_time) && $slotEnd->gt($a->start_time);
            });

            if (!$conflict) {
                $availableSlots[] = $slotStart->format('H:i');
            }
        }

        return response()->json($availableSlots);
    }

    // API: Создание новой записи
    public function createAppointment(Request $request)
    {
        $validated = $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'time' => 'required', // формат H:i
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $duration = $service->duration ?? 60;

        $start = Carbon::parse("{$validated['date']} {$validated['time']}");
        $end = $start->copy()->addMinutes($duration);

        // Проверка на пересечения
        $hasConflict = Appointment::where('barber_id', $validated['barber_id'])
            ->whereDate('start_time', $start->toDateString())
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end->subMinute()])
                    ->orWhereBetween('end_time', [$start->addMinute(), $end]);
            })->exists();

        if ($hasConflict) {
            return response()->json(['error' => 'Выбранное время уже занято.'], 409);
        }

        $appointment = Appointment::create([
            'barber_id' => $validated['barber_id'],
            'service_id' => $validated['service_id'],
            'start_time' => $start,
            'end_time' => $end,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
        ]);

        return response()->json(['message' => 'Запись успешно создана!', 'appointment' => $appointment]);
    }

    public function barberWorkingDays(Request $request, $barberId)
    {
        $barber = Barber::with('schedules')->findOrFail($barberId);

        $daysMap = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        $workingDayNumbers = $barber->schedules->pluck('day_of_week')
            ->map(fn($day) => $daysMap[strtolower($day)])
            ->unique()
            ->values();

        return response()->json($workingDayNumbers);
    }

}
