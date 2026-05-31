<?php

namespace App\Http\Controllers;

use App\Services\TelegramStreamService;
use Illuminate\Http\Request;

class TgStreamController extends Controller
{
    public function stream(int $messageId, Request $request)
    {
        $service = new TelegramStreamService;

        return $service->streamMessage($messageId, $request);
    }
}
