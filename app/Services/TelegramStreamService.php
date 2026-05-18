<?php

namespace App\Services;

use danog\MadelineProto\API;
use danog\MadelineProto\Settings\AppInfo;

class TelegramStreamService
{
    protected ?API $madeline = null;

    protected function api(): API
    {
        if ($this->madeline === null) {
            $dir = storage_path('app/telegram-session');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $settings = new AppInfo;
            $settings->setApiId((int) config('services.telegram.api_id'));
            $settings->setApiHash(config('services.telegram.api_hash'));

            $this->madeline = new API($dir . '/bot_session.madeline', $settings);
            $this->madeline->start();

            if (! $this->madeline->isBotLoggedIn()) {
                $this->madeline->botLogin(config('services.telegram.bot_token'));
            }
        }

        return $this->madeline;
    }

    public function streamMessage(int $messageId): void
    {
        $channel = config('services.telegram.channel_id');

        try {
            $messages = $this->api()->getMessages($channel, [$messageId]);
            $message = $messages['messages'][0] ?? null;

            if (! $message) {
                http_response_code(404);
                echo json_encode(['error' => 'Message not found']);

                return;
            }

            set_time_limit(0);

            while (ob_get_level()) {
                ob_end_clean();
            }

            $this->api()->downloadToBrowser($message);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getMessageInfo(int $messageId): ?array
    {
        $channel = config('services.telegram.channel_id');

        try {
            $messages = $this->api()->getMessages($channel, [$messageId]);
            $message = $messages['messages'][0] ?? null;

            if (! $message) {
                return null;
            }

            $media = $message['media'] ?? null;
            if (! $media) {
                return null;
            }

            $document = $media['document'] ?? $media['video'] ?? null;
            if (! $document) {
                return null;
            }

            $videoAttr = null;
            foreach ($document['attributes'] ?? [] as $attr) {
                if (($attr['_'] ?? '') === 'documentAttributeVideo') {
                    $videoAttr = $attr;
                    break;
                }
            }

            return [
                'file_id' => $document['id'] ?? null,
                'access_hash' => $document['access_hash'] ?? null,
                'file_reference' => $document['file_reference'] ?? null,
                'dc_id' => $document['dc_id'] ?? null,
                'size' => $document['size'] ?? null,
                'mime_type' => $document['mime_type'] ?? null,
                'duration' => $videoAttr['duration'] ?? null,
                'width' => $videoAttr['w'] ?? null,
                'height' => $videoAttr['h'] ?? null,
                'caption' => $message['message'] ?? null,
                'date' => $message['date'] ?? null,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
