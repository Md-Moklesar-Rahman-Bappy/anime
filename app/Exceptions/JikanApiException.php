<?php

namespace App\Exceptions;

use Exception;

class JikanApiException extends Exception
{
    public function __construct(
        string $message = 'Jikan API error',
        public ?int $statusCode = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function connectionFailed(string $detail): self
    {
        return new self("Failed to connect to Jikan API: {$detail}");
    }

    public static function rateLimited(int $retryAfter): self
    {
        return new self("Rate limited by Jikan API. Retry after {$retryAfter}s.", 429);
    }

    public static function badResponse(int $status, string $body): self
    {
        return new self("Jikan API returned HTTP {$status}: {$body}", $status);
    }

    public static function notFound(): self
    {
        return new self('Resource not found on MyAnimeList.', 404);
    }
}
