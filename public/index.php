<?php
//Запрет на неявное преобразование типов
declare(strict_types=1);

//Сессии на все страницы
session_start();

try {
//Создание экземпляра приложения и его запуск
    $app = require_once __DIR__ . '/../core/bootstrap.php';
    $app->run();
} catch (\Throwable $exception) {
    echo '<pre>';
    print_r($exception);
    echo '</pre>';
}