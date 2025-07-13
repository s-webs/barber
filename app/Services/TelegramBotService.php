<?php

namespace App\Services;

use Telegram\Bot\Api;

class TelegramBotService
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api(config('services.telegram.bot_token'));
    }

    public function sendMessage($chatId, $text)
    {
        return $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    public function getUpdates()
    {
        return $this->telegram->getWebhookUpdates();
    }
}
