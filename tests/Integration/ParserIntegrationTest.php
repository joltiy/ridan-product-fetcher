<?php

namespace Joltiy\RidanProductFetcher\Tests\Integration;

use Joltiy\RidanProductFetcher\Parser\RidanParser;
use Joltiy\RidanProductFetcher\HttpClient\CurlHttpClient;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 * @covers \Joltiy\RidanProductFetcher\Parser\RidanParser
 */
class ParserIntegrationTest extends TestCase
{
    public function testRealApiCallWithValidProduct()
    {
        $this->markTestSkipped(
            'Интеграционные тесты требуют реальных HTTP запросов. Раскомментируйте для ручного тестирования.'
        );

        /*
        $httpClient = new CurlHttpClient();
        $parser = new RidanParser($httpClient);

        // Используйте реальный артикул для тестирования
        $product = $parser->parseProduct('060-117766R');

        $this->assertNotEmpty($product->getArticle());
        $this->assertIsArray($product->getFiles());
        $this->assertIsArray($product->getImages());
        */
    }

    public function testRealApiCallWithInvalidProduct()
    {
        $this->markTestSkipped(
            'Интеграционные тесты требуют реальных HTTP запросов. Раскомментируйте для ручного тестирования.'
        );

        /*
        $this->expectException(\Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException::class);

        $httpClient = new CurlHttpClient();
        $parser = new RidanParser($httpClient);

        // Несуществующий артикул
        $parser->parseProduct('invalid-article-12345');
        */
    }
}
