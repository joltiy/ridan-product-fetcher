<?php

namespace Joltiy\RidanProductFetcher\Tests\Unit\Parser;

use Joltiy\RidanProductFetcher\Parser\RidanParser;
use Joltiy\RidanProductFetcher\HttpClient\HttpClientInterface;
use Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException;
use Joltiy\RidanProductFetcher\HttpClient\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Joltiy\RidanProductFetcher\Parser\RidanParser
 * @covers \Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException
 */
class RidanParserTest extends TestCase
{
    private $httpClientMock;
    private $loggerMock;
    private $parser;


    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->parser = new RidanParser($this->httpClientMock, $this->loggerMock);
    }

    public function testParseProductWithHttpException()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('HTTP error while fetching');

        $article = '060-117766R';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willThrowException(new HttpException('Connection failed', 500));

        $this->parser->parseProduct($article);
    }

    public function testParseProductWithEmptyResponse()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Empty response received');

        $article = '060-117766R';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn('');

        $this->parser->parseProduct($article);
    }

    public function testParseProductWithValidHtml()
    {
        $article = '060-117766R';
        $mockHtml = $this->getValidMockHtml();

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->with($this->stringContains($article))
            ->willReturn($mockHtml);

        $this->loggerMock->expects($this->atLeastOnce())
            ->method('info');

        $product = $this->parser->parseProduct($article);

        $this->assertEquals($article, $product->getArticle());
        $this->assertIsArray($product->getFiles());
        $this->assertIsArray($product->getImages());
        $this->assertIsArray($product->getSpecification());
        $this->assertIsArray($product->getMainSpecification());
    }

    public function testSetAndGetDomain()
    {
        $testDomain = 'https://test.ridan.ru';

        $this->parser->setDomain($testDomain);

        $this->assertEquals($testDomain, $this->parser->getDomain());
    }

    public function testParseProductWithCompletelyInvalidHtml()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Empty response received');

        $article = '060-117766R';

        // Пустой ответ вместо невалидного HTML
        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn('');

        $this->parser->parseProduct($article);
    }

    public function testParseProductWithVeryShortHtml()
    {
        $article = '060-117766R';

        // Очень короткий HTML (меньше 10 символов) - должен обработаться
        $shortHtml = '<html></html>';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn($shortHtml);

        $product = $this->parser->parseProduct($article);

        // Должен вернуться продукт с пустыми данными
        $this->assertEquals($article, $product->getArticle());
        $this->assertEmpty($product->getFiles());
        $this->assertEmpty($product->getImages());
    }

    public function testParseProductWithHtmlContainingNoProductData()
    {
        $article = '060-117766R';

        // HTML без данных о продукте (например, страница 404)
        $htmlWithoutData = '<!DOCTYPE html><html><body><h1>Product Not Found</h1></body></html>';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn($htmlWithoutData);

        $product = $this->parser->parseProduct($article);

        // Должен вернуться продукт с пустыми данными
        $this->assertEquals($article, $product->getArticle());
        $this->assertEmpty($product->getFiles());
        $this->assertEmpty($product->getImages());
        $this->assertEmpty($product->getSpecification());
        $this->assertEmpty($product->getMainSpecification());
    }

    private function getValidMockHtml(): string
    {
        return file_get_contents(__DIR__ . '/../../Mocks/ridan_product_page.html');
    }

    public function testParseProductWithHtmlContainingOnlyFiles()
    {
        $article = '060-117766R';
//        $html = $this->getValidMockHtml();
        $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Test Product</title>
    </head>
    <body>
        <div class="docs-list">
            <div class="docs-list__table-row" data-param-value="1">
                 <div class="docs-list__table-cel" data-label="Название">
                    <a href="/files/1729/1729064-060111066R_Паспорт.pdf">060111066R_Паспорт.pdf</a>
                </div>
            </div>
            <div class="docs-list__table-row" data-param-value="1">
                  <div class="docs-list__table-cel" data-label="Название">
                    <a href="/files/1729/1729065-060111066R_Руководство.pdf">060111066R_Руководство.pdf</a>
                </div>
            </div>
            <div class="docs-list__table-row" data-param-value="1">
                  <div class="docs-list__table-cel" data-label="Название">
                    <a href="/files/1729/1729066-ЕАЭС_RU_С-RU.АЯ46.В.30948_23.pdf">ЕАЭС_RU_С-RU.АЯ46.В.30948_23.pdf</a>
                </div>
            </div>
        </div>
    </body>
    </html>';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn($html);

        $product = $this->parser->parseProduct($article);

        $this->assertEquals($article, $product->getArticle());

        $this->assertCount(3, $product->getFiles());
        $this->assertEmpty($product->getImages());
        $this->assertEmpty($product->getSpecification());
        $this->assertEmpty($product->getMainSpecification());
    }

    public function testParseProductWithHtmlContainingOnlyImages()
    {
        $article = '060-117766R';
        $html = '
    <!DOCTYPE html>
    <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Test Product</title>
    </head>
    <body>
        <div class="carousel-inner">
            <a class="carousel-item" href="/images/image1.jpg"></a>
            <a class="carousel-item" href="/images/image2.jpg"></a>
        </div>
    </body>
    </html>';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn($html);

        $product = $this->parser->parseProduct($article);

        $this->assertEquals($article, $product->getArticle());
        $this->assertEmpty($product->getFiles());
        $this->assertCount(2, $product->getImages());
        $this->assertEmpty($product->getSpecification());
        $this->assertEmpty($product->getMainSpecification());
    }

    public function testParseProductWithHtmlContainingOnlySpecifications()
    {
        $article = '060-117766R';
        $html = '
    <!DOCTYPE html>
    <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Test Product</title>
    </head>
    <body>
        <div class="specifications specifications_in-tab">
            <dl class="row g-0">
                <dt class="col-auto">Параметр 1</dt>
                <dd class="col-auto">Значение 1</dd>
                <dt class="col-auto">Параметр 2</dt>
                <dd class="col-auto">Значение 2</dd>
            </dl>
        </div>
    </body>
    </html>';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn($html);

        $product = $this->parser->parseProduct($article);

        $this->assertEquals($article, $product->getArticle());
        $this->assertEmpty($product->getFiles());
        $this->assertEmpty($product->getImages());
        $this->assertCount(2, $product->getMainSpecification());
        $this->assertEmpty($product->getSpecification());
    }

    public function testParseProductWithDomainChange()
    {
        $article = '060-117766R';
        $testDomain = 'https://test.ridan.ru';
        $mockHtml = $this->getValidMockHtml();

        $this->parser->setDomain($testDomain);

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->with($this->stringContains($testDomain))
            ->willReturn($mockHtml);

        $product = $this->parser->parseProduct($article);

        $this->assertEquals($article, $product->getArticle());
        $this->assertEquals($testDomain, $this->parser->getDomain());
    }

    public function testParseProductWithMalformedHtmlButRecoverable()
    {
        $article = '060-117766R';

        // HTML с ошибками, но которые можно обработать
        $malformedHtml = '
    <!DOCTYPE html>
    <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Test Product</title>
    </head>
    <body>
        <div class="docs-list">
            <div class="docs-list__table-row" data-param-value="1">
                <div class="docs-list__table-cel" data-label="Название">
                    <a href="/files/test.pdf">Тестовый документ
                </div> <!-- незакрытый тег -->
            </div>
        </div>
    </body>
    </html>';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn($malformedHtml);

        $product = $this->parser->parseProduct($article);

        $this->assertEquals($article, $product->getArticle());
        // Даже с ошибками HTML должен вернуть объект продукта
        $this->assertInstanceOf(\Joltiy\RidanProductFetcher\Models\Product::class, $product);
    }



    public function testParseProductWithHtmlContainingDifferentDataStructures()
    {
        $article = '060-117766R';
        $html = '
    <!DOCTYPE html>
    <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Test Product</title>
    </head>
    <body>
        <!-- Разные варианты структур -->
        <div class="carousel-inner">
            <img src="/images/image1.jpg" alt="Image 1">
            <img src="/images/image2.jpg" alt="Image 2">
        </div>
        
        <div class="specifications">
            <dl>
                <dt>Параметр 1</dt>
                <dd>Значение 1</dd>
                <dt>Параметр 2</dt>
                <dd>Значение 2</dd>
            </dl>
        </div>
    </body>
    </html>';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn($html);

        $product = $this->parser->parseProduct($article);

        $this->assertEquals($article, $product->getArticle());
        // Проверяем что парсер не падает на разных структурах
        $this->assertIsArray($product->getImages());
        $this->assertIsArray($product->getSpecification());
    }

    public function testDomainGetterAndSetter()
    {
        $testDomain = 'https://test.example.com';

        $this->parser->setDomain($testDomain);

        $this->assertEquals($testDomain, $this->parser->getDomain());

        // Проверяем что домен используется в URL
        $article = 'test-article';
        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->with($this->stringContains($testDomain))
            ->willReturn('<html></html>');

        $this->parser->parseProduct($article);
    }

    public function testParseProductWithNetworkTimeout()
    {
        $this->expectException(\Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException::class);

        $article = '060-117766R';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willThrowException(new HttpException('Connection timed out', 0));

        $this->parser->parseProduct($article);
    }

    public function testParseProductWithSslError()
    {
        $this->expectException(\Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException::class);

        $article = '060-117766R';

        $this->httpClientMock->expects($this->once())
            ->method('get')
            ->willThrowException(new HttpException('SSL certificate problem', 0));

        $this->parser->parseProduct($article);
    }

    public function testUnexpectedErrorDuringParsing()
    {
        // Создаем мок HTTP клиента
        $httpClientMock = $this->createMock(HttpClientInterface::class);

        // Настраиваем мок так, чтобы метод get выбрасывал RuntimeException
        $httpClientMock->method('get')->willThrowException(new \RuntimeException('Unexpected runtime error'));

        // Создаем экземпляр RidanParser с моком клиента
        $parser = new RidanParser($httpClientMock);

        // Ожидаем ParserException
        $this->expectException(ParserException::class);

        // Проверяем сообщение исключения
        $this->expectExceptionMessage('Unexpected error while parsing product');

        // Вызываем метод, который должен обработать некорректное поведение клиента
        $parser->parseProduct('060-117766R');
    }
}
