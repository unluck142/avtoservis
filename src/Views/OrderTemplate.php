<?php
namespace App\Views;

use App\Views\BaseTemplate;

class OrderTemplate extends BaseTemplate {
    public static function getOrderTemplate(array $arr): string {
        $template = parent::getTemplate();
        $title = 'Записаться';
        $content = '<h1 class="mb-5">Создание заказа</h1><h3>Корзина</h3>';
        $all_sum = 0;

        // Проверка и отображение флэш-сообщения
        if (isset($_SESSION['flash'])) {
            $content .= '<div class="alert alert-success">' . $_SESSION['flash'] . '</div>';
            unset($_SESSION['flash']); // Удаляем сообщение после отображения
        }

        if (empty($arr)) {
            $content .= <<<HTML
            <div class="row">
                <div class="col-12">
                    - нет добавленных товаров -
                </div>
            </div>
            HTML;
        } else {
            foreach ($arr as $product) {
                $name = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
                $price = htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8');
                $quantity = htmlspecialchars($product['quantity'], ENT_QUOTES, 'UTF-8');
                $sum = htmlspecialchars($product['sum'], ENT_QUOTES, 'UTF-8');
                $all_sum += $sum;

                $content .= <<<HTML
                <div class="row">
                    <div class="col-6">{$name}</div>
                    <div class="col-2">{$quantity} ед. x {$price} руб.</div>
                    <div class="col-2">{$sum} ₽</div>
                </div>
                HTML;
            }
        }

        // Итоговая сумма
        if ($all_sum > 0) {
            $content .= <<<HTML
            <div class="row">
                <div class="col-6">Итого:</div>
                <div class="col-2">{$all_sum} ₽</div>
            </div>
            <div class="row">
                <div class="col-6"></div>
                <div class="col-6 float-end">
                    <form action="/avtoservis/basket_clear" method="POST">
                        <button type="submit" class="btn btn-secondary mt-3">Очистить корзину</button>
                    </form>
                </div>
            </div>
            HTML;
        }

        // Добавляем HTML-код формы
        $content .= self::getOrderForm();

        // Возвращаем сгенерированный контент
        $resultTemplate = sprintf($template, $title, $content);
        return $resultTemplate;
    }

    private static function getOrderForm(): string {
        $fio = $_SESSION['user_data']['username'] ?? ''; // Автозаполнение из данных пользователя
        return '
            <form action="/avtoservis/order" method="POST">
                <div class="mb-3">
                    <label for="fio" class="form-label">Ваше ФИО:</label>
                    <input type="text" name="fio" id="fio" class="form-control" required>
                    value="'.htmlspecialchars($fio).'" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Адрес доставки:</label>
                    <input type="text" name="address" id="address" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Телефон:</label>
                    <input type="tel" name="phone" id="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail:</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Перейти к выбору времени</button>
            </form>
        ';
    }
}