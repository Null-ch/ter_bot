<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $update = Telegram::getWebhookUpdates();
        if ($update->getMessage()) {
            $chatId = $update->getMessage()->getChat()->getId();
            $text = $update->getMessage()->getText();
            $userId = $update->getMessage()->getFrom()->getId();

            if ($text === '/start') {
                $keyboard = [
                    [
                        ['text' => 'Подать заявку!', 'callback_data' => 'appeal']
                    ]
                ];
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Привет! С помощью меня можно создать заявку',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $keyboard
                    ])
                ]);
            } else {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Спасибо',
                ]);
            }
        }

        if ($update->getCallbackQuery()) {
            $callbackQuery = $update->getCallbackQuery();
            $data = $callbackQuery->getData();
            $chatId = $callbackQuery->getMessage()->getChat()->getId();
            $userId = $callbackQuery->getFrom()->getId();

            $userData = session($userId, []);

            if ($data === 'appeal') {
                $list = "#Тема\n#Метка\n#Исполнитель\n#Услуга\n#Приоритет\n#Суть обращения"; 
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Пожалуйста, заполните форму обратной связи, используя следующие префиксы: #Тема\n#Метка\n#Исполнитель\n#Услуга\n#Приоритет\n#Суть обращения' . json_encode($list,JSON_UNESCAPED_UNICODE),
                ]);
            }

            return response()->json(['status' => 'success']);
        }
    }
}
