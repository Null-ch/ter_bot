<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
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
    public function handleWebhook(Request $request)
    {
        $updates = Telegram::getWebhookUpdates();
        $message = $updates['message']['text'];
        $chat = $updates['message']['chat']['id'];

        if ($message === '/start') {
            Telegram::sendMessage([
                'chat_id' => $chat,
                'text' => 'Добрый день, чем можем помочь?',
                'parse_mode' => 'HTML'
            ]);
            return;
        }
    }
}
