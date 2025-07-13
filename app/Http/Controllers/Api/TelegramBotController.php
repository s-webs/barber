<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Services\TelegramBotService;

class TelegramBotController extends Controller
{
    protected TelegramBotService $telegram;

    public function __construct(TelegramBotService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function webhook(Request $request)
    {
        $update = $this->telegram->getWebhookUpdate(); // используем Webhook update

        $message = $update->getMessage();
        if (!$message) {
            return response()->json(['status' => 'no message'], 200);
        }

        $chatId = $message->getChat()->getId();
        $text = trim($message->getText());

        // Удалим пробелы и проверим, похоже ли это на номер
        if (preg_match('/^\+?\d{10,12}$/', preg_replace('/\s+/', '', $text))) {
            $formattedPhone = $this->formatPhoneLikeInDb($text);

            $appointments = Appointment::with('services')
                ->where('client_phone', $formattedPhone)
                ->get();

            if ($appointments->isEmpty()) {
                $this->telegram->sendMessage($chatId, "Записей с номером {$formattedPhone} не найдено.");
            } else {
                $reply = "Ваши записи:\n\n";

                foreach ($appointments as $appointment) {
                    $time = $appointment->time;
                    $status = $appointment->status;

                    $services = $appointment->services->map(function ($service) {
                        return $service->name . " ({$service->pivot->price} тг, {$service->pivot->duration} мин)";
                    })->implode(", ");

                    $reply .= "📅 Дата/время: {$time}\n";
                    $reply .= "📌 Статус: {$status}\n";
                    $reply .= "💈 Услуги: {$services}\n\n";
                }

                $this->telegram->sendMessage($chatId, $reply);
            }
        } else {
            $this->telegram->sendMessage($chatId, "Привет! Пожалуйста, отправьте номер телефона в формате +77007102135 (без пробелов), чтобы получить свои записи.");
        }

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Приводит номер к формату, в котором он хранится в базе: +7 700 710 2135
     */
    private function formatPhoneLikeInDb(string $input): string
    {
        // Удалим всё, кроме цифр
        $digits = preg_replace('/\D+/', '', $input);

        // Преобразуем в формат +7 700 710 2135
        if (strlen($digits) === 11 && str_starts_with($digits, '7')) {
            return '+7 ' . substr($digits, 1, 3) . ' ' . substr($digits, 4, 3) . ' ' . substr($digits, 7, 4);
        }

        return $input; // fallback, если номер нераспознан
    }
}
