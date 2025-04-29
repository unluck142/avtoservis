<?php
namespace App\Controllers;

use App\Views\UserTemplate;
use App\Services\UserDBStorage;

class AppointmentController {
    public function bookAppointment(): string {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверка наличия user_id в сессии
            if (!isset($_SESSION['user_id'])) {
                error_log("Пользователь не авторизован. user_id не установлен.");
                $_SESSION['flash'] = "Вы должны быть авторизованы для записи.";
                header("Location: /avtoservis/login");
                exit;
            }
    
            $data = [
                'user_id' => $_SESSION['user_id'],
                'date' => strip_tags($_POST['date']),
                'time' => strip_tags($_POST['time']),
            ];
    
            // Логирование данных для отладки
            error_log("Данные для записи: " . print_r($data, true));
    
            // Сохранение данных в базе данных
            $serviceDB = new UserDBStorage();
            if ($serviceDB->saveAppointment($data)) {
                $_SESSION['flash'] = "Запись успешно создана!";
                header("Location: /avtoservis/orders"); // Перенаправление на страницу заказов
                exit;
            } else {
                $_SESSION['flash'] = "Ошибка при создании записи.";
            }
        }
    
        return UserTemplate::getBookingTemplate(); // Возвращаем шаблон записи
    }
}