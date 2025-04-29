<?php 
namespace App\Views;

use App\Views\BaseTemplate;

class UserTemplate extends BaseTemplate
{
    /*
        Формирование страницы "Вход пользователя"
    */
    public static function getUserTemplate(): string {
        $template = parent::getTemplate();
        $title = 'Вход пользователя';
        $content = <<<CORUSEL
        <main class="row p-5 justify-content-center align-items-center">
            <div class="col-5 bg-light border">
                <h3 class="mb-5">Вход пользователя</h3>
        CORUSEL;
        $content .= self::getFormLogin();
        $content .= "</div></main>";

        $resultTemplate = sprintf($template, $title, $content);
        return $resultTemplate;
    }

    /* 
        Форма входа (логин, пароль)
    */
    public static function getFormLogin(): string {
        $html = <<<FORMA
                <form action="/avtoservis/login" method="POST">
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Логин (имя или емайл):</label>
                        <input type="text" name="username" class="form-control" id="nameInput" required>
                    </div>
                    <div class="mb-3">
                        <label for="passwordInput" class="form-label">Пароль:</label>
                        <input type="password" name="password" class="form-control" id="passwordInput">
                    </div>
                    <button type="submit" class="btn btn-primary mb-3">Войти</button>
                </form>
        FORMA;
        return $html;
    }

    /*
        Формирование страницы "Профиль"
    */
    public static function getProfileTemplate(?array $data): string {
        $template = parent::getTemplate();
        $title = 'Профиль пользователя';
        $content = <<<CORUSEL
        <main class="row p-5 justify-content-center align-items-center">
            <div class="col-8 bg-light border">
                <h3 class="mb-5">Профиль пользователя</h3>
        CORUSEL;
        $content .= self::getFormProfile($data);
        $content .= "</div></main>";

        $resultTemplate = sprintf($template, $title, $content);
        return $resultTemplate;
    }

    /* 
        Форма профиля
    */
    public static function getFormProfile(?array $data): string {
        $fio = (isset($data)) ? $data[1] : "";
        $email = (isset($data)) ? $data[2] : "";
        $address = (isset($data)) ? $data[3] : "";
        $phone = (isset($data)) ? $data[4] : "";

        $html = <<<FORMA
                <form action="/avtoservis/profile" method="POST">
                    <div class="mb-3">
                        <label for="fioInput" class="form-label">Ваше имя (ФИО):</label>
                        <input type="text" name="fio" class="form-control" id="fioInput" disabled value="$fio">
                    </div>
                    <div class="mb-3">
                        <label for="emailInput" class="form-label">Емайл:</label>
                        <input type="email" name="email" class="form-control" id="emailInput" disabled value="$email">
                    </div>
                    <div class="mb-3">
                        <label for="addressInput" class="form-label">Адрес доставки:</label>
                        <input type="text" name="address" class="form-control" id="addressInput" value="$address">
                    </div>
                    <div class="mb-3">
                        <label for="phoneInput" class="form-label">Телефон:</label>
                        <input type="text" name="phone" class="form-control" id="phoneInput" value="$phone">
                    </div>
                    <button type="submit" class="btn btn-primary mb-3">Обновить</button>
                </form>
        FORMA;
        return $html;
    }

    /*
        Формирование страницы "История записей"
    */
    /*
    Формирование страницы "История заказов"
*/
public static function getHistoryTemplate(array $appointments = []): string {
    $template = parent::getTemplate();
    $title = 'История заказов';
    $content = <<<CORUSEL
    <main class="row p-5 justify-content-center align-items-center">
        <div class="col-8 bg-light border">
            <h3 class="mb-5">История заказов</h3>
CORUSEL;

    // Проверяем, есть ли записи
    if (empty($appointments)) {
        $content .= "<p>У вас нет заказов.</p>";
    } else {
        $content .= <<<TABLE
        <table class="table table-striped">
        <thead>
            <tr>    
                <th>Номер заказа</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
TABLE;

        foreach ($appointments as $row) {
            $orderDate = date("d-m-Y H:i", strtotime($row['created_at'])); // Используем правильное поле для даты
            $nameStatus = Config::getStatusName($row['status']);
            $colorStyle = Config::getStatusColor($row['status']);
            $content .= <<<TABLE
            <tr>    
                <td>Заказ #{$row['id']}</td>
                <td>{$orderDate}</td>
                <td>{$row['all_sum']} ₽</td>
                <td class="{$colorStyle}">{$nameStatus}</td>
            </tr>
TABLE;
        }

        $content .= <<<TABLE
        </tbody>
        </table>
TABLE;
    }

    $content .= "</div></main>";
    $resultTemplate = sprintf($template, $title, $content);
    return $resultTemplate;
}
}