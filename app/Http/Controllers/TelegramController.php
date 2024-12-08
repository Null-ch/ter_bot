<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Message;
use BadMethodCallException;
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

    public function removeWebhook()
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $telegramUrl = 'https://api.telegram.org/bot' . $token . '/deleteWebhook';

        $client = new Client();
        $response = $client->request('GET', $telegramUrl);

        $status = $response->getStatusCode();

        if ($status == 200) {
            return 'Вебхук успешно удален!';
        } else {
            return 'Ошибка при удалении вебхука';
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
            '344590941',
            '615007058',
            '774982582',
            '5000707181',
        ];

        if (isset($update['business_message']) && isset($update['business_message']['text'])) {
            $userId = $update['business_message']['from']['id'];
            if (in_array($userId, $admins)) {
                return;
            }
            $businessConnectionId = $update['business_message']['business_connection_id'];
            $currentAccount = $this->getBusinessConnectionDetails($businessConnectionId);
            $chatId = $update['business_message']['chat']['id'];
            $nick = isset($update['business_message']['from']['username']) ? "\nНик пользователя в ТГ:" . "@" . $update['business_message']['from']['username'] : "\n";
            $username = $update['business_message']['from']['first_name'];
            $text = $update['business_message']['text'];
            $groupName = 'Личные сообщения';
            $lastMessage = Message::active()->where('user_tg', $userId)
                ->where('chat', $groupName)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($lastMessage && Carbon::now()->diffInMinutes($lastMessage->created_at) < 60) {
                return;
            } else {
                $response = Telegram::sendMessage([
                    'chat_id' => '-1002384608890',
                    'text' => "Аккаунт: {$currentAccount}\nСодержимое сообщения:\n{$text}\n\nПришло из: {$groupName} {$nick}\nПользователь: {$username}",
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
        } elseif ($update->getMessage()) {
            try {
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

                if ($lastMessage && Carbon::now()->diffInMinutes($lastMessage->created_at) < 60) {
                    return;
                } else {
                    $response = Telegram::sendMessage([
                        'chat_id' => '-1002384608890',
                        'text' => "Содержимое сообщения:\n{$text}\n\nПришло из: {$groupName} \nНик пользователя в ТГ: @{$nick}\n Пользователь: {$username}",
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
            } catch (BadMethodCallException $e) {
                Log::error("Failed to get user ID: {$e->getMessage()}");
            }
        }
    }

    function getBusinessConnectionDetails($businessConnectionId)
    {
        return match ($businessConnectionId) {
            'oxJk4Oab2EhrCQAAYWSsRiG7EZc' => '@HelpDesk_MO',
            'kJ7HBIpn2UgaCQAAWoNNGeoijfI' => '@HelpdeskOrionTerminal',
            'LJi3nkXG4EhiCQAArrgN6n2Zcrk' => '@HelpdeskTerminal',
            default => null
        };
    }
}
