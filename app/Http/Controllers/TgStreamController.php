<?php

namespace App\Http\Controllers;

use App\Services\TelegramStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TgStreamController extends Controller
{
    public function __construct(
        protected TelegramStreamService $telegramStream
    ) {}

    public function stream(int $messageId, Request $request): StreamedResponse
    {
        try {
            // ✅ Basic validation safeguard
            if ($messageId <= 0) {
                abort(400, 'Invalid message ID');
            }

            // ✅ Delegate logic to service
            return $this->telegramStream->streamMessage($messageId, $request);

        } catch (\Throwable $e) {
            Log::error('Telegram stream failed', [
                'message_id' => $messageId,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Streaming failed');
        }
    }
}