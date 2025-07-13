<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');


Route::get('/find-appointments-phone', [\App\Http\Controllers\Api\BookingController::class, 'findAppointments']);

Route::post('/telegram/webhook', [\App\Http\Controllers\Api\TelegramBotController::class, 'webhook']);
