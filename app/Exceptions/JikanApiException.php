<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;

class JikanApiException extends Exception
{
    public ?int $statusCode;

    public ?string $responseBody;

    public function __construct(
        string $message = 'Jikan API error',
        ?int $statusCode = null,
        ?string $responseBody = null,
        Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);

        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    /*
    |--------------------------------------------------------------------------
    | Factory Methods
    |--------------------------------------------------------------------------
    */

    public static function connectionFailed(string $detail): self
    {
        return new self(
            "Failed to connect to Jikan API: {$detail}"
        );
    }

    public static function rateLimited(int $retryAfter): self
    {
        return new self(
            "Rate limited by Jikan API. Retry after {$retryAfter}s.",
            429
        );
    }

    public static function badResponse(int $status, string $body): self
    {
        return new self(
            "Jikan API returned HTTP {$status}",
            $status,
            $body
        );
    }

    public static function notFound(): self
    {
        return new self(
            'Resource not found on MyAnimeList.',
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isRateLimited(): bool
    {
        return $this->statusCode === 429;
    }

    public function isNotFound(): bool
    {
        return $this->statusCode === 404;
    }

    /*
    |--------------------------------------------------------------------------
    | Report (Laravel logging)
    |--------------------------------------------------------------------------
    */

    public function report(): void
    {
        Log::error('Jikan API Exception', [
            'message' => $this->getMessage(),
            'status' => $this->statusCode,
            'response' => $this->responseBody,
        ]);
    }
}
