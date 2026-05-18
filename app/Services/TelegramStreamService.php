<?php

namespace App\Services;

/**
 * Telegram Streaming Service
 *
 * Handles streaming of Telegram videos for files >20MB that cannot be downloaded
 * via the Telegram Bot API (which has a 20MB file size limit).
 *
 * Architecture:
 * - Uses Python 3 + Telethon (MTProto client) for streaming large files
 * - Communicates with Python script via proc_open() for each request
 * - Supports HTTP Range requests for seeking within videos
 *
 * Python Component:
 * - Location: storage/app/telegram/stream.py
 * - Usage:
 *   - `python stream.py info <message_id>` - Get file metadata as JSON
 *   - `python stream.py stream <message_id> <offset> <length>` - Stream byte range
 * - Session: Persisted at storage/app/telegram-session/telethon_session
 * - Libraries: telethon (installed via pip)
 *
 * Flow for Large Files (>20MB):
 * 1. Admin resolves t.me/aniwavebd/123 in episode form
 * 2. TelegramService.resolveMessage() calls TelegramStreamService.getMessageInfo()
 * 3. getMessageInfo() executes Python script: `python stream.py info 123`
 * 4. Python script uses Telethon to fetch message metadata from Telegram
 * 5. Form receives metadata, detects needs_streaming=true, shows large file notice
 * 6. Admin imports episode, Server.url is set to `/tg/123`
 * 7. Browser plays episode, Plyr requests `/tg/123`
 * 8. TgStreamController calls TelegramStreamService.streamMessage()
 * 9. streamMessage() parses HTTP Range header (if present for seeking)
 * 10. Executes Python script: `python stream.py stream 123 <offset> <length>`
 * 11. Python streams the requested byte range to browser via subprocess pipe
 *
 * Advantages:
 * - No PHP MTProto implementation needed (Python/Telethon handles protocol)
 * - No dependency on external APIs for large files
 * - Supports seeking via HTTP Range requests
 * - Session persisted across requests for fast reuse
 * - Memory efficient (streaming via pipes, not loading into memory)
 *
 * @package App\Services
 */

class TelegramStreamService
{
    /**
     * Get the path to the Python streamer script.
     */
    protected function streamerScript(): string
    {
        return storage_path('app/telegram/stream.py');
    }

    /**
     * Execute the Python streamer script with given arguments.
     * Returns the output or null on error.
     */
    protected function runStreamer(array $args): ?string
    {
        $script = $this->streamerScript();
        if (! file_exists($script)) {
            throw new \RuntimeException('Streamer script not found: ' . $script);
        }

        $cmd = [
            'python',
            escapeshellarg($script),
            ...array_map('escapeshellarg', $args),
        ];

        // Pass all environment variables to Python process
        $env = getenv();

        $process = proc_open(
            implode(' ', $cmd),
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            $env
        );

        if (! is_resource($process)) {
            throw new \RuntimeException('Failed to start streamer process');
        }

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Streamer failed: ' . $error);
        }

        return $output;
    }

    /**
     * Get message metadata (file size, mime type, etc).
     */
    public function getMessageInfo(int $messageId): ?array
    {
        try {
            $json = $this->runStreamer(['info', (string) $messageId]);
            $data = json_decode($json, true);

            if (isset($data['error'])) {
                return null;
            }

            return $data;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Stream a message's media to the current output buffer.
     * Handles HTTP Range requests for seeking.
     */
    public function streamMessage(int $messageId): void
    {
        try {
            set_time_limit(0);

            // Get message info first
            $info = $this->getMessageInfo($messageId);
            if (! $info) {
                http_response_code(404);
                echo json_encode(['error' => 'Message not found']);

                return;
            }

            $fileSize = $info['file_size'];
            $mimeType = $info['mime_type'] ?? 'video/mp4';

            // Parse Range header if present
            $start = 0;
            $end = $fileSize - 1;
            $statusCode = 200;

            if (isset($_SERVER['HTTP_RANGE'])) {
                if (preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
                    $start = intval($matches[1]);
                    $end = $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;
                    $statusCode = 206;
                }
            }

            $length = $end - $start + 1;

            // Set response headers
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . $length);
            header('Accept-Ranges: bytes');
            http_response_code($statusCode);

            if ($statusCode === 206) {
                header("Content-Range: bytes $start-$end/$fileSize");
            }

            // Clear output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Stream the file via Python subprocess
            $this->streamMediaRange($messageId, $start, $length);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Stream a byte range of media via subprocess, directly to output.
     */
    protected function streamMediaRange(int $messageId, int $offset, int $length): void
    {
        $script = $this->streamerScript();
        if (! file_exists($script)) {
            throw new \RuntimeException('Streamer script not found: ' . $script);
        }

        $cmd = [
            'python',
            escapeshellarg($script),
            'stream',
            (string) $messageId,
            (string) $offset,
            (string) $length,
        ];

        // Pass all environment variables to Python process
        $env = getenv();

        $process = proc_open(
            implode(' ', $cmd),
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            $env
        );

        if (! is_resource($process)) {
            throw new \RuntimeException('Failed to start streamer process');
        }

        // Stream output directly to PHP's output buffer
        while (! feof($pipes[1])) {
            $chunk = fread($pipes[1], 65536); // 64KB chunks
            if ($chunk === false) {
                break;
            }
            echo $chunk;
            flush();
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    }
}
