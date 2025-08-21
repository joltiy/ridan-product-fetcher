<?php

namespace Joltiy\RidanProductFetcher\Tests\Mocks;

use Joltiy\RidanProductFetcher\HttpClient\HttpClientInterface;
use Joltiy\RidanProductFetcher\HttpClient\Exceptions\HttpException;

class MockHttpClient implements HttpClientInterface
{
    private array $responses = [];
    private array $exceptions = [];

    public function setResponse(string $url, string $response): void
    {
        $this->responses[$url] = $response;
    }

    public function throwExceptionOnUrl(string $url, HttpException $exception): void
    {
        $this->exceptions[$url] = $exception;
    }

    public function get(string $url): string
    {
        if (isset($this->exceptions[$url])) {
            throw $this->exceptions[$url];
        }

        if (!isset($this->responses[$url])) {
            throw new HttpException("No mock response set for URL: {$url}", 404);
        }

        return $this->responses[$url];
    }

    public function post(string $url, array $data = []): string
    {
        return $this->get($url);
    }

    public function setOptions(array $options): void
    {
        // Mock implementation
    }
}
