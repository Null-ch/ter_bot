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
                    'text' => 'Вы написали: ' . $text,
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
                $keyboard = [
                    [
                        ['text' => 'Введите имя', 'callback_data' => 'name']
                    ],
                    [
                        ['text' => 'Введите email', 'callback_data' => 'email']
                    ],
                    [
                        ['text' => 'Введите сообщение', 'callback_data' => 'message']
                    ],
                    [
                        ['text' => 'Отправить', 'callback_data' => 'send']
                    ]
                ];
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Пожалуйста, заполните форму:',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $keyboard
                    ])
                ]);
            } elseif (in_array($data, ['name', 'email', 'message'])) {
                $text = $callbackQuery->getMessage()->getText(); 
                $userData[$data] = $text; 
                session(['user.' . $userId => $userData]);
    

                $keyboard = [
                    [
                        ['text' => 'Введите имя', 'callback_data' => 'name']
                    ],
                    [
                        ['text' => 'Введите email', 'callback_data' => 'email']
                    ],
                    [
                        ['text' => 'Введите сообщение', 'callback_data' => 'message']
                    ],
                    [
                        ['text' => 'Отправить', 'callback_data' => 'send']
                    ]
                ];
    
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Продолжайте заполнять форму:',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $keyboard
                    ])
                ]);
            } elseif ($data === 'send') {
                $name = $userData['name'] ?? '';
                $email = $userData['email'] ?? '';
                $message = $userData['message'] ?? '';
    
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Спасибо за подачу заявки!',
                ]);
            }
        }
    
        return response()->json(['status' => 'success']);
    }
}
