<?php
namespace App\Router;

use App\Controllers\AboutController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\BasketController;
use App\Controllers\OrderController;
use App\Controllers\RegisterController;
use App\Controllers\UserController;
use App\Controllers\AppointmentController;

class Router {
    private array $routes = [];

    public function register(string $uri, array $controllerAction, array $methods = ['GET']): void {
        $this->routes[] = [
            'uri' => $uri,
            'controller' => $controllerAction[0],
            'action' => $controllerAction[1],
            'methods' => $methods
        ];
    }

    public function route(string $url): string {
        $path = parse_url($url, PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Сначала проверяем зарегистрированные маршруты
        foreach ($this->routes as $route) {
            if ($this->matchRoute($path, $route['uri']) && in_array($method, $route['methods'])) {
                return $this->callControllerAction($route['controller'], $route['action']);
            }
        }

        // Если маршрут не найден, используем legacy-маршрутизацию
        return $this->handleLegacyRoute($path);
    }

    private function matchRoute(string $path, string $routeUri): bool {
        return $path === $routeUri;
    }

    private function callControllerAction(string $controllerClass, string $action): string {
        $fullClassName = "App\\Controllers\\" . $controllerClass;
        
        if (!class_exists($fullClassName)) {
            throw new \RuntimeException("Controller {$fullClassName} not found. Check namespace and autoloading.");
        }
    
        $controller = new $fullClassName();
        
        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Method {$action} not found in {$fullClassName}");
        }
        
        return $controller->$action();
    }

    private function handleLegacyRoute(string $path): string {
        $pieces = explode("/", $path);
        $resource = $pieces[2] ?? null;

        switch ($resource) {
            case "about":
                return (new AboutController())->get();
            case "order":
                return (new OrderController())->get();
            case "register":
                return (new RegisterController())->get();
            case "profile":
                return (new UserController())->profile();
            case "verify":
                $token = $pieces[3] ?? null;
                return (new RegisterController())->verify($token);
            case "login":
                return (new UserController())->get();
            case "logout":
                unset($_SESSION['user_id'], $_SESSION['username']);
                session_destroy();
                header("Location: /avtoservis/");
                return "";
            case 'basket_clear':
                (new BasketController())->clear();
                header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
                return '';
            case "products":
                $id = $pieces[3] ?? null;
                return (new ProductController())->get($id);
            case "basket":
                (new BasketController())->add();
                header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
                return "";
            case "select_time":
                return (new OrderController())->selectTime();
            case "history":
                return (new AppointmentController())->getHistory();
            case "profile":
                return (new UserController())->profile();
            default:
                return (new HomeController())->get();
        }
    }
}