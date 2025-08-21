<?php

namespace Joltiy\RidanProductFetcher\Tests\Unit\Parser\Exceptions;

use Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException
 */
class ParserExceptionTest extends TestCase
{
    public function testExceptionCreation()
    {
        $exception = new ParserException('Test error');

        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertNull($exception->getPrevious());
    }

    public function testForEmptyResponse()
    {
        $exception = ParserException::forEmptyResponse('060-117766R');

        $this->assertStringContainsString('Empty response received', $exception->getMessage());
        $this->assertStringContainsString('060-117766R', $exception->getMessage());
    }

    public function testForHtmlParsing()
    {
        $exception = ParserException::forHtmlParsing('060-117766R', 'Invalid tag');

        $this->assertStringContainsString('Failed to parse HTML', $exception->getMessage());
        $this->assertStringContainsString('060-117766R', $exception->getMessage());
        $this->assertStringContainsString('Invalid tag', $exception->getMessage());
    }

    public function testForElementNotFound()
    {
        $exception = ParserException::forElementNotFound('060-117766R', 'specifications');

        $this->assertStringContainsString('Element "specifications" not found', $exception->getMessage());
        $this->assertStringContainsString('060-117766R', $exception->getMessage());
    }

    public function testForInvalidXpath()
    {
        $exception = ParserException::forInvalidXpath('060-117766R', '//invalid[xpath');

        $this->assertStringContainsString('Invalid XPath expression', $exception->getMessage());
        $this->assertStringContainsString('060-117766R', $exception->getMessage());
    }
}
