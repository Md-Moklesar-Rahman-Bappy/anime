<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamProxyController extends Controller
{
    protected array $allowedHosts = [
        'youtube.com',
        'googlevideo.com',
        'cdn.example.com',
    ];

    protected array $mimeMap = [
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'm3u8' => 'application/x-mpegURL',
        'ts'   => 'video/mp2t',
    ];

    public function stream(Request $request)
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Decode URL
            |--------------------------------------------------------------------------
            */
            $encoded = $request->query('url');

            if (!$encoded) {
                abort(400, 'Missing URL');
            }

            $url = base64_decode($encoded, true);

            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                abort(400, 'Invalid URL');
            }

            /*
            |--------------------------------------------------------------------------
            | Host Validation (SECURITY)
            |--------------------------------------------------------------------------
            */
            $host = parse_url($url, PHP_URL_HOST);

            if (!$this->isAllowedHost($host)) {
                abort(403, 'Unauthorized host');
            }

            /*
            |--------------------------------------------------------------------------
            | Determine Content Type
            |--------------------------------------------------------------------------
            */
            $path = parse_url($url, PHP_URL_PATH);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            $contentType = $this->mimeMap[$ext] ?? 'application/octet-stream';

            /*
            |--------------------------------------------------------------------------
            | Get Remote Headers
            |--------------------------------------------------------------------------
            */
            $headers = $this->getHeaders($url);

            if (!$headers) {
                abort(502, 'Upstream unreachable');
            }

            $fileSize = (int) ($headers['Content-Length'] ?? 0);

            /*
            |--------------------------------------------------------------------------
            | Range Handling
            |--------------------------------------------------------------------------
            */
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

            /*
            |--------------------------------------------------------------------------
            | Stream Response
            |--------------------------------------------------------------------------
            */
            return new StreamedResponse(function () use ($url, $start, $end) {

                $context = stream_context_create([
                    'http' => [
                        'method'  => 'GET',
                        'header'  => "Range: bytes={$start}-{$end}\r\n",
                        'timeout' => 30,
                    ],
                ]);

                $stream = @fopen($url, 'rb', false, $context);

                if (!$stream) {
                    return;
                }

                while (!feof($stream)) {
                    echo fread($stream, 8192); // ✅ smaller chunks = more stable
                    flush();
                }

                fclose($stream);
            }, $status, [
                'Content-Type' => $contentType,
                'Accept-Ranges' => 'bytes',
                'Access-Control-Allow-Origin' => '*',

                // ✅ Important for video players
                'Cache-Control' => 'no-cache',
            ]);
        } catch (\Throwable $e) {

            $this->logError('Stream proxy failed', $e, [
                'url' => $request->query('url'),
            ]);

            abort(500, 'Streaming failed');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HEADERS FETCH
    |--------------------------------------------------------------------------
    */

    protected function getHeaders(string $url): ?array
    {
        try {
            return @get_headers($url, true);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HOST VALIDATION
    |--------------------------------------------------------------------------
    */

    protected function isAllowedHost(?string $host): bool
    {
        if (!$host) return false;

        foreach ($this->allowedHosts as $allowed) {
            if (str_ends_with($host, $allowed)) {
                return true;
            }
        }

        return false;
    }
}
