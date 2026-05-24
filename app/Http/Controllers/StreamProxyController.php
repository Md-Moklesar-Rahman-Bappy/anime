<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    public function stream(Request $request)
    {
        $encoded = $request->query('url');
        if (!$encoded) {
            abort(400, 'Missing url parameter');
        }

        $url = base64_decode($encoded, true);
        if ($url === false || !str_starts_with($url, 'http')) {
            abort(400, 'Invalid url parameter');
        }

        set_time_limit(0);

        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $contentType = $this->mimeMap[$ext] ?? 'application/octet-stream';

        $headers = get_headers($url, true);
        if (!$headers || !isset($headers[0])) {
            abort(502, 'Could not reach upstream server');
        }

        $statusParts = explode(' ', $headers[0]);
        $upstreamCode = (int) ($statusParts[1] ?? 500);
        if ($upstreamCode >= 400) {
            abort(502, 'Upstream returned ' . $upstreamCode);
        }

        $upstreamContentType = $headers['Content-Type'] ?? null;
        if (is_array($upstreamContentType)) {
            $upstreamContentType = end($upstreamContentType);
        }
        if ($upstreamContentType && $upstreamContentType !== 'application/octet-stream') {
            $contentType = $upstreamContentType;
        }

        $fileSize = $headers['Content-Length'] ?? 0;
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
                $statusCode = 206;
            }
        }

        $length = $fileSize > 0 ? $end - $start + 1 : 0;

        header('Content-Type: ' . $contentType);
        header('Access-Control-Allow-Origin: *');
        header('Accept-Ranges: bytes');
        header('Cache-Control: no-cache, must-revalidate');

        if ($fileSize > 0) {
            header('Content-Length: ' . $length);
        }

        http_response_code($statusCode);

        if ($statusCode === 206 && $fileSize > 0) {
            header("Content-Range: bytes $start-$end/$fileSize");
        }

        if ($request->method() === 'HEAD') {
            return;
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Range: bytes=$start-$end\r\n",
                'timeout' => 3600,
            ],
        ]);

        $remote = @fopen($url, 'rb', false, $ctx);
        if (!$remote) {
            http_response_code(502);
            echo 'Failed to connect to upstream server';
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
    }
}
