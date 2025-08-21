<?php

namespace Joltiy\RidanProductFetcher\Tests\Unit\Models;

use Joltiy\RidanProductFetcher\Models\Product;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Joltiy\RidanProductFetcher\Models\Product
 */
class ProductTest extends TestCase
{
    public function testProductCreation()
    {
        $product = new Product(
            '060-117766R',
            [['url1', 'name1'], ['url2', 'name2']],
            ['image1.jpg', 'image2.jpg'],
            ['spec1' => 'value1'],
            ['main_spec1' => 'main_value1']
        );

        $this->assertEquals('060-117766R', $product->getArticle());
        $this->assertCount(2, $product->getFiles());
        $this->assertCount(2, $product->getImages());
        $this->assertCount(1, $product->getSpecification());
        $this->assertCount(1, $product->getMainSpecification());
    }

    public function testFromArray()
    {
        $data = [
            'article' => '060-117766R',
            'files' => [['url1', 'name1']],
            'images' => ['image1.jpg'],
            'specification' => ['spec1' => 'value1'],
            'main_specification' => ['main_spec1' => 'main_value1']
        ];

        $product = Product::fromArray($data);

        $this->assertEquals('060-117766R', $product->getArticle());
        $this->assertCount(1, $product->getFiles());
        $this->assertCount(1, $product->getImages());
    }

    public function testToArray()
    {
        $product = new Product(
            '060-117766R',
            [['url1', 'name1']],
            ['image1.jpg'],
            ['spec1' => 'value1'],
            ['main_spec1' => 'main_value1']
        );

        $array = $product->toArray();

        $this->assertEquals('060-117766R', $array['article']);
        $this->assertCount(1, $array['files']);
        $this->assertCount(1, $array['images']);
    }

    public function testHelperMethods()
    {
        $product = new Product(
            '060-117766R',
            [['https://example.com/doc.pdf', 'Document Name']],
            ['https://example.com/image.jpg'],
            ['spec' => 'value'],
            ['main_spec' => 'main_value']
        );

        $this->assertTrue($product->hasDocuments());
        $this->assertTrue($product->hasImages());
        $this->assertEquals(['https://example.com/doc.pdf'], $product->getDocumentUrls());
        $this->assertEquals(['Document Name'], $product->getDocumentNames());

        // Test empty product
        $emptyProduct = new Product('test');
        $this->assertFalse($emptyProduct->hasDocuments());
        $this->assertFalse($emptyProduct->hasImages());
        $this->assertEmpty($emptyProduct->getDocumentUrls());
        $this->assertEmpty($emptyProduct->getDocumentNames());
    }
}
