<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Services\TelegramBotService;

class TelegramBotController extends Controller
{
    protected $telegram;

    public function __construct(TelegramBotService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function webhook(Request $request)
    {
        $update = $this->telegram->getUpdates();

        $message = $update->getMessage();
        if (!$message) {
            return response()->json(['status' => 'no message'], 200);
        }

        $chatId = $message->getChat()->getId();
        $text = trim($message->getText());

        if (preg_match('/^\d+$/', $text)) {
            // Получаем записи по номеру телефона
            $appointments = Appointment::with('services')
                ->where('client_phone', $text)
                ->get();

            if ($appointments->isEmpty()) {
                $this->telegram->sendMessage($chatId, "Записей с номером {$text} не найдено.");
            } else {
                $reply = "Ваши записи:\n\n";
                foreach ($appointments as $appointment) {
                    $time = $appointment->time;
                    $status = $appointment->status;
                    $services = $appointment->services->map(function ($service) {
                        return $service->name . " ({$service->pivot->price} тг, {$service->pivot->duration} мин)";
                    })->implode(", ");

                    $reply .= "Дата/время: {$time}\n";
                    $reply .= "Статус: {$status}\n";
                    $reply .= "Услуги: {$services}\n\n";
                }

                $this->telegram->sendMessage($chatId, $reply);
            }
        } else {
            $this->telegram->sendMessage($chatId, "Привет! Отправьте номер телефона (только цифры), чтобы узнать свои записи.");
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
