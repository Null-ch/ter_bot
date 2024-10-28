<?php

namespace App\Services;

use GuzzleHttp\Client;
use Telegram\Bot\Objects\Update;

class TelegramService
{
    const     admins = [
        '6899147031',
        '6256784114',
        '6960195534',
        '395590080',
        '344590941',
        '615007058',
        '774982582',
        '5000707181',
    ];
    const buisnessKeys = [
        'oxJk4Oab2EhrCQAAYWSsRiG7EZc' => '@HelpDesk_MO',
        'kJ7HBIpn2UgaCQAAWoNNGeoijfI' => '@HelpdeskOrionTerminal',
        'LJi3nkXG4EhiCQAArrgN6n2Zcrk' => '@HelpdeskTerminal'
    ];
    public function setWebhook(string $token, string $webhookRoute): string
    {
        $telegramUrl = 'https://api.telegram.org/bot' . $token . '/setWebhook?url=';
        $client = new Client();
        $response = $client->request('GET', $telegramUrl . $webhookRoute);

        $status = $response->getStatusCode();

        if ($status == 200) {
            return 'Вебхук успешно установлен!';
        } else {
            return 'Ошибка';
        }
    }

    public function removeWebhook(string $token): string
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

    public function getBusinessMessage(Update $updates)
    {
        return $updates['business_message'];
    }

    public function getBusinessMessageChatId(Update $updates)
    {
        return $updates['business_message']['chat']['id'];
    }

    public function getBusinessMessageUserId(Update $updates)
    {
        return $updates['business_message']['from']['id'];
    }

    public function getBusinessMessageConnectionId(Update $updates)
    {
        return $updates['business_message']['business_connection_id'];
    }
    public function getBusinessMessageText(Update $updates)
    {
        return $updates['business_message']['text'];
    }
    public function getBusinessMessageIncomeNick(Update $updates)
    {
        return $updates['business_message']['from']['username'];
    }
    public function getBusinessMessageIncomeгUsername(Update $updates)
    {
        return $updates['business_message']['from']['username'];
    }
}
