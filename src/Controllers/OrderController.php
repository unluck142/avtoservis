<?php 
namespace App\Controllers;

use App\Views\OrderTemplate;
use App\Services\ProductFactory;
use App\Services\ValidateOrderData;

class OrderController {
    public function get(): string {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            return $this->create(); // Обработка POST-запроса
        }

        $model = ProductFactory::createProduct();
        $data = $model->getBasketData();
        $all_sum = $model->getAllSum($data);
        
        return OrderTemplate::getOrderTemplate($data, $all_sum);
    }

    public function create(): string {
        $arr = [];
        $arr['fio'] = strip_tags($_POST['fio']??'');
        $arr['address'] = strip_tags($_POST['address']??'');
        $arr['phone'] = strip_tags($_POST['phone']??'');
        $arr['email'] = strip_tags($_POST['email']??'');
        $arr['created_at'] = date("d-m-Y H:i:s");

        // Валидация
        if (!ValidateOrderData::validate($arr)) {
            header("Location: /avtoservis/order");
            exit;
        }

        // Сохраняем данные в сессии
        $_SESSION['order_data'] = $arr;
        $_SESSION['user_data'] = $arr; // Дополнительное сохранение

        // Устанавливаем флэш-сообщение
        $_SESSION['flash'] = "Запись успешно создана!"; // Добавьте это сообщение

        // Переадресация на страницу выбора времени
        header("Location: /avtoservis/select_time");
        exit;
    }

    public function selectTime(): string {
        if (!isset($_SESSION['order_data'])) {
            header("Location: /avtoservis/order");
            exit;
        }
    
        return '
            <form action="/avtoservis/confirm_booking" method="POST">
                <input type="datetime-local" name="bookingDate" required>
                <input type="hidden" name="fio" value="'.htmlspecialchars($_SESSION['order_data']['fio']).'">
                <input type="hidden" name="address" value="'.htmlspecialchars($_SESSION['order_data']['address']).'">
                <input type="hidden" name="phone" value="'.htmlspecialchars($_SESSION['order_data']['phone']).'">
                <input type="hidden" name="email" value="'.htmlspecialchars($_SESSION['order_data']['email']).'">
                <button type="submit">Подтвердить запись</button>
            </form>
        ';
    }
    
    public function confirmBooking(): string {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /avtoservis/login");
            exit;
        }
    
        $orderData = [
            'fio' => $_POST['fio'],
            'address' => $_POST['address'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'],
            'bookingDate' => $_POST['bookingDate']
        ];
    
        $storage = new UserDBStorage();
        if ($storage->saveOrder($orderData)) {
            $_SESSION['flash'] = "Запись успешно создана!";
            header("Location: /avtoservis/history");
            exit;
        } else {
            $_SESSION['flash'] = "Ошибка при создании записи";
            header("Location: /avtoservis/order");
            exit;
        }
    }
}