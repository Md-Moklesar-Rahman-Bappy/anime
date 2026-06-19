<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class JikanApiException extends Exception
{
    /**
     * HTTP status code from Jikan API
     */
    public ?int $statusCode;

    /**
     * Raw API response body
     */
    public ?string $responseBody;

    /**
     * Constructor
     */
    public function __construct(
        string $message = 'Jikan API error',
        ?int $statusCode = null,
        ?string $responseBody = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);

        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    /*
    |--------------------------------------------------------------------------
    | FACTORY METHODS
    |--------------------------------------------------------------------------
    */

    public static function connectionFailed(string $detail): self
    {
        return new self(
            "Failed to connect to Jikan API: {$detail}"
        );
    }

    public static function rateLimited(int $retryAfter = 0): self
    {
        return new self(
            'Jikan API rate limit reached'
                . ($retryAfter > 0 ? " (retry after {$retryAfter}s)" : ''),
            429
        );
    }

    public static function badResponse(int $status, string $body = ''): self
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
            'Resource not found on MyAnimeList',
            404
        );
    }

    public static function invalidData(string $detail = ''): self
    {
        return new self(
            'Invalid or unexpected data from Jikan API'
                . ($detail ? " ({$detail})" : '')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
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

    public function isServerError(): bool
    {
        return $this->statusCode !== null
            && $this->statusCode >= 500;
    }

    /*
    |--------------------------------------------------------------------------
    | REPORT (Laravel integration)
    |--------------------------------------------------------------------------
    */

    public function report(): void
    {
        logger()->error('Jikan API Exception', [
            'message' => $this->getMessage(),
            'status_code' => $this->statusCode,
            'response' => $this->responseBody,
        ]);
    }
}
