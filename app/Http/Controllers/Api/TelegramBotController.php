<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramBotController extends Controller
{
    protected Api $telegram;

    public function __construct()
    {
        $this->telegram = new Api(config('services.telegram.bot_token'));
    }

    public function webhook(Request $request)
    {
        $update = $this->telegram->getWebhookUpdate();
        $message = $update->getMessage();

        if (!$message) {
            return response()->json(['status' => 'no message'], 200);
        }

        $chatId = $message->getChat()->getId();
        $text = trim($message->getText());

        // Контакт — клиент
        if ($message->has('contact')) {
            $phone = $message->getContact()->getPhoneNumber();
            return $this->handleClientPhone($chatId, $phone);
        }

        // Проверка авторизации
        $barber = Barber::where('telegram_chat_id', $chatId)->first();

        // === Команды для барбера ===
        if ($barber) {
            return $this->handleBarberCommands($chatId, $text, $barber);
        }

        // === Команды для клиента ===
        return $this->handleClientCommands($chatId, $text);
    }

    protected function handleClientCommands($chatId, $text)
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
            $this->sendClientDefaultKeyboard($chatId);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    protected function handleBarberCommands($chatId, $text, Barber $barber)
    {
        if ($text === '📋 Мои клиенты') {
            return $this->sendBarberAppointments($chatId, $barber);
        }

        if ($text === '🧑‍💼 Мой профиль') {
            return $this->sendBarberProfile($chatId, $barber);
        }

        if ($text === '🚪 Выйти') {
            $barber->update(['telegram_chat_id' => null]);
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Вы вышли из аккаунта мастера.',
            ]);
            $this->sendClientDefaultKeyboard($chatId);
        } else {
            $this->sendBarberDefaultKeyboard($chatId);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    protected function sendPhoneRequestKeyboard($chatId)
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

    protected function sendClientDefaultKeyboard($chatId)
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

    protected function sendBarberDefaultKeyboard($chatId)
    {
        $keyboard = Keyboard::make([
            'keyboard' => [
                ['📋 Мои клиенты'],
                ['🧑‍💼 Мой профиль'],
                ['🚪 Выйти'],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Привет! Вы авторизованы как мастер.",
            'reply_markup' => $keyboard,
        ]);
    }

    protected function handleClientPhone($chatId, $phone)
    {
        $formattedPhone = $this->formatPhoneLikeInDb($phone);
        return $this->sendAppointments($chatId, $formattedPhone);
    }

    protected function sendAppointments($chatId, $phone)
    {
        $appointments = Appointment::where('client_phone', $phone)->orderBy('date')->get();

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
            $messageText .= "👤 {$appointment->client_name}\n";
            $messageText .= "📞 {$appointment->client_phone}\n";
            $messageText .= "────────────\n";
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $messageText,
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function sendBarberAppointments($chatId, Barber $barber)
    {
        $appointments = $barber->appointments()->whereDate('date', '>=', now())->orderBy('date')->get();

        if ($appointments->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'У вас пока нет записей.',
            ]);
            return;
        }

        $messageText = "*Ваши клиенты:*\n\n";
        foreach ($appointments as $appointment) {
            $date = Carbon::parse($appointment->date)->format('d.m.y');
            $time = Carbon::parse($appointment->time)->format('H:i');
            $messageText .= "📅 *{$date}* в 🕒 *{$time}*\n";
            $messageText .= "👤 {$appointment->client_name}\n";
            $messageText .= "📞 {$appointment->client_phone}\n";

            foreach ($appointment->services as $service) {
                $messageText .= "▫️ {$service->name} ({$service->pivot->price}₸)\n";
            }

            $messageText .= "────────────\n";
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $messageText,
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function sendBarberProfile($chatId, Barber $barber)
    {
        $caption = "*Профиль мастера*\n\n";
        $caption .= "👤 *{$barber->name}*\n";
        $caption .= "📱 {$barber->phone}\n";
        $caption .= "📍 Филиал: " . optional($barber->branch)->name . "\n";

        if ($barber->photo) {
            $photoUrl = public_path($barber->photo);

            $this->telegram->sendPhoto([
                'chat_id' => $chatId,
                'photo' => InputFile::create($photoUrl),
                'caption' => $caption,
                'parse_mode' => 'Markdown',
            ]);
        } else {
            $caption .= "🖼 Фото: _не загружено_";

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $caption,
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    protected function formatPhoneLikeInDb($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);
        return '7' . substr($phone, -10); // Пример: 77071234567
    }
}
