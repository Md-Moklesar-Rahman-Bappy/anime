<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TelegramStreamService
{
    /*
    |--------------------------------------------------------------------------
    | STREAMER SCRIPT
    |--------------------------------------------------------------------------
    */
    protected function streamerScript(): string
    {
        return storage_path('app/telegram/stream.py');
    }

    /*
    |--------------------------------------------------------------------------
    | RUN STREAMER (SAFE)
    |--------------------------------------------------------------------------
    */
    protected function runStreamer(array $args): ?string
    {
        $script = $this->streamerScript();

        if (!file_exists($script)) {
            throw new \RuntimeException("Streamer script not found: {$script}");
        }

        $command = array_merge(['python', $script], $args);

        $process = proc_open(
            $command,
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start streamer process');
        }

        stream_set_blocking($pipes[1], false);

        $output = '';
        $start = time();
        $timeout = 15;

        try {
            while (true) {

                $status = proc_get_status($process);

                if (!$status['running']) {
                    break;
                }

                if ((time() - $start) > $timeout) {
                    proc_terminate($process);
                    throw new \RuntimeException('Streamer timeout');
                }

                $output .= fread($pipes[1], 8192);

                usleep(10000);
            }

            $output .= stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);

            if (!empty($error)) {
                logger()->warning('Streamer stderr output', ['error' => $error]);
            }
        } finally {
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }

        return $output ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | GET MESSAGE INFO
    |--------------------------------------------------------------------------
    */
    public function getMessageInfo(int $messageId): ?array
    {
        try {
            $json = $this->runStreamer(['info', (string) $messageId]);

            return $json ? json_decode($json, true) : null;
        } catch (\Throwable $e) {
            logger()->warning('Stream info failed', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STREAM MESSAGE
    |--------------------------------------------------------------------------
    */
    public function streamMessage(int $messageId, Request $request): StreamedResponse
    {
        $info = $this->getMessageInfo($messageId);

        if (!$info || empty($info['file_size'])) {
            abort(404, 'Message not found');
        }

        $fileSize = (int) $info['file_size'];
        $mimeType = $info['mime_type'] ?? 'video/mp4';

        [$start, $end, $status] = $this->parseRange($request, $fileSize);

        $length = $end - $start + 1;

        return new StreamedResponse(
            function () use ($messageId, $start, $length) {

                ignore_user_abort(true);

                $this->streamMediaRange($messageId, $start, $length);
            },
            $status,
            $this->buildHeaders($mimeType, $start, $end, $fileSize, $length, $status)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STREAM PARTIAL MEDIA
    |--------------------------------------------------------------------------
    */
    protected function streamMediaRange(int $messageId, int $offset, int $length): void
    {
        $script = $this->streamerScript();

        $command = [
            'python',
            $script,
            'stream',
            (string) $messageId,
            (string) $offset,
            (string) $length,
        ];

        $process = proc_open(
            $command,
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            return;
        }

        stream_set_blocking($pipes[1], false);

        try {
            while (true) {

                if (connection_aborted()) {
                    proc_terminate($process);
                    break;
                }

                $status = proc_get_status($process);

                if (!$status['running']) {
                    break;
                }

                echo fread($pipes[1], 65536);

                if (ob_get_level() > 0) {
                    ob_flush();
                }

                flush();

                usleep(10000);
            }
        } finally {
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PARSE RANGE HEADER (SAFE)
    |--------------------------------------------------------------------------
    */
    protected function parseRange(Request $request, int $fileSize): array
    {
        $start = 0;
        $end = $fileSize - 1;
        $status = 200;

        $range = $request->header('Range');

        if ($range && preg_match('/bytes=(\d+)-(\d*)/', $range, $m)) {

            $start = (int) $m[1];
            $end = $m[2] !== '' ? (int) $m[2] : $end;

            if ($start > $end || $start >= $fileSize) {
                abort(416, 'Requested Range Not Satisfiable');
            }

            $end = min($end, $fileSize - 1);
            $status = 206;
        }

        return [$start, $end, $status];
    }

    /*
    |--------------------------------------------------------------------------
    | BUILD HEADERS
    |--------------------------------------------------------------------------
    */
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
