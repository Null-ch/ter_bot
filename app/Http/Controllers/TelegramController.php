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

        if (isset($update['business_message']) && isset($update['business_message']['text'])) {
            $userId = $update['business_message']['from']['id'];
            $nick = $update['business_message']['from']['username'];
            $username = $update['business_message']['from']['first_name'];
            $text = $update['business_message']['text'];
            $groupName = 'Личные сообщения';
            $lastMessage = Message::active()->where('user_tg', $userId)
                ->where('chat', $groupName)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($lastMessage && Carbon::now()->diffInMinutes($lastMessage->created_at) < 1) {
                return;
            } else {
                $message = [
                    'message' => $text,
                    'user_tg' => $userId,
                    'client' => $username,
                    'chat' => $groupName
                ];
                Message::create($message);
                Telegram::sendMessage([
                    // 'chat_id' => '-1002384608890',
                    'chat_id' => '395590080',
                    'text' => "Содержимое сообщения:\n{$text}\n\n Пришло из: {$groupName} \n Ник пользователя в ТГ: @{$nick}\n Пользователь: {$username}",
                ]);
            }
        }

        if ($update->getMessage()) {
            $chatId = $update->getMessage()->getChat()->getId();
            if ($userId == '395590080') {
                return;
            }
            $chat = Telegram::getChat(['chat_id' => $chatId]);
            $groupName = $chat->getTitle();
            $chatType = $update->getMessage()->getChat()->getType();
            $userId = $update->getMessage()->getFrom()->getId();
            $text = $update->getMessage()->getText();
            $user = $update->getMessage()->getFrom();
            $nick = $user->getUsername();
            $username = $user->getFirstName() . " " . $user->getLastName();

            $lastMessage = Message::active()->where('user_tg', $userId)
                ->where('chat', $groupName)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastMessage && Carbon::now()->diffInMinutes($lastMessage->created_at) < 1) {
                return;
            } else {
                if (!$groupName) {
                    $groupName = 'Личные сообщения';
                }

                $message = [
                    'message' => $text,
                    'user_tg' => $userId,
                    'client' => $username,
                    'chat' => $groupName
                ];
                Message::create($message);
                Telegram::sendMessage([
                    // 'chat_id' => '-1002384608890',
                    'chat_id' => '395590080',
                    'text' => "Содержимое сообщения:\n{$text}\n\n Пришло из: {$groupName} \n Ник пользователя в ТГ: @{$nick}\n Пользователь: {$username}",
                ]);
            }
        }
    }
}
