<?php

namespace Joltiy\RidanProductFetcher\HttpClient;

use Joltiy\RidanProductFetcher\HttpClient\Exceptions\HttpException;

class CurlHttpClient implements HttpClientInterface
{
    private int $timeout = 30;
    private string $userAgent = 'RidanProductFetcher/1.0';
    private bool $verifySSL = true;

    /**
     * @param array<string, int|bool|string> $options Опции конфигурации клиента
     */
    public function __construct(array $options = [])
    {
        // Простая установка опций без сложного мерджа
        if (isset($options['timeout'])) {
            $this->timeout = (int) $options['timeout'];
        }
        if (isset($options['user_agent'])) {
            $this->userAgent = is_string($options['user_agent'])
                ? $options['user_agent']
                : (string) $options['user_agent'];
        }
        if (isset($options['verify_ssl'])) {
            $this->verifySSL = (bool) $options['verify_ssl'];
        }
    }

    public function get(string $url): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySSL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySSL ? 2 : 0);

        $response = curl_exec($ch);

        if (curl_errno($ch) || $response === false || $response === true) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw HttpException::fromCurlError($errno, $error, $url);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw HttpException::fromHttpError($httpCode, $url);
        }

        return $response;
    }

    public function post(string $url, array $data = []): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySSL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySSL ? 2 : 0);

        $response = curl_exec($ch);

        if (curl_errno($ch) || $response === false || $response === true) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw HttpException::fromCurlError($errno, $error, $url);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw HttpException::fromHttpError($httpCode, $url);
        }

        return $response;
    }

    public function setOptions(array $options): void
    {
        // Простая реализация для этого метода
        if (isset($options['timeout'])) {
            $this->timeout = (int) $options['timeout'];
        }
        if (isset($options['user_agent'])) {
            $this->userAgent = $options['user_agent'];
        }
        if (isset($options['verify_ssl'])) {
            $this->verifySSL = (bool) $options['verify_ssl'];
        }
    }
}
