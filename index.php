<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

// Инициализация глобальных переменных из сессии
$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? '';

// Настройки отображения ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Инициализация маршрутизатора
$router = require __DIR__ . '/src/Routes.php';
echo $router->route($_SERVER['REQUEST_URI']);