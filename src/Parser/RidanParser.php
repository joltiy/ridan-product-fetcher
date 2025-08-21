<?php

namespace Joltiy\RidanProductFetcher\Parser;

use Joltiy\RidanProductFetcher\HttpClient\Exceptions\HttpException;
use Joltiy\RidanProductFetcher\Parser\Interfaces\ParserInterface;
use Joltiy\RidanProductFetcher\HttpClient\HttpClientInterface;
use Joltiy\RidanProductFetcher\Models\Product;
use Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException;
use DOMDocument;
use DOMXPath;
use DOMAttr;
use DOMText;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class RidanParser implements ParserInterface
{
    private const FILES_KEY = 'files';
    private const IMAGES_KEY = 'images';
    private const SPECIFICATION_KEY = 'specification';
    private const MAIN_SPECIFICATION_KEY = 'main_specification';

    private const FILES_XPATH =
        '//div[contains(@class, "docs-list__table-row") and @data-param-value="1"]'
        . '//div[contains(@class, "docs-list__table-cel") and @data-label="Название"]'
        . '/a[1]/@href |'
        . '//div[contains(@class, "docs-list__table-row") and @data-param-value="1"]'
        . '//div[contains(@class, "docs-list__table-cel") and @data-label="Название"]'
        . '/a[1]/text()';
//    private const FILES_XPATH = <<<XPATH
////div[@class='docs-list__table-row' and @data-param-value="1"]
//    //div[@class='docs-list__table-cel' and @data-label="Название"]
//    /a[1]/@href |
////div[@class='docs-list__table-row' and @data-param-value="1"]
//    //div[@class='docs-list__table-cel' and @data-label="Название"]
//    /a[1]/text()
//XPATH;

    private const IMAGES_XPATH =
        '//div[@class="carousel-inner"]//a[contains(@class, "carousel-item")]/@href';
    private const MAIN_SPECIFICATION_DTS_XPATH =
        '//div[contains(@class, "specifications specifications_in-tab")]'
        . '//dl[@class="row g-0"]/dt[@class="col-auto"]';
    private const MAIN_SPECIFICATION_DDS_XPATH =
        '//div[contains(@class, "specifications specifications_in-tab")]'
        . '//dl[@class="row g-0"]/dd[@class="col-auto"]';
    private const SPECIFICATION_DTS_XPATH =
        '//div[contains(@class, "tab-pane") and @id="info-2"]'
        . '/div[contains(@class, "specifications")]'
        . '//dl[@class="row g-0"]/dt[@class="col-auto"]';
    private const SPECIFICATION_DDS_XPATH =
        '//div[contains(@class, "tab-pane") and @id="info-2"]'
        . '/div[contains(@class, "specifications")]'
        . '//dl[@class="row g-0"]/dd[@class="col-auto"]';

    private string $domain = 'https://ridan.ru';
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        ?LoggerInterface $logger = new NullLogger()
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function parseProduct(string $article): Product
    {
        $this->logger->info('Parsing product', ['article' => $article]);

        try {
            $htmlXPath = $this->fetchProductHtml($article);

            $parsedData = [
                'article' => $article,
                self::FILES_KEY => $this->extractFiles($htmlXPath),
                self::IMAGES_KEY => $this->extractImages($htmlXPath),
                self::SPECIFICATION_KEY => $this->extractSpecification($htmlXPath),
                self::MAIN_SPECIFICATION_KEY => $this->extractMainSpecification($htmlXPath),
            ];

            $this->logger->debug('Product parsed successfully', [
                'article' => $article,
                'files_count' => count($parsedData[self::FILES_KEY]),
                'images_count' => count($parsedData[self::IMAGES_KEY]),
            ]);

            return Product::fromArray($parsedData);
        } catch (ParserException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error during parsing', [
                'article' => $article,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            throw new ParserException(
                sprintf('Unexpected error while parsing product %s: %s', $article, $e->getMessage()),
                0,
                $e
            );
        }
    }

    private function fetchProductHtml(string $article): DOMXPath
    {
        $url = $this->domain . '/product/' . urlencode($article);

        $this->logger->debug('Fetching product page', ['url' => $url]);

        try {
            $html = $this->httpClient->get($url);

            if (empty($html) || strlen(trim($html)) < 10) {
                // Слишком короткий HTML считается пустым ответом
                throw ParserException::forEmptyResponse($article);
            }

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);

            // Пытаемся загрузить HTML
            $loaded = @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

            $errors = libxml_get_errors();
            libxml_clear_errors();

            // Логируем предупреждения, но продолжаем работу
            if (!empty($errors)) {
                $errorMessages = array_map(fn($e) => $e->message, $errors);
                $this->logger->warning('HTML parsed with warnings', [
                    'article' => $article,
                    'warnings' => $errorMessages,
                    'warning_count' => count($errors)
                ]);
            }

            return new DOMXPath($dom);
        } catch (HttpException $e) {
            $this->logger->warning('HTTP error fetching product', [
                'article' => $article,
                'error' => $e->getMessage(),
            ]);
            throw new ParserException(
                sprintf('HTTP error while fetching product %s: %s', $article, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * @return array<int, array<int, string>> Возвращает массив пар (ссылки и текст)
     */
    private function extractFiles(DOMXPath $htmlXPath): array
    {
        $nodes = $htmlXPath->query(self::FILES_XPATH);
        if ($nodes === false) {
            $this->logger->warning('XPath query returned false.', ['xpath' => self::FILES_XPATH]);
            return []; // Возврат пустого массива в случае ошибки
        }

        $results = [];
        $buffer = [];

        foreach ($nodes as $node) {
            if ($node instanceof DOMAttr || $node instanceof DOMText) {
                $buffer[] = trim($node->nodeValue ?? '');

                // Если буфер заполнен парой [url, имя], добавляем в результаты
                if (count($buffer) === 2) {
                    $results[] = [$this->getDomain() . $buffer[0], $buffer[1]];
                    $buffer = []; // Сброс буфера
                }
            }
        }

        return $results;
//        foreach ($nodes as $node) {
//            if ($node instanceof DOMAttr) {
//                $results[] = $this->domain . ($node->value ?? '');
//            } elseif ($node instanceof DOMText) {
//                $results[] = $node->nodeValue ?? '';
//            }
//        }
//
//        return array_chunk($results, 2);
    }

    /**
     * @return array<string> Возвращает массив ссылок на изображения
     */
    private function extractImages(DOMXPath $htmlXPath): array
    {
        $nodes = $htmlXPath->query(self::IMAGES_XPATH);
        if ($nodes === false) {
            $this->logger->warning('XPath query returned false.', ['xpath' => self::IMAGES_XPATH]);
            return []; // Возврат пустого массива
        }

        $images = [];

        foreach ($nodes as $node) {
            $images[] = $this->domain . $node->nodeValue;
        }

        return $images;
    }

    /**
     * @return array<string, string> Возвращает массив спецификаций (название-значение)
     */
    private function extractSpecification(DOMXPath $htmlXPath): array
    {
        return $this->extractSpecificationData(
            $htmlXPath,
            self::SPECIFICATION_DTS_XPATH,
            self::SPECIFICATION_DDS_XPATH
        );
    }

    /**
     * @return array<string, string> Возвращает массив ключ-значение для основной спецификации
     */
    private function extractMainSpecification(DOMXPath $htmlXPath): array
    {
        return $this->extractSpecificationData(
            $htmlXPath,
            self::MAIN_SPECIFICATION_DTS_XPATH,
            self::MAIN_SPECIFICATION_DDS_XPATH
        );
    }

    /**
     * @param DOMXPath $htmlXPath
     * @param string $dtsXPath XPath для ключей
     * @param string $ddsXPath XPath для значений
     *
     * @return array<string, string> Возвращает парные спецификации (название-значение)
     */
    private function extractSpecificationData(DOMXPath $htmlXPath, string $dtsXPath, string $ddsXPath): array
    {
        $dtNodes = $htmlXPath->query($dtsXPath);
        $ddNodes = $htmlXPath->query($ddsXPath);

        if ($dtNodes === false || $ddNodes === false) {
            $this->logger->warning('One of the XPath queries returned false.', [
                'dtsXPath' => $dtsXPath,
                'ddsXPath' => $ddsXPath,
            ]);
            return [];
        }

        $specifications = [];

        if ($dtNodes->length === $ddNodes->length) {
            for ($i = 0; $i < $dtNodes->length; $i++) {
                $dtNode = $dtNodes->item($i);
                $ddNode = $ddNodes->item($i);

                $key = trim($dtNode?->textContent ?? '');
                $value = trim($ddNode?->textContent ?? '');

                if (!empty($key) && !empty($value)) {
                    $specifications[$key] = $value;
                }
            }
        } else {
            $this->logger->warning('Mismatched node lengths.', [
                'dtNodes' => $dtNodes->length,
                'ddNodes' => $ddNodes->length,
            ]);
        }

        return $specifications;
    }

    public function setDomain(string $domain): void
    {
        $this->domain = rtrim($domain, '/');
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}
