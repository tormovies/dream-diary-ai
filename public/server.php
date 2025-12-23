<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Если запрашивается существующий файл или директория, отдаем его напрямую
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // вернуть файл как есть
}

// Все остальные запросы перенаправляем в index.php
require_once __DIR__ . '/index.php';



