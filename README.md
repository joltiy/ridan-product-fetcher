# ridan-product-fetcher

Парсер каталога оборудования Ридан для получения полной технической информации по артикулу производителя. Получает полную информацию о продукции Ридан по артикулу, включая документацию, изображения и технические характеристики.

![CI](https://github.com/joltiy/ridan-product-fetcher/actions/workflows/ci.yml/badge.svg)
![Coverage](https://codecov.io/gh/joltiy/ridan-product-fetcher/branch/main/graph/badge.svg)
[![codecov](https://codecov.io/github/joltiy/ridan-product-fetcher/graph/badge.svg?token=I1K22JRXIK)](https://codecov.io/github/joltiy/ridan-product-fetcher)
## 📦 Возможности

- 🔍 **Поиск по артикулу** - Получение полной информации по артикулу производителя
- 📄 **Документация** - Паспорта, руководства, сертификаты в PDF
- 🖼️ **Изображения** - Фото и схемы оборудования
- ⚙️ **Технические характеристики** - Детальные спецификации и параметры
- 🏷️ **Структурированные данные** - Четкая организация информации
- 🤖 **Telegram integration** - Готовность к интеграции с ботами
- 🧪 **Полное тестирование** - Unit tests, integration tests, mock tests
- 🛡️ **Обработка ошибок** - Детальные исключения и логирование

## 📋 Установка

### Требования

- PHP 7.4 или выше
- Расширения: cURL, DOM, libxml

### Установка через Composer

```bash
composer require joltiy/ridan-product-fetcher
```

### Ручная установка
```bash
git clone https://github.com/joltiy/ridan-product-fetcher.git
cd ridan-product-fetcher
composer install
```

## 🚀 Быстрый старт

### Базовое использование
```php
<?php

require_once 'vendor/autoload.php';

use Joltiy\RidanProductFetcher\Parser\RidanParser;
use Joltiy\RidanProductFetcher\HttpClient\CurlHttpClient;

// Инициализация парсера
$httpClient = new CurlHttpClient();
$parser = new RidanParser($httpClient);

try {
    // Парсинг продукта по артикулу
    $product = $parser->parseProduct('060-117766R');
    
    // Основная информация
    echo "Артикул: " . $product->getArticle() . "\n";
    echo "Документы: " . count($product->getFiles()) . " файлов\n";
    echo "Изображения: " . count($product->getImages()) . " шт\n";
    echo "Характеристики: " . count($product->getSpecification()) . " параметров\n";
    
    // Документация
    foreach ($product->getFiles() as $index => $file) {
        echo "Документ {$index}: {$file[1]} -> {$file[0]}\n";
    }
    
    // Технические характеристики
    foreach ($product->getMainSpecification() as $key => $value) {
        echo "{$key}: {$value}\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
```

### Расширенное использование
```php
<?php

require_once 'vendor/autoload.php';

use Joltiy\RidanProductFetcher\Parser\RidanParser;
use Joltiy\RidanProductFetcher\HttpClient\CurlHttpClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Настройка логгера
$logger = new Logger('ridan-parser');
$logger->pushHandler(new StreamHandler('logs/parser.log', Logger::DEBUG));

// HTTP клиент с кастомными настройками
$httpClient = new CurlHttpClient([
    'timeout' => 60,
    'user_agent' => 'MyCompanyParser/1.0'
]);

// Парсер с логгером
$parser = new RidanParser($httpClient, $logger);

// Массовый парсинг
$articles = ['060-117766R', '060-117767R', '060-117768R'];
$results = [];

foreach ($articles as $article) {
    try {
        $product = $parser->parseProduct($article);
        $results[$article] = $product->toArray();
        echo "Успешно: {$article}\n";
    } catch (Exception $e) {
        echo "Ошибка {$article}: " . $e->getMessage() . "\n";
    }
}

// Экспорт в JSON
file_put_contents('products.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
```



### 📖 Documentation

### Класс Product

#### Методы
```php
// Геттеры
public function getArticle(): string;
public function getFiles(): array;
public function getImages(): array;
public function getSpecification(): array;
public function getMainSpecification(): array;

// Вспомогательные методы
public function hasDocuments(): bool;
public function hasImages(): bool;
public function getDocumentUrls(): array;
public function getDocumentNames(): array;
public function toArray(): array;
```

#### Структура данных
```php
[
    'article' => '060-117766R',
    'files' => [
        ['https://ridan.ru/files/.../passport.pdf', 'Паспорт оборудования'],
        ['https://ridan.ru/files/.../manual.pdf', 'Руководство по эксплуатации']
    ],
    'images' => [
        'https://ridan.ru/files/.../image1.jpg',
        'https://ridan.ru/files/.../image2.jpg'
    ],
    'specification' => [
        'Диапазон регулирования, бар' => '-0,5 - 6,0',
        'Максимальное рабочее давление, бар' => '16,5'
    ],
    'main_specification' => [
        'Тип сброса' => 'Авто',
        'Дифференциал ∆p, бар' => '0,6 - 4'
    ]
]
```

## 🧪 Тестирование

### Установка зависимостей для разработки
```bash
composer install --dev
```

### Запуск тестов
```bash
# Способ 1: Установите XDEBUG_MODE в системе
export XDEBUG_MODE=coverage
composer test

# Способ 2: Используйте PCOV (быстрее)
# Убедитесь что PCOV установлен и включен в php.ini
composer test

# Способ 3: Запуск без coverage (только тесты)
./vendor/bin/phpunit --no-coverage

# Проверка настроек PHP
php -i | grep -E "(xdebug|pcov)"

# Только unit тесты (быстро)
composer test-unit

# Интеграционные тесты (требуют интернета)
composer test-integration

# Покрытие кода
composer test-coverage
# Отчет будет доступен в coverage/index.html


```


### Запуск статического анализа
```bash
# Проверка кодстайла PSR-12
composer cs-check

# Автоисправление кодстайла
composer cs-fix

# Статический анализ PHPStan
composer stan

# Полная проверка качества
composer quality
```


### Примеры тестов

#### Unit тесты

```php
<?php

namespace Joltiy\RidanProductFetcher\Tests\Unit\Parser;

use Joltiy\RidanProductFetcher\Parser\RidanParser;
use Joltiy\RidanProductFetcher\HttpClient\HttpClientInterface;
use PHPUnit\Framework\TestCase;

class RidanParserTest extends TestCase
{
    public function testParseProductSuccess()
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->method('get')->willReturn($this->getMockHtml());
        
        $parser = new RidanParser($httpClientMock);
        $product = $parser->parseProduct('060-117766R');
        
        $this->assertEquals('060-117766R', $product->getArticle());
        $this->assertNotEmpty($product->getFiles());
    }
}
```


## 🔧 Конфигурация

### Настройка HTTP клиента
```php
use Joltiy\RidanProductFetcher\HttpClient\CurlHttpClient;

$httpClient = new CurlHttpClient([
    'timeout' => 60,                    // Таймаут в секундах
    'user_agent' => 'MyParser/1.0',     // User-Agent
    'verify_ssl' => false               // Отключить SSL проверку (не рекомендуется)
]);
```

### Настройка логгера

```php
use Joltiy\RidanProductFetcher\Parser\RidanParser;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('ridan-parser');
$logger->pushHandler(new StreamHandler('path/to/logfile.log', Logger::INFO));

$parser = new RidanParser($httpClient, $logger);
```


### Кастомный домен

```php
$parser->setDomain('https://test.ridan.ru'); // Для тестового окружения
```

##🐛 Отладка

### Включение детального логирования
```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

$logger = new Logger('ridan-parser');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$logger->pushProcessor(new PsrLogMessageProcessor());

$parser = new RidanParser($httpClient, $logger);
```

### Обработка ошибок
```php
try {
    $product = $parser->parseProduct('invalid-article');
} catch (\Joltiy\RidanProductFetcher\Parser\Exceptions\ParserException $e) {
    // Ошибки парсинга
    echo "Parser error: " . $e->getMessage();
} catch (\Joltiy\RidanProductFetcher\HttpClient\Exceptions\HttpException $e) {
    // HTTP ошибки
    echo "HTTP error ({$e->getStatusCode()}): " . $e->getMessage();
} catch (\Exception $e) {
    // Все остальные ошибки
    echo "Unexpected error: " . $e->getMessage();
}
```

## 📊 Пример вывода

```plaintext
Артикул: 060-117766R
Документы: 4 файлов
Изображения: 1 шт
Характеристики: 15 параметров

Документы:
0: Паспорт -> https://ridan.ru/files/1729/1729064-060111066R_Паспорт.pdf
1: Руководство по эксплуатации -> https://ridan.ru/files/1729/1729065-060111066R_Руководство_по_эксплуатации.pdf

Основные характеристики:
Тип сброса: Авто
Диапазон регулирования, бар: -0,5 - 6,0
Дифференциал ∆p, бар: 0,6 - 4
Максимальное рабочее давление, бар: 16,5
```

## 🤝 Contributing

1. Форкните репозиторий
2. Создайте feature branch: git checkout -b feature/amazing-feature
3. Сделайте коммит: git commit -m 'Add amazing feature'
4. Запушьте ветку: git push origin feature/amazing-feature
5. Создайте Pull Request

### Требования к коду
- Следуйте PSR-12 coding standard
- Пишите тесты для нового функционала
- Обновляйте документацию
- Используйте meaningful commit messages

## 🆘 Поддержка

Если у вас есть вопросы или проблемы:

1. Проверьте Issues
2. Создайте новое Issue с детальным описанием проблемы
3. Укажите версии PHP и используемые расширения

## 🔄 Changelog

### v1.0.0
- Первый стабильный релиз
- Полная поддержка парсинга продукции Ридан
- Unit и integration тесты
- PSR-12 compliance
- Детальная документация

## ⭐ Если этот проект был полезен, поставьте звезду на GitHub!