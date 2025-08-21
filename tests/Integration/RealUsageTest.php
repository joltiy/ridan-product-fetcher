<?php

namespace Joltiy\RidanProductFetcher\Tests\Integration;

use Joltiy\RidanProductFetcher\Parser\RidanParser;
use Joltiy\RidanProductFetcher\HttpClient\CurlHttpClient;
use Joltiy\RidanProductFetcher\Models\Product;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 * @covers \Joltiy\RidanProductFetcher\Parser\RidanParser
 * @covers \Joltiy\RidanProductFetcher\HttpClient\CurlHttpClient
 * @covers \Joltiy\RidanProductFetcher\Models\Product
 */
class RealUsageTest extends TestCase
{
    public function testRealProductParsing()
    {
        $this->markTestSkipped('Интеграционный тест с реальными запросами. Раскомментируйте для ручного тестирования.');

        /*
        $httpClient = new CurlHttpClient();
        $parser = new RidanParser($httpClient);

        // Тестируем с реальным артикулом
        $product = $parser->parseProduct('060-117766R');

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('060-117766R', $product->getArticle());
        $this->assertIsArray($product->getFiles());
        $this->assertIsArray($product->getImages());
        $this->assertIsArray($product->getSpecification());
        $this->assertIsArray($product->getMainSpecification());

        // Проверяем что есть хотя бы какие-то данные
        $this->assertTrue(
            $product->hasDocuments() ||
            $product->hasImages() ||
            !empty($product->getSpecification()) ||
            !empty($product->getMainSpecification()),
            'Product should have at least some data'
        );
        */
    }

    public function testProductToArrayConversion()
    {
        $httpClient = new CurlHttpClient();
        $parser = new RidanParser($httpClient);

        // Создаем mock продукт для тестирования преобразования
        $product = new Product(
            'test-article',
            [['url1', 'name1'], ['url2', 'name2']],
            ['image1.jpg'],
            ['spec' => 'value'],
            ['main_spec' => 'main_value']
        );

        $array = $product->toArray();

        $this->assertEquals('test-article', $array['article']);
        $this->assertCount(2, $array['files']);
        $this->assertCount(1, $array['images']);
        $this->assertCount(1, $array['specification']);
        $this->assertCount(1, $array['main_specification']);

        // Проверяем структуру файлов
        $this->assertEquals('url1', $array['files'][0][0]);
        $this->assertEquals('name1', $array['files'][0][1]);
    }
}
