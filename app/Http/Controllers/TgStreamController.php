<?php

namespace App\Http\Controllers;

use App\Services\TelegramStreamService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TgStreamController extends Controller
{
    public function __construct(
        protected TelegramStreamService $telegramStream
    ) {}

    /*
    |--------------------------------------------------------------------------
    | STREAM TELEGRAM VIDEO
    |--------------------------------------------------------------------------
    */

    public function stream(int $messageId, Request $request): StreamedResponse
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Basic Validation
            |--------------------------------------------------------------------------
            */
            if ($messageId <= 0) {
                abort(400, 'Invalid message ID');
            }

            /*
            |--------------------------------------------------------------------------
            | Delegate to Service (CORE LOGIC)
            |--------------------------------------------------------------------------
            */
            return $this->telegramStream->streamMessage(
                $messageId,
                $request
            );
        } catch (\Throwable $e) {

            $this->logError('Telegram stream failed', $e, [
                'message_id' => $messageId,
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            abort(500, 'Streaming failed');
        }
    }
}
