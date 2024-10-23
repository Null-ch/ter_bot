<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $updates = Telegram::getWebhookUpdate();
        $message = $updates['message']['text'];
        $chat = $updates['message']['chat']['id'];
        // if ($updates->getMessage()) {
        //     $chatId = $updates->getMessage()->getChat()->getId();
        //     $text = $updates->getMessage()->getText();

        //     // Обработка команды /start
        //     if ($text === '/start') {
        //         Telegram::sendMessage([
        //             'chat_id' => $chatId,
        //             'text' => 'Привет! Я ваш Telegram-бот. Что вы хотите сделать?',
        //         ]);
        //     } else {
        //         // Обработка других сообщений
        //         Telegram::sendMessage([
        //             'chat_id' => $chatId,
        //             'text' => 'Вы написали: ' . $text,
        //         ]);
        //     }
        // }
        // if ($message === '/start') {
            Telegram::sendMessage([
                'chat_id' => $chat,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
        // }
    }

    public function setWebhook()
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $telegramUrl = 'https://api.telegram.org/bot' . $token . '/setWebhook?url=';
        $route = route('telegram_webhook');

        $client = new Client();
        $response = $client->request('GET', $telegramUrl . $route);

        $status = $response->getStatusCode();

        if ($status == 200) {
            return 'Вебхук успешно установлен!';
        } else {
            return 'Ошибка';
        }
    }
}
