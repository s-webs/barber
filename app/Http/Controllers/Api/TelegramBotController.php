<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Reception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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

        // Если барбер загружает фото для обновления профиля
        if ($message->has('photo')) {
            $this->handlePhotoMessage($chatId, $message->getPhoto());
            return response()->json(['status' => 'photo handled'], 200);
        }

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

        $reception = Reception::where('telegram_chat_id', $chatId)->first();

        if ($reception) {
            return $this->handleReceptionCommands($chatId, $text, $reception);
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
        } // Обработка обеих кнопок авторизации
        elseif ($text === '🧔 Авторизация для мастера' || $text === '👩‍💼 Авторизация для ресепшена') {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Пожалуйста, отправьте ваш токен авторизации:',
            ]);
        } // Проверка токена
        elseif (preg_match('/^[a-f0-9\-]{36}$/', $text)) {
            // Проверка токена мастера
            $barber = \App\Models\Barber::where('auth_token', $text)->first();
            if ($barber) {
                $barber->update(['telegram_chat_id' => $chatId]);
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Вы успешно авторизованы как мастер: {$barber->name}",
                ]);
                $this->sendBarberDefaultKeyboard($chatId);
                return response()->json(['status' => 'ok'], 200);
            }

            // Проверка токена ресепшена
            $reception = \App\Models\Reception::where('auth_token', $text)->first();
            if ($reception) {
                $reception->update(['telegram_chat_id' => $chatId]);
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Вы авторизованы как ресепшен: {$reception->name}",
                ]);
                $this->sendReceptionDefaultKeyboard($chatId);
                return response()->json(['status' => 'ok'], 200);
            }

            // Если токен не найден
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Неверный токен авторизации.',
            ]);
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

        if ($text === '✏️ Изменить фото') {
            return $this->handleChangePhotoCommand($chatId);
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
                ['👩‍💼 Авторизация для ресепшена'],
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
                ['✏️ Изменить фото'],
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

    protected function sendReceptionDefaultKeyboard($chatId)
    {
        $keyboard = Keyboard::make([
            'keyboard' => [
                ['📅 Записи на сегодня'],
                ['🚪 Выйти'],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Вы вошли как ресепшен. Выберите действие:',
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
        $caption .= "📍 Филиал: " . optional($barber->branch)->name . "\n";

        // 🛠 Услуги мастера
        $services = $barber->services;
        if ($services->isNotEmpty()) {
            $caption .= "\n💈 *Услуги:*\n";
            foreach ($services as $service) {
                $caption .= "• {$service->name} — {$service->price}₸\n";
            }
        } else {
            $caption .= "\n💈 Услуги не указаны\n";
        }

        // 🖼 Фото профиля
        if ($barber->photo) {
            $photoPath = public_path($barber->photo); // ❗ без добавления 'uploads/barbers/'

            if (file_exists($photoPath)) {
                $this->telegram->sendPhoto([
                    'chat_id' => $chatId,
                    'photo' => InputFile::create($photoPath),
                    'caption' => $caption,
                    'parse_mode' => 'Markdown',
                ]);
                return;
            } else {
                $caption .= "\n⚠️ *Фото не найдено*";
            }
        } else {
            $caption .= "\n🖼 Фото: _не загружено_";
        }

        // Fallback: текстовое сообщение
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $caption,
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

    protected function handleChangePhotoCommand($chatId)
    {
        Cache::put("barber_waiting_photo_$chatId", true, now()->addMinutes(5)); // Запомним на 5 минут

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => '📷 Пожалуйста, отправьте новое фото профиля',
        ]);
    }

    protected function handlePhotoMessage($chatId, $photo)
    {
        if (!Cache::get("barber_waiting_photo_$chatId")) {
            return; // Никакой команды не было
        }

        $barber = Barber::where('telegram_chat_id', $chatId)->first();
        if (!$barber) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Профиль не найден',
            ]);
            return;
        }

        // Берем максимальное по качеству фото
        $photoId = collect($photo)->last()['file_id'];

        // Получаем файл Telegram
        $file = $this->telegram->getFile(['file_id' => $photoId]);
        $filePath = $file->getFilePath();
        $url = "https://api.telegram.org/file/bot" . config('services.telegram.bot_token') . "/$filePath";

        // Генерируем имя
        $filename = uniqid() . '.jpg';
        $savePath = public_path("uploads/barbers/$filename");

        // Скачиваем и сохраняем
        file_put_contents($savePath, file_get_contents($url));

        // Обновляем запись в базе
        $barber->photo = "uploads/barbers/$filename";
        $barber->save();

        // Удаляем из кэша
        Cache::forget("barber_waiting_photo_$chatId");

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => '✅ Фото успешно обновлено!',
        ]);
    }

    protected function handleReceptionCommands($chatId, $text, \App\Models\Reception $reception)
    {
        if ($text === '📅 Записи на сегодня') {
            $appointments = Appointment::with(['barber', 'services'])
                ->where('date', now()->format('Y-m-d'))
                ->whereHas('barber', fn($q) => $q->where('branch_id', $reception->branch_id))
                ->orderBy('time')
                ->get();

            if ($appointments->isEmpty()) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'На сегодня записей нет.',
                ]);
                return response()->json(['status' => 'ok']);
            }

            $messageText = "*Записи на сегодня:*\n\n";

            foreach ($appointments as $appointment) {
                $time = Carbon::parse($appointment->time)->format('H:i');
                $messageText .= "🕒 *{$time}* — {$appointment->client_name} ({$appointment->client_phone})\n";
                $messageText .= "🧔 Мастер: " . optional($appointment->barber)->name . "\n";

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
        } elseif ($text === '🚪 Выйти') {
            $reception->update(['telegram_chat_id' => null]);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Вы вышли из аккаунта ресепшена.',
            ]);

            $this->sendClientDefaultKeyboard($chatId);
        } else {
            $this->sendReceptionDefaultKeyboard($chatId);
        }

        return response()->json(['status' => 'ok']);
    }

}
