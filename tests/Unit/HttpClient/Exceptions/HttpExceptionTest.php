<?php

namespace Joltiy\RidanProductFetcher\Tests\Unit\HttpClient\Exceptions;

use Joltiy\RidanProductFetcher\HttpClient\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Joltiy\RidanProductFetcher\HttpClient\Exceptions\HttpException
 */
class HttpExceptionTest extends TestCase
{
    public function testExceptionCreation()
    {
        $exception = new HttpException('Test error', 404);

        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals(404, $exception->getStatusCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithPrevious()
    {
        $previous = new \RuntimeException('Previous error');
        $exception = new HttpException('Test error', 500, $previous);

        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals(500, $exception->getStatusCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testFromCurlError()
    {
        $exception = HttpException::fromCurlError(6, 'Could not resolve host', 'https://example.com');

        $this->assertStringContainsString('cURL error (6)', $exception->getMessage());
        $this->assertStringContainsString('Could not resolve host', $exception->getMessage());
        $this->assertNull($exception->getStatusCode());
    }

    public function testFromHttpError()
    {
        $exception = HttpException::fromHttpError(404, 'https://example.com');

        $this->assertStringContainsString('HTTP error 404', $exception->getMessage());
        $this->assertEquals(404, $exception->getStatusCode());
    }
}
