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

    public function sendMessage($chatId, $text, array $params = [])
    {
        return $this->telegram->sendMessage(array_merge([
            'chat_id' => $chatId,
            'text' => $text,
        ], $params));
    }

    public function getUpdates()
    {
        return $this->telegram->getWebhookUpdates();
    }
}
