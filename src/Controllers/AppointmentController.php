<?php
namespace App\Controllers;

use App\Services\UserDBStorage;
use App\Views\UserTemplate;

class AppointmentController {
    public function bookAppointment(): string {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
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

            $serviceDB = new UserDBStorage();
            if ($serviceDB->saveAppointment($data)) {
                $_SESSION['flash'] = "Запись успешно создана!";
                header("Location: /avtoservis/orders");
                exit;
            } else {
                $_SESSION['flash'] = "Ошибка при создании записи.";
            }
        }

        return UserTemplate::getBookingTemplate();
    }

    public function getHistory(): string {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash'] = "Вы должны быть авторизованы для просмотра истории.";
            header("Location: /avtoservis/login");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $serviceDB = new UserDBStorage();
        $appointments = $serviceDB->getDataHistory($userId);

        return UserTemplate::getHistoryTemplate($appointments);
    }
}