<?php 
namespace App\Controllers;

use App\Views\UserTemplate;
use App\Configs\Config;
use App\Services\UserDBStorage;

class UserController {
    /* Форма входа на сайт */
    public function get(): string {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST")
            return $this->login();

        return UserTemplate::getUserTemplate();
    }
    
    public function login():string {      

        $arr = [];
        $arr['username'] =  strip_tags($_POST['username']);
        $arr['password'] = strip_tags($_POST['password']);

        // проверка логина и пароля
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceDB = new UserDBStorage();
            if (!$serviceDB->loginUser($arr['username'], $arr['password'])) {
                $_SESSION['flash'] = "Ошибка ввода логина или пароля";
                return UserTemplate::getUserTemplate();
            }
        }

        // переадресация на Главную
	    header("Location: /avtoservis/");
        return "";
    }

    /* Форма профиля пользователя */
    public function profile(): string {
        global $user_id;

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST")
            return $this->updateProfile();


        $data = null;
        // проверка логина и пароля
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceDB = new UserDBStorage();
            $data = $serviceDB->getUserData($user_id);
            if (! $data) {
                $_SESSION['flash'] = "Ошибка получения данных пользователя";
            }
        }
        return UserTemplate::getProfileTemplate($data);
    }

    public function updateProfile(): string {
        return "";
    }
    public function getOrdersHistory(): string {
        $userId = $_SESSION['user_id']; // Получите ID авторизованного пользователя
        if (!$userId) {
            header("Location: /avtoservis/login"); // Перенаправление на страницу входа, если пользователь не авторизован
            exit;
        }
    
        // Создаем экземпляр UserDBStorage
        $userDBStorage = new UserDBStorage();
        $data = $userDBStorage->getDataHistory($userId); // Получаем историю заказов
    
        // Если данных нет, инициализируем пустой массив
        if ($data === null) {
            $data = []; // Инициализируем пустой массив, если нет заказов
            $_SESSION['flash'] = "У вас нет заказов."; // Уведомление, если заказов нет
        }
    
        return UserTemplate::getHistoryTemplate($data); // Возвращаем шаблон с историей заказов
    }
}

