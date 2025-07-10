<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home.index');

Route::get('/booking', [\App\Http\Controllers\Public\BookingController::class, 'index'])->name('booking.index');

// API-роуты внутри контроллера
Route::prefix('/api')->group(function () {
    Route::get('/branches', [\App\Http\Controllers\Public\BookingController::class, 'branches']);
    Route::get('/services', [\App\Http\Controllers\Public\BookingController::class, 'services']);
    Route::get('/barbers/by-branch/{branch}', [\App\Http\Controllers\Public\BookingController::class, 'barbersByBranch']);
    Route::get('/barbers/by-service/{service}', [\App\Http\Controllers\Public\BookingController::class, 'barbersByService']);
    Route::get('/services/by-barber/{barber}', [\App\Http\Controllers\Public\BookingController::class, 'servicesByBarber']);

});
