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
        $admins = [
            '6899147031',
            '6256784114',
            '6960195534',
            '395590080',
        ];
        if (isset($update['business_message']) && isset($update['business_message']['text'])) {
            $userId = $update['business_message']['from']['id'];
            if (in_array($userId, $admins)) {
                return;
            }
            $nick = $update['business_message']['from']['username'];
            $username = $update['business_message']['from']['first_name'];
            $text = $update['business_message']['text'];
            $groupName = 'Личные сообщения';
            $lastMessage = Message::active()->where('user_tg', $userId)
                ->where('chat', $groupName)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($lastMessage && Carbon::now()->diffInMinutes($lastMessage->created_at) < 15) {
                return;
            } else {
                $response = Telegram::sendMessage([
                    'chat_id' => '-1002384608890',
                    'text' => "Содержимое сообщения:\n{$text}\n\n Пришло из: {$groupName} \n Ник пользователя в ТГ: @{$nick}\n Пользователь: {$username}",
                ]);
                $messageId = $response->getMessageId();
                $message = [
                    'message' => $text,
                    'user_tg' => $userId,
                    'client' => $username,
                    'message_id' => $messageId,
                    'chat' => $groupName
                ];

                Message::create($message);
            }
        }

        if ($update->getMessage()) {
            $userId = $update->getMessage()->getFrom()->getId();
            $text = $update->getMessage()->getText();
            if (in_array($userId, $admins) || $text == '/start') {
                return;
            }
            $chatId = $update->getMessage()->getChat()->getId();
            $chat = Telegram::getChat(['chat_id' => $chatId]);
            $groupName = $chat->getTitle();
            if (!$groupName) {
                $groupName = 'Личные сообщения';
            }
            $user = $update->getMessage()->getFrom();
            $nick = $user->getUsername();
            $username = $user->getFirstName() . " " . $user->getLastName();

            $lastMessage = Message::active()->where('user_tg', $userId)
                ->where('chat', $groupName)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastMessage && Carbon::now()->diffInMinutes($lastMessage->created_at) < 15) {
                return;
            } else {
                                $response = Telegram::sendMessage([
                    'chat_id' => '-1002384608890',
                    'text' => "Содержимое сообщения:\n{$text}\n\n Пришло из: {$groupName} \n Ник пользователя в ТГ: @{$nick}\n Пользователь: {$username}",
                ]);
                $messageId = $response->getMessageId();
                $message = [
                    'message' => $text,
                    'user_tg' => $userId,
                    'client' => $username,
                    'message_id' => $messageId,
                    'chat' => $groupName
                ];

                Message::create($message);
            }
        }
    }
}
