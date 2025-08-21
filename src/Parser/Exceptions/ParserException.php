<?php

namespace Joltiy\RidanProductFetcher\Parser\Exceptions;

use RuntimeException;

class ParserException extends RuntimeException
{
    public static function forEmptyResponse(string $article): self
    {
        return new self(sprintf('Empty response received for article: %s', $article));
    }

    public static function forHtmlParsing(string $article, string $error): self
    {
        return new self(sprintf('Failed to parse HTML for article %s: %s', $article, $error));
    }

    public static function forElementNotFound(string $article, string $element): self
    {
        return new self(sprintf('Element "%s" not found for article: %s', $element, $article));
    }

    public static function forInvalidXpath(string $article, string $xpath): self
    {
        return new self(sprintf('Invalid XPath expression for article %s: %s', $article, $xpath));
    }
}
