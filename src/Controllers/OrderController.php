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
        $arr['fio'] = strip_tags($_POST['fio']);
        $arr['address'] = strip_tags($_POST['address']);
        $arr['phone'] = strip_tags($_POST['phone']);
        $arr['email'] = strip_tags($_POST['email']);
        $arr['created_at'] = date("d-m-Y H:i:s");

        // Валидация
        if (!ValidateOrderData::validate($arr)) {
            header("Location: /avtoservis/order");
            exit;
        }

        // Сохраняем данные в сессии
        $_SESSION['order_data'] = $arr;

        // Устанавливаем флэш-сообщение
        $_SESSION['flash'] = "Запись успешно создана!"; // Добавьте это сообщение

        // Переадресация на страницу выбора времени
        header("Location: /avtoservis/select_time");
        exit;
    }

    public function selectTime(): string {
        // Проверяем, есть ли данные в сессии
        if (!isset($_SESSION['order_data'])) {
            header("Location: /avtoservis/order");
            exit; // Если данных нет, перенаправляем обратно
        }

        // Отображаем форму выбора времени
        $orderData = $_SESSION['order_data'];
        return '
            <h1>Выбор времени записи</h1>
            <form action="/avtoservis/confirm_booking" method="POST">
                <label for="date">Выберите дату и время:</label>
                <input type="datetime-local" name="bookingDate" id="date" required>
                <input type="hidden" name="fio" value="' . htmlspecialchars($orderData['fio']) . '">
                <input type="hidden" name="address" value="' . htmlspecialchars($orderData['address']) . '">
                <input type="hidden" name="phone" value="' . htmlspecialchars($orderData['phone']) . '">
                <input type="hidden" name="email" value="' . htmlspecialchars($orderData['email']) . '">
                <button type="submit" class="btn btn-primary">Записаться</button>
            </form>
        ';
    }
}