<?php

namespace Joltiy\RidanProductFetcher\Tests\Integration;

use Joltiy\RidanProductFetcher\HttpClient\CurlHttpClient;
use Joltiy\RidanProductFetcher\HttpClient\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 * @covers \Joltiy\RidanProductFetcher\HttpClient\CurlHttpClient
 */
class HttpClientIntegrationTest extends TestCase
{
    public function testRealHttpGetRequest()
    {
        $this->markTestSkipped(
            'Интеграционные тесты требуют реальных HTTP запросов. Раскомментируйте для ручного тестирования.'
        );

        /*
        $client = new CurlHttpClient();

        $response = $client->get('https://httpbin.org/user-agent');

        $this->assertIsString($response);
        $this->assertNotEmpty($response);
        $this->assertStringContainsString('user-agent', $response);
        */
    }

    public function testRealHttpPostRequest()
    {
        $this->markTestSkipped(
            'Интеграционные тесты требуют реальных HTTP запросов. Раскомментируйте для ручного тестирования.'
        );

        /*
        $client = new CurlHttpClient();

        $response = $client->post('https://httpbin.org/post', ['test' => 'data']);

        $this->assertIsString($response);
        $this->assertNotEmpty($response);
        $this->assertStringContainsString('test', $response);
        */
    }

    public function testRealHttpError()
    {
        $this->markTestSkipped(
            'Интеграционные тесты требуют реальных HTTP запросов. Раскомментируйте для ручного тестирования.'
        );

        /*
        $this->expectException(HttpException::class);

        $client = new CurlHttpClient();
        $client->get('https://httpbin.org/status/404');
        */
    }
}
