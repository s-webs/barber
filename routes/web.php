<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home.index');

Route::get('/booking', [\App\Http\Controllers\Public\BookingController::class, 'index'])->name('booking.index');

Route::get('/services', [\App\Http\Controllers\Public\ServicesController::class, 'index'])->name('services.index');

Route::get('/contacts', [\App\Http\Controllers\Public\ContactsController::class, 'index'])->name('contacts.index');

Route::get('/about', [\App\Http\Controllers\Public\AboutController::class, 'index'])->name('about.index');

// API-роуты внутри контроллера
Route::prefix('/api')->group(function () {
    Route::get('/branches', [\App\Http\Controllers\Public\BookingController::class, 'branches']);
    Route::get('/services', [\App\Http\Controllers\Public\BookingController::class, 'services']);
    Route::get('/barbers/by-branch/{branch}', [\App\Http\Controllers\Public\BookingController::class, 'barbersByBranch']);
    Route::get('/barbers/by-service/{service}', [\App\Http\Controllers\Public\BookingController::class, 'barbersByService']);
    Route::get('/services/by-barber/{barber}', [\App\Http\Controllers\Public\BookingController::class, 'servicesByBarber']);
    Route::get('/barbers/{barber}/available-times', [\App\Http\Controllers\Public\BookingController::class, 'availableTimes']);
    Route::get('/barbers/{barber}/working-days', [\App\Http\Controllers\Public\BookingController::class, 'barberWorkingDays']);
});
