<?php
namespace App\Router;

use App\Controllers\AboutController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\BasketController;
use App\Controllers\OrderController;
use App\Controllers\RegisterController;
use App\Controllers\UserController;
use App\Controllers\AppointmentController; // Добавьте этот импорт

class Router {
    public function route(string $url): string {
        $path = parse_url($url, PHP_URL_PATH);
        $pieces = explode("/", $path);
        $resource = $pieces[2] ?? null; // Используем null coalescing operator для избежания ошибок

        switch ($resource) {
            case "about":
                $about = new AboutController();
                return $about->get();
            case "order":
                $orderController = new OrderController();
                return $orderController->get();
            case "register":
                $registerController = new RegisterController();
                return $registerController->get();
            case "profile":
                $userController = new UserController();
                return $userController->profile();
            case "verify":
                $registerController = new RegisterController();
                $token = $pieces[3] ?? null; // Используем null coalescing operator
                return $registerController->verify($token);
            case "login":
                $userController = new UserController();
                return $userController->get();
            case "logout":
                unset($_SESSION['user_id']);
                unset($_SESSION['username']);
                session_destroy();
                header("Location: /avtoservis/");
                return ""; // Возвращаем пустую строку
            case 'basket_clear':
                $basketController = new BasketController();
                $basketController->clear();
                $prevUrl = $_SERVER['HTTP_REFERER'];
                header("Location: {$prevUrl}");
                return ''; // Возвращаем пустую строку
            case "products":
                $productController = new ProductController();
                $id = $pieces[3] ?? null; // Используем null coalescing operator
                return $productController->get($id);                
            case "basket":
                $basketController = new BasketController();
                $basketController->add();
                $prevUrl = $_SERVER['HTTP_REFERER'];
                header("Location: {$prevUrl}");                    
                return ""; // Возвращаем пустую строку
            case "select_time":
                $orderController = new OrderController();
                return $orderController->selectTime();
            case "history":
                $userController = new UserController();
                return $userController->getOrdersHistory(); // Обработка истории заказов
            default:
                $home = new HomeController();
                return $home->get(); // Возвращаем главную страницу по умолчанию
        }

        // Если ни один из случаев не сработал, возвращаем пустую строку
        return ""; // Это гарантирует, что метод всегда возвращает строку
    }

    // Добавьте метод для обработки POST-запроса на создание записи
    public function post(string $url): string {
        $path = parse_url($url, PHP_URL_PATH);
        $pieces = explode("/", $path);
        $resource = $pieces[2] ?? null;

        if ($resource === "book") {
            $appointmentController = new AppointmentController();
            return $appointmentController->bookAppointment(); // Обработка записи
        }

        return ""; // Возвращаем пустую строку, если не найдено
    }
}