<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Service;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        return view('pages.booking.index');
    }

    public function branches()
    {
        return Branch::select('id', 'name', 'image', 'address')->get();
    }

    // API: Получить все услуги
    public function services()
    {
        return Service::select('id', 'name')->get();
    }

    // API: Получить мастеров по филиалу
    public function barbersByBranch($branchId)
    {
        return Barber::where('branch_id', $branchId)
            ->where('is_enabled', true)
            ->get();
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
            ->get();
    }

    // API: Получить услуги по мастеру и филиалу
    public function servicesByBarber(Request $request, $barberId)
    {
        $branchId = $request->get('branch_id');

        return Service::whereHas('barbers', function ($q) use ($barberId) {
            $q->where('barbers.id', $barberId);
        })
            ->get();
    }

}
