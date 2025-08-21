<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Установка временной зоны для тестов
date_default_timezone_set('UTC');

// Обработка ошибок libxml
libxml_use_internal_errors(true);
