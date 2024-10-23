<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function handleUpdate(Request $request)
    {
        $response = Telegram::getMe();
        $update = Telegram::getWebhookUpdates();

        if ($update->getMessage()) {
            $chatId = $update->getMessage()->getChat()->getId();
            $text = $update->getMessage()->getText();

            // Обработка команды /start
            if ($text === '/start') {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Привет! Я ваш Telegram-бот. Что вы хотите сделать?',
                ]);
            } else {
                // Обработка других сообщений
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Вы написали: ' . $text,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
