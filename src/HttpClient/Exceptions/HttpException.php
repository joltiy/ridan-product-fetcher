<?php

namespace Joltiy\RidanProductFetcher\HttpClient\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    private ?int $statusCode;

    public function __construct(string $message, ?int $statusCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public static function fromCurlError(int $errno, string $error, string $url): self
    {
        return new self(sprintf('cURL error (%d) for URL %s: %s', $errno, $url, $error));
    }

    public static function fromHttpError(int $statusCode, string $url): self
    {
        return new self(sprintf('HTTP error %d for URL: %s', $statusCode, $url), $statusCode);
    }
}
