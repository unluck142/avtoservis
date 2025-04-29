<?php 
namespace App\Services;

use PDO;

class UserDBStorage extends DBStorage implements ISaveStorage
{
    public function saveData(string $name, array $data): bool
    {
        $sql = "INSERT INTO `users`
        (`username`, `email`, `password`, `token`) 
        VALUES (:name, :email, :pass, :token)";

        $sth = $this->connection->prepare($sql);

        $result = $sth->execute( [
            'name' => $data['username'],
            'email' => $data['email'],
            'pass' => $data['password'],
            'token' => $data['token']
        ] );

        return $result;
    }

    public function uniqueEmail(string $email): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT id FROM users WHERE email = ?"
        );
        $stmt->execute([$email]);
        return $stmt->rowCount() === 0; // Упрощение условия
    }

    public function saveVerified($token): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT id FROM users WHERE token = ? 
            AND is_verified = 0");
        $stmt->execute([$token]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            $update = $this->connection->prepare(
                "UPDATE users SET is_verified = 1, 
                token = '' 
                WHERE id = ?");
            $update->execute([$user['id']]);

            return true;
        }
        return false;
    }

    /**
     * Аутентификация пользователя
     */
    public function loginUser ($username, $password): bool {   
        // Поиск пользователя
        $stmt = $this->connection->prepare(
            "SELECT id, username, password FROM users 
            WHERE is_verified = 1 and
            (username = ? OR email = ?)");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        // Проверка записи
        if ($user === false) 
            return false;
        if (!password_verify($password, $user['password']))
            return false;
        
        // Установка переменных сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        return true;
    }

    /* Получает данные пользователя по его id */
    public function getUserData(int $id_user): ?array {
        $stmt = $this->connection->prepare(
            "SELECT id, username, email, address, phone
            FROM users WHERE id = ? ");
        $stmt->execute([$id_user]);

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC); // Возвращаем ассоциативный массив
        }
        return null; // Если пользователя нет, возвращаем null
    }

    /* Получает историю заказов пользователя по его id */
    public function getDataHistory(int $userId): array {
        $query = "SELECT * FROM appointments WHERE user_id = :user_id ORDER BY date DESC, time DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Возвращаем массив всех заказов
    }
    public function saveAppointment(array $data): bool {
        $sql = "INSERT INTO appointments (user_id, date, time) VALUES (:user_id, :date, :time)";
        $stmt = $this->connection->prepare($sql);
        
        // Логирование перед выполнением запроса
        error_log("Сохраняем запись: " . print_r($data, true));
    
        if ($stmt->execute([
            'user_id' => $data['user_id'],
            'date' => $data['date'],
            'time' => $data['time'],
        ])) {
            return true;
        } else {
            // Логирование ошибки выполнения запроса
            error_log("Ошибка выполнения запроса: " . implode(", ", $stmt->errorInfo()));
            return false;
        }
    }
    
}