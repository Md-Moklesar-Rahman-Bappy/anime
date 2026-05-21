<?php

namespace App\Http\Controllers;

use App\Services\TelegramStreamService;

class TgStreamController extends Controller
{
    public function stream(int $messageId)
    {
        $service = new TelegramStreamService();
        $service->streamMessage($messageId);
        exit;
    }
}
