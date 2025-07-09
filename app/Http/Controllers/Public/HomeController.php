<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Service;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $services = Service::query()->take(3)->get();
        $barbers = Barber::query()->where('is_enabled', '=', true)->get();
        return view('pages.home.index', compact('services', 'barbers'));
    }
}
