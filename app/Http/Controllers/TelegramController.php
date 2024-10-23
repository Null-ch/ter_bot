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
        $update = Telegram::getWebhookUpdate();
        // Проверяем, содержит ли обновление сообщение
        if ($update->getMessage()) {
            // Получаем ID чата
            $chatId = $update->getMessage()->getChat()->getId();
            // Получаем текст сообщения
            $text = $update->getMessage()->getText();

            // Обработка сообщения
            if ($text === '/start') {
                // Отправка приветственного сообщения
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Привет! Я ваш Telegram-бот.',
                ]);
            } else {
                // Обработка других сообщений
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Вы написали: ' . $text,
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
