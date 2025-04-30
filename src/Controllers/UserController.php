<?php 
namespace App\Controllers;

use App\Views\UserTemplate;
use App\Configs\Config;
use App\Services\UserDBStorage;

class UserController {
    private $userStorage;

    public function __construct() {
        if (Config::STORAGE_TYPE === Config::TYPE_DB) {
            $this->userStorage = new UserDBStorage();
        }
    }

    /* Форма входа на сайт */
    public function get(): string {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            return $this->login();
        }

        return UserTemplate::getUserTemplate();
    }
    
    public function login(): string {
        $arr = [];
        $arr['username'] = strip_tags($_POST['username'] ?? '');
        $arr['password'] = strip_tags($_POST['password'] ?? '');

        // проверка логина и пароля
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            if (!$this->userStorage->loginUser($arr['username'], $arr['password'])) {
                $_SESSION['flash'] = "Ошибка ввода логина или пароля";
                return UserTemplate::getUserTemplate();
            }
        }

        // переадресация на Главную
        header("Location: /avtoservis/");
        return "";
    }

    public function logout(): void {
        session_start();
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        session_destroy();
        header("Location: /avtoservis/");
        exit;
    }

    public function profile(): string {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /avtoservis/login");
            exit;
        }
    
        try {
            $user = $this->userStorage->getUserData($_SESSION['user_id']);
            
            $profileData = [
                'username' => $user['username'] ?? 'Не указано',
                'email' => $user['email'] ?? 'Не указано',
                'address' => $user['address'] ?? '',
                'phone' => $user['phone'] ?? '',
                'avatar' => $user['avatar'] ?? '/assets/images/default-avatar.png'
            ];
    
            return UserTemplate::getProfileForm($profileData);
        } catch (\RuntimeException $e) {
            $_SESSION['flash'] = "Ошибка загрузки профиля";
            header("Location: /avtoservis/");
            exit;
        }
    }

    public function updateProfile(): void {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash'] = "Необходимо войти в аккаунт.";
            header("Location: /avtoservis/login");
            exit;
        }
    
        $userId = (int)$_SESSION['user_id'];
        $data = [
            'username' => strip_tags($_POST['username'] ?? ''),
            'email' => strip_tags($_POST['email'] ?? ''),
            'address' => strip_tags($_POST['address'] ?? ''),
            'phone' => strip_tags($_POST['phone'] ?? '')
        ];
    
        // Обработка загрузки аватара
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/';
            
            // Создаем папку, если её нет
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
    
            $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
            if (in_array(strtolower($fileExtension), $allowedExtensions)) {
                $newFileName = uniqid('avatar_', true) . '.' . $fileExtension;
                $destPath = $uploadDir . $newFileName;
    
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
                    $data['avatar'] = "/assets/uploads/" . $newFileName;
                } else {
                    $_SESSION['flash'] = "Ошибка при сохранении аватара";
                }
            } else {
                $_SESSION['flash'] = "Недопустимый формат файла. Разрешены: jpg, png, gif, webp";
            }
        }
    
        if ($this->userStorage->updateProfile($userId, $data)) {
            $_SESSION['flash'] = "Профиль успешно обновлен!";
        } else {
            $_SESSION['flash'] = "Ошибка при обновлении профиля";
        }
    
        header("Location: /avtoservis/profile");
        exit;
    }

    public function getOrdersHistory(): string {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /avtoservis/login");
            exit;
        }

        $data = $this->userStorage->getDataHistory($_SESSION['user_id']);
        return UserTemplate::getHistoryTemplate($data);
    }
    
}