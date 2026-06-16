<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamProxyController extends Controller
{
    protected array $allowedHosts = [
        'youtube.com',
        'googlevideo.com',
        'cdn.example.com', // ✅ your CDN here
    ];

    protected array $mimeMap = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'm3u8' => 'application/x-mpegURL',
        'ts' => 'video/mp2t',
    ];

    public function stream(Request $request)
    {
        try {
            $encoded = $request->query('url');

            if (!$encoded) {
                abort(400, 'Missing URL');
            }

            $url = base64_decode($encoded, true);

            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                abort(400, 'Invalid URL');
            }

            // ✅ SECURITY: restrict domains
            $host = parse_url($url, PHP_URL_HOST);

            if (!$this->isAllowedHost($host)) {
                abort(403, 'Unauthorized host');
            }

            set_time_limit(0);

            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            $contentType = $this->mimeMap[$ext] ?? 'application/octet-stream';

            $headers = $this->getHeaders($url);

            if (!$headers) {
                abort(502, 'Upstream unreachable');
            }

            $fileSize = (int) ($headers['Content-Length'] ?? 0);

            $start = 0;
            $end = $fileSize > 0 ? $fileSize - 1 : 0;
            $status = 200;

            if ($request->header('Range') && $fileSize > 0) {
                if (preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $m)) {
                    $start = (int) $m[1];
                    $end = $m[2] !== '' ? (int) $m[2] : $fileSize - 1;
                    $status = 206;
                }
            }

            return new StreamedResponse(function () use ($url, $start, $end) {

                $ctx = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => "Range: bytes={$start}-{$end}\r\n",
                        'timeout' => 60,
                    ],
                ]);

                $stream = @fopen($url, 'rb', false, $ctx);

                if (!$stream) {
                    return;
                }

                while (!feof($stream)) {
                    echo fread($stream, 65536);
                    flush();
                }

                fclose($stream);

            }, $status, [
                'Content-Type' => $contentType,
                'Accept-Ranges' => 'bytes',
                'Access-Control-Allow-Origin' => '*',
            ]);

        } catch (\Throwable $e) {

            Log::error('Stream proxy failed', [
                'url' => $request->query('url'),
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Streaming failed');
        }
    }

    protected function getHeaders(string $url): ?array
    {
        try {
            return @get_headers($url, true);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function isAllowedHost(?string $host): bool
    {
        if (!$host) return false;

        foreach ($this->allowedHosts as $allowed) {
            if (str_contains($host, $allowed)) {
                return true;
            }
        }

        return false;
    }
}