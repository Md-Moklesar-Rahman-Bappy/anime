<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class TelegramStreamService
{
    protected function streamerScript(): string
    {
        return storage_path('app/telegram/stream.py');
    }

    protected function runStreamer(array $args): ?string
    {
        $script = $this->streamerScript();

        if (!file_exists($script)) {
            throw new \RuntimeException("Streamer script not found: {$script}");
        }

        $cmd = [
            'python',
            escapeshellarg($script),
            ...array_map('escapeshellarg', $args),
        ];

        $process = proc_open(
            implode(' ', $cmd),
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start streamer process');
        }

        // ✅ timeout protection
        $start = time();
        $timeout = 15;

        $output = '';
        while (!feof($pipes[1])) {

            if (time() - $start > $timeout) {
                proc_terminate($process);
                throw new \RuntimeException('Streamer timeout');
            }

            $output .= fread($pipes[1], 8192);
        }

        $error = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            Log::error('Streamer error', ['error' => $error]);
            throw new \RuntimeException("Streamer failed: {$error}");
        }

        return $output;
    }

    public function getMessageInfo(int $messageId): ?array
    {
        try {
            $json = $this->runStreamer(['info', (string)$messageId]);

            return json_decode($json, true);
        } catch (\Throwable $e) {
            Log::warning('Stream info failed', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function streamMessage(int $messageId, Request $request): StreamedResponse
    {
        $info = $this->getMessageInfo($messageId);

        if (!$info || empty($info['file_size'])) {
            abort(404, 'Message not found');
        }

        $fileSize = (int)$info['file_size'];
        $mimeType = $info['mime_type'] ?? 'video/mp4';

        [$start, $end, $status] = $this->parseRange($request, $fileSize);

        $length = $end - $start + 1;

        return new StreamedResponse(
            function () use ($messageId, $start, $length) {

                // ✅ client disconnect detection
                ignore_user_abort(true);

                $this->streamMediaRange($messageId, $start, $length);

            },
            $status,
            $this->buildHeaders($mimeType, $start, $end, $fileSize, $length, $status)
        );
    }

    protected function streamMediaRange(int $messageId, int $offset, int $length): void
    {
        $script = $this->streamerScript();

        $cmd = [
            'python',
            escapeshellarg($script),
            'stream',
            (string)$messageId,
            (string)$offset,
            (string)$length,
        ];

        $process = proc_open(
            implode(' ', $cmd),
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            return;
        }

        while (!feof($pipes[1])) {

            if (connection_aborted()) {
                proc_terminate($process);
                break;
            }

            echo fread($pipes[1], 65536);
            flush();
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function parseRange(Request $request, int $fileSize): array
    {
        $start = 0;
        $end = $fileSize - 1;
        $status = 200;

        if ($range = $request->header('Range')) {
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $m)) {
                $start = (int)$m[1];
                $end = $m[2] !== '' ? (int)$m[2] : $fileSize - 1;

                $start = min($start, $fileSize - 1);
                $end = min($end, $fileSize - 1);

                if ($start <= $end) {
                    $status = 206;
                }
            }
        }

        return [$start, $end, $status];
    }

    protected function buildHeaders(
        string $mime,
        int $start,
        int $end,
        int $fileSize,
        int $length,
        int $status
    ): array {
        $headers = [
            'Content-Type' => $mime,
            'Content-Length' => $length,
            'Accept-Ranges' => 'bytes',
        ];

        if ($status === 206) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        return $headers;
    }
}
