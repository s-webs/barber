<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Services\TelegramBotService;
use Illuminate\Support\Carbon;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramBotController extends Controller
{
    protected TelegramBotService $telegram;

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

        // 📱 Если пользователь отправил контакт (через кнопку "Отправить номер")
        if ($message->has('contact')) {
            $phone = $message->getContact()->getPhoneNumber();
            $formattedPhone = $this->formatPhoneLikeInDb($phone);
            return $this->sendAppointments($chatId, $formattedPhone);
        }

        $text = trim($message->getText());

        // 🟢 Если текст похож на номер телефона
        if (preg_match('/^\+?\d{10,12}$/', preg_replace('/\s+/', '', $text))) {
            $formattedPhone = $this->formatPhoneLikeInDb($text);
            return $this->sendAppointments($chatId, $formattedPhone);
        }

        // 🔘 Отправим приветствие и клавиатуру
        $keyboard = Keyboard::make()->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '📌 Записаться',
                    'url' => route('booking.index')
                ])
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '📅 Мои записи',
                    'callback_data' => 'send_phone'
                ])
            ]);

        $this->telegram->sendMessage(
            $chatId,
            "Привет! Чтобы посмотреть свои записи, отправьте номер вручную или нажмите кнопку ниже:",
            ['reply_markup' => $keyboard]
        );

        return response()->json(['status' => 'ok'], 200);
    }

    private function sendAppointments($chatId, string $formattedPhone)
    {
        $appointments = Appointment::with('services')
            ->where('client_phone', $formattedPhone)
            ->get();

        if ($appointments->isEmpty()) {
            $this->telegram->sendMessage($chatId, "Записей с номером {$formattedPhone} не найдено.");
        } else {
            $reply = "Ваши записи:\n\n";

            foreach ($appointments as $appointment) {
                $time = $appointment->time;
                $date = Carbon::parse($appointment->date)->format('d.m.Y');
                $status = $appointment->status;

                $services = $appointment->services->map(function ($service) {
                    return $service->name . " ({$service->pivot->price} тг, {$service->pivot->duration} мин)";
                })->implode(", ");

                $reply .= "📅 Дата: {$date}, время: {$time}\n";
                $reply .= "📌 Статус: {$status}\n";
                $reply .= "💈 Услуги: {$services}\n\n";
            }

            $this->telegram->sendMessage($chatId, $reply);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    private function formatPhoneLikeInDb(string $input): string
    {
        $digits = preg_replace('/\D+/', '', $input);

        if (strlen($digits) === 11 && str_starts_with($digits, '7')) {
            return '+7 ' . substr($digits, 1, 3) . ' ' . substr($digits, 4, 3) . ' ' . substr($digits, 7, 4);
        }

        return $input;
    }
}
