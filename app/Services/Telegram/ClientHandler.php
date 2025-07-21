<?php

namespace App\Services\Telegram;

use App\Models\Appointment;
use App\Models\Barber;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class ClientHandler
{
    protected Api $telegram;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function menu($chatId): void
    {
        $keyboard = Keyboard::make([
            'keyboard' => [
                ['📌 Записаться'],
                ['📅 Мои записи'],
                ['🧔 Авторизация для мастера'],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Привет! Вы можете записаться или посмотреть свои записи.",
            'reply_markup' => $keyboard,
        ]);
    }

    public function handleClientCommands($chatId, $text): \Illuminate\Http\JsonResponse
    {
        if ($text === '📌 Записаться') {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Перейдите по ссылке: ' . route('booking.index'),
            ]);
        } elseif ($text === '📅 Мои записи') {
            $this->sendPhoneRequestKeyboard($chatId);
        } elseif ($text === '🧔 Авторизация для мастера') {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Пожалуйста, отправьте ваш токен авторизации:',
            ]);
        } elseif (preg_match('/^[a-f0-9\-]{36}$/', $text)) {
            $barber = Barber::where('auth_token', $text)->first();
            if ($barber) {
                $barber->update(['telegram_chat_id' => $chatId]);
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Вы успешно авторизованы как мастер: {$barber->name}",
                ]);
            } else {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Неверный токен авторизации.',
                ]);
            }
        } else {
            $this->menu($chatId);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    protected function sendPhoneRequestKeyboard($chatId): void
    {
        $keyboard = Keyboard::make([
            'keyboard' => [
                [Keyboard::button([
                    'text' => '📱 Отправить номер',
                    'request_contact' => true,
                ])]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Пожалуйста, отправьте свой номер телефона:',
            'reply_markup' => $keyboard,
        ]);
    }

    protected function handleClientPhone($chatId, $phone)
    {
        $formattedPhone = $this->formatPhoneLikeInDb($phone);
        return $this->sendAppointments($chatId, $formattedPhone);
    }

    protected function sendAppointments($chatId, $phone): void
    {
        $appointments = Appointment::with(['barber', 'services'])
            ->where('client_phone', $phone)
            ->orderBy('date')
            ->get();

        if ($appointments->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Записей не найдено.',
            ]);
            return;
        }

        $messageText = "*Ваши записи:*\n\n";
        foreach ($appointments as $appointment) {
            $date = \Carbon\Carbon::parse($appointment->date)->format('d.m.y');
            $time = \Carbon\Carbon::parse($appointment->time)->format('H:i');

            $messageText .= "📅 *{$date}* в 🕒 *{$time}*\n";
            $messageText .= "🧔 Мастер: *" . optional($appointment->barber)->name . "*\n";
            $messageText .= "💈 Услуги:\n";

            foreach ($appointment->services as $service) {
                $messageText .= "• {$service->name} ({$service->pivot->price}₸)\n";
            }

            $messageText .= "────────────\n";
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $messageText,
            'parse_mode' => 'Markdown',
        ]);
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
