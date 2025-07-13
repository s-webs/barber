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

        // 📱 Контакт (пришел через кнопку "📱 Отправить номер")
        if ($message->has('contact')) {
            $phone = $message->getContact()->getPhoneNumber();
            $formattedPhone = $this->formatPhoneLikeInDb($phone);
            return $this->sendAppointments($chatId, $formattedPhone);
        }

        $text = trim($message->getText());

        // ✅ UUID авторизация для мастера
        if (preg_match('/^[0-9a-fA-F\-]{36}$/', $text)) {
            $barber = \App\Models\Barber::where('auth_token', $text)->first();

            if ($barber) {
                $barber->telegram_chat_id = $chatId;
                $barber->save();

                $this->telegram->sendMessage($chatId, "✅ Авторизация успешна! Добро пожаловать, {$barber->name}.");
            } else {
                $this->telegram->sendMessage($chatId, "❌ Неверный токен. Пожалуйста, проверьте и попробуйте снова.");
            }

            return response()->json(['status' => 'ok'], 200);
        }

        // 🟢 Если текст похож на номер телефона
        if (preg_match('/^\+?\d{10,12}$/', preg_replace('/\s+/', '', $text))) {
            $formattedPhone = $this->formatPhoneLikeInDb($text);
            return $this->sendAppointments($chatId, $formattedPhone);
        }

        // 📌 Кнопка "Записаться"
        if ($text === '📌 Записаться') {
            $this->telegram->sendMessage($chatId, 'Перейдите по ссылке: ' . route('booking.index'));
            return response()->json(['status' => 'ok'], 200);
        }

        // 📅 Кнопка "Мои записи"
        if ($text === '📅 Мои записи') {
            $contactKeyboard = Keyboard::make([
                'keyboard' => [
                    [Keyboard::button([
                        'text' => '📱 Отправить номер',
                        'request_contact' => true,
                    ])]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]);

            $this->telegram->sendMessage($chatId, "Пожалуйста, отправьте свой номер телефона:", [
                'reply_markup' => $contactKeyboard,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        // 🧔 Кнопка "Авторизация для мастера"
        if ($text === '🧔 Авторизация для мастера') {
            $this->telegram->sendMessage($chatId, 'Пожалуйста, отправьте ваш токен авторизации:');
            return response()->json(['status' => 'ok'], 200);
        }

        if ($text === '👥 Мои клиенты') {
            $barber = \App\Models\Barber::where('telegram_chat_id', $chatId)->first();

            if (!$barber) {
                $this->telegram->sendMessage($chatId, "❗ Вы не авторизованы. Нажмите 🧔 Авторизация для мастера и отправьте ваш токен.");
                return response()->json(['status' => 'not authorized'], 200);
            }

            // Получим записи на сегодня
            $today = now()->toDateString();

            $appointments = \App\Models\Appointment::where('barber_id', $barber->id)
                ->where('date', $today)
                ->orderBy('time')
                ->take(10)
                ->get();

            if ($appointments->isEmpty()) {
                $this->telegram->sendMessage($chatId, "📭 На сегодня у вас нет записей.");
                return response()->json(['status' => 'no clients'], 200);
            }

            $messageText = "👥 Ваши клиенты на *" . now()->format('d.m.Y') . "*:\n\n";

            foreach ($appointments as $appointment) {
                $date = \Carbon\Carbon::parse($appointment->date)->format('d.m.y');
                $time = \Carbon\Carbon::parse($appointment->time)->format('H:i');

                $messageText .= "📅 *{$date}* в 🕒 *{$time}*\n";
                $messageText .= "👤 {$appointment->client_name}\n";
                $messageText .= "📞 {$appointment->client_phone}\n";

                $messageText .= "────────────\n";
            }

            $this->telegram->sendMessage($chatId, $messageText, [
                'parse_mode' => 'Markdown'
            ]);

            return response()->json(['status' => 'ok'], 200);
        }

        // 👋 Стартовое сообщение и меню
        $keyboard = Keyboard::make([
            'keyboard' => [
                ['📌 Записаться'],
                ['📅 Мои записи'],
                ['🧔 Авторизация для мастера'],
                ['👥 Мои клиенты'],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ]);

        $this->telegram->sendMessage(
            $chatId,
            "Привет! Чтобы посмотреть свои записи или авторизоваться как мастер, выберите нужный пункт меню ниже:",
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
