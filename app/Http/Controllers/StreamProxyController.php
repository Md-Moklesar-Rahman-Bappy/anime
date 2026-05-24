<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamProxyController extends Controller
{
    protected array $mimeMap = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'mkv' => 'video/x-matroska',
        'm3u8' => 'application/x-mpegURL',
        'ts' => 'video/mp2t',
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime',
        'wmv' => 'video/x-ms-wmv',
        'flv' => 'video/x-flv',
        'ogv' => 'video/ogg',
        'mp3' => 'audio/mpeg',
        'aac' => 'audio/aac',
        'ogg' => 'audio/ogg',
        'wav' => 'audio/wav',
        'm4a' => 'audio/mp4',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    protected array $blockedHosts = [
        'localhost', '127.0.0.1', '::1', '0.0.0.0',
        '10.', '172.16.', '172.17.', '172.18.', '172.19.',
        '172.20.', '172.21.', '172.22.', '172.23.', '172.24.',
        '172.25.', '172.26.', '172.27.', '172.28.', '172.29.',
        '172.30.', '172.31.', '192.168.',
    ];

    public function stream(Request $request)
    {
        $encoded = $request->query('url');
        if (!$encoded) {
            abort(400, 'Missing url parameter');
        }

        $url = base64_decode($encoded, true);
        if ($url === false || (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://'))) {
            abort(400, 'Invalid url parameter');
        }

        $host = parse_url($url, PHP_URL_HOST);
        if ($this->isBlockedHost($host)) {
            abort(403, 'Access to this resource is denied');
        }

        set_time_limit(0);

        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $contentType = $this->mimeMap[$ext] ?? 'application/octet-stream';

        $responseHeaders = $this->getHeaders($url);
        if (!$responseHeaders) {
            abort(502, 'Could not reach upstream server');
        }

        $upstreamCode = (int) explode(' ', $responseHeaders[0])[1] ?? 500;
        if ($upstreamCode >= 400) {
            abort(502, 'Upstream returned ' . $upstreamCode);
        }

        $upstreamContentType = $responseHeaders['Content-Type'] ?? null;
        if (is_array($upstreamContentType)) {
            $upstreamContentType = end($upstreamContentType);
        }
        if ($upstreamContentType && $upstreamContentType !== 'application/octet-stream') {
            $contentType = $upstreamContentType;
        }

        $fileSize = $responseHeaders['Content-Length'] ?? 0;
        if (is_array($fileSize)) {
            $fileSize = end($fileSize);
        }
        $fileSize = (int) $fileSize;

        $start = 0;
        $end = $fileSize > 0 ? $fileSize - 1 : 0;
        $statusCode = 200;

        if ($request->header('Range') && $fileSize > 0) {
            $range = $request->header('Range');
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = (int) $matches[1];
                $end = $matches[2] !== '' ? (int) $matches[2] : $fileSize - 1;
                $start = min($start, $fileSize - 1);
                $end = min($end, $fileSize - 1);
                if ($start <= $end) {
                    $statusCode = 206;
                }
            }
        }

        $length = $fileSize > 0 ? $end - $start + 1 : 0;

        $headers = [
            'Content-Type' => $contentType,
            'Access-Control-Allow-Origin' => '*',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, must-revalidate',
        ];

        if ($fileSize > 0) {
            $headers['Content-Length'] = $length;
        }

        if ($statusCode === 206 && $fileSize > 0) {
            $headers['Content-Range'] = "bytes $start-$end/$fileSize";
        }

        return new StreamedResponse(function () use ($url, $start, $end, $length, $fileSize) {
            $ctx = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Range: bytes=$start-$end\r\n",
                    'timeout' => 3600,
                ],
            ]);

            $remote = @fopen($url, 'rb', false, $ctx);
            if (!$remote) {
                return;
            }

            $bytesRemaining = $length > 0 ? $length : PHP_INT_MAX;
            while ($bytesRemaining > 0 && !feof($remote)) {
                $chunkSize = min(65536, $bytesRemaining);
                $chunk = fread($remote, $chunkSize);
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
                flush();
                $bytesRemaining -= strlen($chunk);
            }

            fclose($remote);
        }, $statusCode, $headers);
    }

    protected function getHeaders(string $url): ?array
    {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 15,
            ],
        ]);

        $headers = @get_headers($url, true, $ctx);
        return $headers ?: null;
    }

    protected function isBlockedHost(string $host): bool
    {
        foreach ($this->blockedHosts as $blocked) {
            if (str_starts_with($host, $blocked)) {
                return true;
            }
        }
        return false;
    }
}
