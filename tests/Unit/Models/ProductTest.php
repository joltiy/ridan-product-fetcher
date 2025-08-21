<?php

namespace Joltiy\RidanProductFetcher\Tests\Unit\Models;

use Joltiy\RidanProductFetcher\Models\Product;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Joltiy\RidanProductFetcher\Models\Product
 */
class ProductTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $article = '060-117766R';
        $files = [
            ['https://example.com/doc1.pdf', 'Document 1'],
            ['https://example.com/doc2.pdf', 'Document 2']
        ];
        $images = ['https://example.com/image1.jpg', 'https://example.com/image2.jpg'];
        $specification = ['param1' => 'value1', 'param2' => 'value2'];
        $mainSpecification = ['main_param' => 'main_value'];

        $product = new Product($article, $files, $images, $specification, $mainSpecification);

        $this->assertEquals($article, $product->getArticle());
        $this->assertEquals($files, $product->getFiles());
        $this->assertEquals($images, $product->getImages());
        $this->assertEquals($specification, $product->getSpecification());
        $this->assertEquals($mainSpecification, $product->getMainSpecification());
    }

    public function testConstructorWithDefaultValues()
    {
        $article = '060-117766R';
        $product = new Product($article);

        $this->assertEquals($article, $product->getArticle());
        $this->assertEmpty($product->getFiles());
        $this->assertEmpty($product->getImages());
        $this->assertEmpty($product->getSpecification());
        $this->assertEmpty($product->getMainSpecification());
    }

    public function testFromArray()
    {
        $data = [
            'article' => '060-117766R',
            'files' => [
                ['https://example.com/doc1.pdf', 'Document 1'],
                ['https://example.com/doc2.pdf', 'Document 2']
            ],
            'images' => ['image1.jpg', 'image2.jpg'],
            'specification' => ['param1' => 'value1'],
            'main_specification' => ['main_param' => 'main_value']
        ];

        $product = Product::fromArray($data);

        $this->assertEquals($data['article'], $product->getArticle());
        $this->assertEquals($data['files'], $product->getFiles());
        $this->assertEquals($data['images'], $product->getImages());
        $this->assertEquals($data['specification'], $product->getSpecification());
        $this->assertEquals($data['main_specification'], $product->getMainSpecification());
    }

    public function testFromArrayWithMissingKeys()
    {
        $data = [
            'article' => '060-117766R'
            // остальные ключи отсутствуют
        ];

        $product = Product::fromArray($data);

        $this->assertEquals($data['article'], $product->getArticle());
        $this->assertEmpty($product->getFiles());
        $this->assertEmpty($product->getImages());
        $this->assertEmpty($product->getSpecification());
        $this->assertEmpty($product->getMainSpecification());
    }

    public function testToArray()
    {
        $article = '060-117766R';
        $files = [
            ['https://example.com/doc1.pdf', 'Document 1'],
            ['https://example.com/doc2.pdf', 'Document 2']
        ];
        $images = ['image1.jpg', 'image2.jpg'];
        $specification = ['param1' => 'value1'];
        $mainSpecification = ['main_param' => 'main_value'];

        $product = new Product($article, $files, $images, $specification, $mainSpecification);
        $array = $product->toArray();

        $expected = [
            'article' => $article,
            'files' => $files,
            'images' => $images,
            'specification' => $specification,
            'main_specification' => $mainSpecification
        ];

        $this->assertEquals($expected, $array);
    }

    public function testHasDocuments()
    {
        // Продукт с документами
        $productWithDocs = new Product('test1', [['url1', 'doc1']]);
        $this->assertTrue($productWithDocs->hasDocuments());

        // Продукт без документов
        $productWithoutDocs = new Product('test2');
        $this->assertFalse($productWithoutDocs->hasDocuments());

        // Продукт с пустым массивом документов
        $productEmptyDocs = new Product('test3', []);
        $this->assertFalse($productEmptyDocs->hasDocuments());
    }

    public function testHasImages()
    {
        // Продукт с изображениями
        $productWithImages = new Product('test1', [], ['image1.jpg']);
        $this->assertTrue($productWithImages->hasImages());

        // Продукт без изображений
        $productWithoutImages = new Product('test2');
        $this->assertFalse($productWithoutImages->hasImages());

        // Продукт с пустым массивом изображений
        $productEmptyImages = new Product('test3', [], []);
        $this->assertFalse($productEmptyImages->hasImages());
    }

    public function testGetDocumentUrls()
    {
        $files = [
            ['https://example.com/doc1.pdf', 'Document 1'],
            ['https://example.com/doc2.pdf', 'Document 2'],
            ['https://example.com/doc3.pdf', 'Document 3']
        ];

        $product = new Product('test', $files);
        $urls = $product->getDocumentUrls();

        $expectedUrls = [
            'https://example.com/doc1.pdf',
            'https://example.com/doc2.pdf',
            'https://example.com/doc3.pdf'
        ];

        $this->assertEquals($expectedUrls, $urls);
    }

    public function testGetDocumentUrlsWithEmptyFiles()
    {
        $product = new Product('test');
        $urls = $product->getDocumentUrls();

        $this->assertIsArray($urls);
        $this->assertEmpty($urls);
    }

    public function testGetDocumentNames()
    {
        $files = [
            ['https://example.com/doc1.pdf', 'Document 1'],
            ['https://example.com/doc2.pdf', 'Document 2'],
            ['https://example.com/doc3.pdf', 'Document 3']
        ];

        $product = new Product('test', $files);
        $names = $product->getDocumentNames();

        $expectedNames = ['Document 1', 'Document 2', 'Document 3'];

        $this->assertEquals($expectedNames, $names);
    }

    public function testGetDocumentNamesWithEmptyFiles()
    {
        $product = new Product('test');
        $names = $product->getDocumentNames();

        $this->assertIsArray($names);
        $this->assertEmpty($names);
    }

    public function testGetDocumentNamesWithMissingNames()
    {
        $files = [
            ['https://example.com/doc1.pdf', ''],
            ['https://example.com/doc2.pdf', null],
            ['https://example.com/doc3.pdf', 'Document 3']
        ];

        $product = new Product('test', $files);
        $names = $product->getDocumentNames();

        $this->assertEquals(['', null, 'Document 3'], $names);
    }

    public function testImmutableBehavior()
    {
        $originalFiles = [['url1', 'doc1']];
        $originalImages = ['image1.jpg'];

        $product = new Product('test', $originalFiles, $originalImages);

        // Пытаемся изменить исходные массивы
        $originalFiles[] = ['url2', 'doc2'];
        $originalImages[] = 'image2.jpg';

        // Продукт должен остаться неизменным
        $this->assertCount(1, $product->getFiles());
        $this->assertCount(1, $product->getImages());
    }

    public function testEmptyArraysInConstructor()
    {
        $product = new Product('test', [], [], [], []);

        $this->assertEquals('test', $product->getArticle());
        $this->assertEmpty($product->getFiles());
        $this->assertEmpty($product->getImages());
        $this->assertEmpty($product->getSpecification());
        $this->assertEmpty($product->getMainSpecification());
    }
}
