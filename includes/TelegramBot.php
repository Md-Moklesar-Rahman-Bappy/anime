<?php
class TelegramBot {
    private static ?TelegramBot $instance = null;
    private string $token;
    private string $api_url;
    private ?array $webhook_data = null;

    private function __construct() {
        $this->token = TELEGRAM_BOT_TOKEN;
        $this->api_url = 'https://api.telegram.org/bot' . $this->token . '/';
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function call(string $method, array $params = []): ?array {
        $url = $this->api_url . $method;
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($params),
                'timeout' => 10,
            ]
        ]);
        $response = @file_get_contents($url, false, $ctx);
        if (!$response) return null;
        $data = json_decode($response, true);
        return ($data['ok'] ?? false) ? $data : null;
    }

    public function setWebhook(string $url): bool {
        $result = $this->call('setWebhook', [
            'url' => $url,
            'allowed_updates' => ['message', 'callback_query', 'channel_post']
        ]);
        return $result !== null;
    }

    public function deleteWebhook(): bool {
        return $this->call('deleteWebhook') !== null;
    }

    public function getWebhookInfo(): ?array {
        return $this->call('getWebhookInfo');
    }

    public function getMe(): ?array {
        return $this->call('getMe');
    }

    public function sendMessage(int|string $chat_id, string $text, string $parse_mode = 'HTML', array $extra = []): ?array {
        $params = array_merge([
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $parse_mode,
        ], $extra);
        return $this->call('sendMessage', $params);
    }

    public function sendPhoto(int|string $chat_id, string $photo_url, string $caption = '', array $extra = []): ?array {
        $params = array_merge([
            'chat_id' => $chat_id,
            'photo' => $photo_url,
            'caption' => $caption,
            'parse_mode' => 'HTML',
        ], $extra);
        return $this->call('sendPhoto', $params);
    }

    public function sendAnimation(int|string $chat_id, string $animation_url, string $caption = ''): ?array {
        return $this->call('sendAnimation', [
            'chat_id' => $chat_id,
            'animation' => $animation_url,
            'caption' => $caption,
            'parse_mode' => 'HTML',
        ]);
    }

    public function getFile(string $file_id): ?array {
        return $this->call('getFile', ['file_id' => $file_id]);
    }

    public function getFileUrl(string $file_path): string {
        return 'https://api.telegram.org/file/bot' . $this->token . '/' . $file_path;
    }

    public function getChat(string $chat_id): ?array {
        return $this->call('getChat', ['chat_id' => $chat_id]);
    }

    public function getChatMember(string $chat_id, string $user_id): ?array {
        return $this->call('getChatMember', ['chat_id' => $chat_id, 'user_id' => $user_id]);
    }

    public function getChatAdministrators(string $chat_id): ?array {
        return $this->call('getChatAdministrators', ['chat_id' => $chat_id]);
    }

    public function copyMessage(int|string $from_chat_id, int $message_id, int|string $chat_id, array $extra = []): ?array {
        $params = array_merge([
            'from_chat_id' => $from_chat_id,
            'message_id' => $message_id,
            'chat_id' => $chat_id,
        ], $extra);
        return $this->call('copyMessage', $params);
    }

    public function forwardMessage(int|string $from_chat_id, int $message_id, int|string $chat_id): ?array {
        return $this->call('forwardMessage', [
            'from_chat_id' => $from_chat_id,
            'message_id' => $message_id,
            'chat_id' => $chat_id,
        ]);
    }

    public function getVideoInfo(string $file_id): ?array {
        $file = $this->getFile($file_id);
        if (!$file || !isset($file['result']['file_path'])) return null;
        $fp = $file['result']['file_path'];
        return [
            'file_id' => $file_id,
            'file_unique_id' => $file['result']['file_unique_id'] ?? '',
            'file_path' => $fp,
            'file_size' => $file['result']['file_size'] ?? 0,
            'stream_url' => $this->getFileUrl($fp),
        ];
    }

    public function getUpdates(int $offset = 0, int $limit = 100, array $allowed_updates = []): ?array {
        $params = ['offset' => $offset, 'limit' => $limit];
        if (!empty($allowed_updates)) {
            $params['allowed_updates'] = $allowed_updates;
        }
        return $this->call('getUpdates', $params);
    }

    public function parseWebhookInput(): ?array {
        if ($this->webhook_data !== null) return $this->webhook_data;
        $input = file_get_contents('php://input');
        if ($input) {
            $this->webhook_data = json_decode($input, true);
            return $this->webhook_data;
        }
        if (isset($GLOBALS['telegram_update'])) {
            $this->webhook_data = $GLOBALS['telegram_update'];
            return $this->webhook_data;
        }
        return null;
    }

    public function getChatId(): ?string {
        $data = $this->parseWebhookInput();
        if (!$data) return null;
        return $data['message']['chat']['id']
            ?? $data['callback_query']['message']['chat']['id']
            ?? $data['my_chat_member']['chat']['id']
            ?? $data['channel_post']['chat']['id']
            ?? null;
    }

    public function getText(): ?string {
        $data = $this->parseWebhookInput();
        return $data['message']['text'] ?? $data['channel_post']['text'] ?? $data['channel_post']['caption'] ?? null;
    }

    public function notifySubscribers(string $message, string $photo_url = ''): int {
        $sent = 0;
        $subs = DB::fetchAll("SELECT chat_id FROM telegram_subscribers WHERE active = 1");
        foreach ($subs as $sub) {
            $result = $photo_url ? $this->sendPhoto($sub['chat_id'], $photo_url, $message) : $this->sendMessage($sub['chat_id'], $message);
            if ($result) $sent++;
        }
        return $sent;
    }
}
