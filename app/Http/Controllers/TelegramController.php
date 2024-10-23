<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Message;
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
        // $list = [
        //     'Тема',
        //     'Метка',
        //     'Исполнитель',
        //     'Услуга',
        //     'Приоритет',
        //     'Суть обращения'
        // ];
        if ($update->getMessage()) {
            $chatId = $update->getMessage()->getChat()->getId();
            $userId = $update->getMessage()->getFrom()->getId();
            $text = $update->getMessage()->getText();
            $user = $update->getMessage()->getFrom();
            $nick = $user->getUsername();
            $username = $user->getFirstName() . $user->getLastName();
            $chat = Telegram::getChat(['chat_id' => $chatId]);
            $groupName = $chat->getTitle();

            $lastMessage = Message::active()->where('user_tg', (string)$userId)
                ->where('chat', (string)$chat)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastMessage && Carbon::now()->diffInMinutes($lastMessage->created_at) < 1) {
                return;
            } else {
                $message = [
                    'message' => $text,
                    'user_tg' => $userId,
                    'chat' => $chat
                ];
                Message::create($message);
                Telegram::sendMessage([
                    'chat_id' => '-1002384608890',
                    'text' => "Содержимое сообщения:\n{$text}\n\n Пришло из: {$groupName} \n Ник пользователя в ТГ: @{$nick}\n Пользователь: {$username}",
                ]);
            }

            // if ($text === '/start') {
            //     $keyboard = [
            //         [
            //             ['text' => 'Подать заявку!', 'callback_data' => 'appeal']
            //         ]
            //     ];
            //     Telegram::sendMessage([
            //         'chat_id' => $chatId,
            //         'text' => 'Привет! С помощью меня можно создать заявку',
            //         'reply_markup' => json_encode([
            //             'inline_keyboard' => $keyboard
            //         ])
            //     ]);
            // } elseif ($this->checkAllWordsPresent($text, $list)) {
            //     Telegram::sendMessage([
            //         'chat_id' => '-1002384608890',
            //         'text' => $text . "\n Ник в ТГ: @{$nick}\n Пользователь: {$username}",
            //     ]);
            // }
        }

        // if ($update->getCallbackQuery()) {
        //     $callbackQuery = $update->getCallbackQuery();
        //     $data = $callbackQuery->getData();
        //     $chatId = $callbackQuery->getMessage()->getChat()->getId();
        //     $userId = $callbackQuery->getFrom()->getId();

        //     if ($data === 'appeal') {
        //         $telegramMessage = "Пожалуйста, заполните форму обратной связи, используя следующие префиксы:\n\n" . implode("\n", $list);
        //         Telegram::sendMessage([
        //             'chat_id' => $chatId,
        //             'text' => $telegramMessage
        //         ]);
        //     }

        //     return response()->json(['status' => 'success']);
        // }
    }
    // function checkAllWordsPresent($text, $wordsList) {
    //     $formattedText = strtolower($text);
    //     $result = true;
    //     foreach ($wordsList as $word) {
    //         if (strpos($formattedText, strtolower($word)) !== false) {
    //             $result = true;
    //         } else {
    //             $result = false;
    //         }
    //     }

    //     return $result;
    // }
}
