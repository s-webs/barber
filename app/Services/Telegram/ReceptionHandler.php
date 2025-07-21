<?php

namespace App\Services\Telegram;

use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class ReceptionHandler
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
                ['📅 Записи на сегодня'],
                ['🚪 Выйти'],
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
}
