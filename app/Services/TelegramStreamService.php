<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TelegramStreamService
{
    protected function streamerScript(): string
    {
        return storage_path('app/telegram/stream.py');
    }

    protected function runStreamer(array $args): ?string
    {
        $script = $this->streamerScript();
        if (! file_exists($script)) {
            throw new \RuntimeException('Streamer script not found: '.$script);
        }

        $cmd = [
            'python',
            escapeshellarg($script),
            ...array_map('escapeshellarg', $args),
        ];

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
            throw new \RuntimeException('Streamer failed: '.$error);
        }

        return $output;
    }

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

    public function streamMessage(int $messageId, Request $request): StreamedResponse
    {
        $info = $this->getMessageInfo($messageId);
        if (! $info) {
            abort(404, 'Message not found');
        }

        $fileSize = $info['file_size'];
        $mimeType = $info['mime_type'] ?? 'video/mp4';

        $start = 0;
        $end = $fileSize - 1;
        $statusCode = 200;

        if ($request->header('Range')) {
            if (preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches)) {
                $start = intval($matches[1]);
                $end = $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;
                $start = min($start, $fileSize - 1);
                $end = min($end, $fileSize - 1);
                if ($start <= $end) {
                    $statusCode = 206;
                }
            }
        }

        $length = $end - $start + 1;

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => $length,
            'Accept-Ranges' => 'bytes',
        ];

        if ($statusCode === 206) {
            $headers['Content-Range'] = "bytes $start-$end/$fileSize";
        }

        return new StreamedResponse(function () use ($messageId, $start, $length) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            $this->streamMediaRange($messageId, $start, $length);
        }, $statusCode, $headers);
    }

    protected function streamMediaRange(int $messageId, int $offset, int $length): void
    {
        $script = $this->streamerScript();
        if (! file_exists($script)) {
            throw new \RuntimeException('Streamer script not found: '.$script);
        }

        $cmd = [
            'python',
            escapeshellarg($script),
            'stream',
            (string) $messageId,
            (string) $offset,
            (string) $length,
        ];

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

        while (! feof($pipes[1])) {
            $chunk = fread($pipes[1], 65536);
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
