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

    public function success()
    {
        return view('pages.booking.success');
    }

    // API: Получить список филиалов
    public function branches()
    {
        return Branch::select('id', 'name', 'image', 'address')->get();
    }

    // API: Получить все услуги
    public function services()
    {
        return Service::select('id', 'name', 'price', 'duration')->get();
    }

    // API: Получить мастеров по филиалу
    public function barbersByBranch($branchId)
    {
        return Barber::where('branch_id', $branchId)
            ->where('is_enabled', true)
            ->get(['id', 'name', 'photo']);
    }

    // API: Получить мастеров по услуге и филиалу
    public function barbersByService(Request $request)
    {
        $branchId = $request->get('branch_id');
        $serviceIds = $request->get('service_ids', []);

        if (empty($serviceIds)) {
            return response()->json([], 400);
        }

        return Barber::where('branch_id', $branchId)
            ->where('is_enabled', true)
            ->whereHas('services', function ($q) use ($serviceIds) {
                $q->whereIn('services.id', $serviceIds);
            }, '=', count($serviceIds)) // <= магия здесь: требует ТОЛЬКО тех, у кого все услуги
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

    public function availableTimes(Request $request, $barberId)
    {
        $request->validate([
            'date' => 'required|date',
            'duration' => 'required|integer|min:1',
        ]);

        $date = Carbon::parse($request->get('date'))->startOfDay();
        $totalDuration = (int)$request->get('duration'); // Явное преобразование в int

        $barber = Barber::with('schedules')->findOrFail($barberId);
        $dayOfWeek = strtolower($date->format('l'));
        $schedule = $barber->schedules->firstWhere('day_of_week', $dayOfWeek);

        if (!$schedule) {
            return response()->json([]);
        }

        // Извлекаем время как числа
        [$startHour, $startMinute] = array_map('intval', explode(':', $schedule->start_time));
        [$endHour, $endMinute] = array_map('intval', explode(':', $schedule->end_time));

        $workStart = Carbon::createFromTime($startHour, $startMinute);
        $workEnd = Carbon::createFromTime($endHour, $endMinute);

        $appointments = Appointment::where('barber_id', $barberId)
            ->whereDate('date', $date)
            ->get()
            ->map(function ($app) {
                return [
                    'start' => Carbon::parse($app->start_time),
                    'end' => Carbon::parse($app->end_time)
                ];
            })
            ->filter(function ($app) {
                return $app['start']->lt($app['end']);
            });

        $availableSlots = [];
        $timeInterval = 30; // Шаг проверки в минутах

        $currentTime = $workStart->copy();
        $endTime = $workEnd->copy()->subMinutes($totalDuration);

        while ($currentTime->lte($endTime)) {
            // Правильное создание слота
            $slotStart = $date->copy()
                ->setHour($currentTime->hour)
                ->setMinute($currentTime->minute)
                ->setSecond(0);

            $slotEnd = $slotStart->copy()->addMinutes($totalDuration);

            $hasConflict = false;
            foreach ($appointments as $appointment) {
                if ($slotStart->lt($appointment['end']) && $slotEnd->gt($appointment['start'])) {
                    $hasConflict = true;
                    break;
                }
            }

            if (!$hasConflict) {
                $availableSlots[] = $slotStart->format('H:i');
            }

            $currentTime->addMinutes($timeInterval);
        }

        return response()->json($availableSlots);
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


    // API: Создание новой записи
    public function createAppointment(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'barber_id' => 'required|exists:barbers,id',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        $services = Service::whereIn('id', $validated['service_ids'])->get();
        $totalDuration = $services->sum('duration');

        $start = Carbon::parse("{$validated['date']} {$validated['time']}");
        $end = $start->copy()->addMinutes($totalDuration);

        $hasConflict = Appointment::where('barber_id', $validated['barber_id'])
            ->whereDate('start_time', $start->toDateString())
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
            })->exists();

        if ($hasConflict) {
            return response()->json(['error' => 'Выбранное время уже занято.'], 409);
        }

        // Создаём одну запись
        $appointment = Appointment::create([
            'barber_id' => $validated['barber_id'],
            'branch_id' => $validated['branch_id'],
            'start_time' => $start,
            'end_time' => $end,
            'date' => $start->toDateString(),
            'time' => $start->format('H:i:s'),
            'client_name' => $validated['customer_name'],
            'client_phone' => $validated['customer_phone'],
        ]);

        // Привязываем услуги к записи
        foreach ($services as $service) {
            $appointment->services()->attach($service->id, [
                'price' => $service->price,
                'duration' => $service->duration,
            ]);
        }

        return response()->json([
            'message' => 'Запись успешно создана!',
            'appointment' => $appointment
        ]);
    }

}
