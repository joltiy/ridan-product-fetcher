<?php

namespace Joltiy\RidanProductFetcher\Parser\Interfaces;

use Joltiy\RidanProductFetcher\Models\Product;
use Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException;

interface ParserInterface
{
    /**
     * Parse product information by article number
     *
     * @param string $article Product article number
     * @return Product Parsed product data
     * @throws ParserException
     */
    public function parseProduct(string $article): Product;
}
