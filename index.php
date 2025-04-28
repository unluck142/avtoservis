<?php
session_start(); // Инициализация сессии

require_once __DIR__ . '/vendor/autoload.php'; // Подключение автозагрузчика Composer

use App\Router\Router;

// Обновляем глобальные переменные - данными из сессии
$user_id = 0; 
$username = "";
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

$router = new Router();
$url = $_SERVER['REQUEST_URI']; 
echo $router->route($url);

// Включение отображения ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);