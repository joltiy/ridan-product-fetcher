<?php

namespace Joltiy\RidanProductFetcher\HttpClient;

use Joltiy\RidanProductFetcher\HttpClient\Exceptions\HttpException;

interface HttpClientInterface
{
    /**
     * Send a GET request
     *
     * @param string $url The URL to request
     * @return string The response content
     * @throws HttpException
     */
    public function get(string $url): string;

    /**
     * Send a POST request
     *
     * @param string $url The URL to request
     * @param array<string, string|int|float> $data The POST data
     * @return string The response content
     * @throws HttpException
     */
    public function post(string $url, array $data = []): string;

    /**
     * Set cURL options
     *
     * @param array<string, mixed> $options Array of cURL options
     */
    public function setOptions(array $options): void;
}
