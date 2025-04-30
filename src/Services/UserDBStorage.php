<?php
namespace App\Services;

use PDO;

class UserDBStorage {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = new PDO('mysql:host=localhost;dbname=is221', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function saveAppointment(array $data): bool {
        $stmt = $this->pdo->prepare("INSERT INTO appointments 
            (user_id, date, time, service_type, comments, status) 
            VALUES (:user_id, :date, :time, :service_type, :comments, 'pending')");
        
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':date' => $data['date'],
            ':time' => $data['time'],
            ':service_type' => $data['service_type'] ?? null,
            ':comments' => $data['comments'] ?? null
        ]);
    }

    public function getUserHistory(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM appointments 
                                   WHERE user_id = :user_id 
                                   ORDER BY date DESC, time DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelAppointment(int $userId, int $appointmentId): bool {
        $stmt = $this->pdo->prepare("UPDATE appointments 
                                   SET status = 'cancelled' 
                                   WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            ':id' => $appointmentId,
            ':user_id' => $userId
        ]);
    }

    public function isSlotAvailable(string $date, string $time): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments 
                                   WHERE date = :date AND time = :time 
                                   AND status != 'cancelled'");
        $stmt->execute([':date' => $date, ':time' => $time]);
        return $stmt->fetchColumn() == 0;
    }

    public function getAvailableSlots(): array {
        // Здесь должна быть логика получения доступных временных слотов
        // Например, можно вернуть фиксированный набор времен
        return [
            '09:00', '10:00', '11:00', '12:00',
            '13:00', '14:00', '15:00', '16:00'
        ];
    }
    public function saveOrder(array $orderData): bool {
        $stmt = $this->pdo->prepare("INSERT INTO bookings 
            (user_id, fio, address, phone, email, bookingDate) 
            VALUES (:user_id, :fio, :address, :phone, :email, :booking_date)");
        
        return $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':fio' => $orderData['fio'],
            ':address' => $orderData['address'],
            ':phone' => $orderData['phone'],
            ':email' => $orderData['email'],
            ':booking_date' => $orderData['bookingDate']
        ]);
    }
    
    public function getOrderHistory(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE user_id = :user_id ORDER BY bookingDate DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUserData(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new \RuntimeException("User with ID {$userId} not found");
        }
        
        return $user;
    }
    public function updateProfile(int $userId, array $data): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET 
            username = :username,
            email = :email,
            address = :address,
            phone = :phone,
            avatar = :avatar
            WHERE id = :id");
        
        return $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':address' => $data['address'],
            ':phone' => $data['phone'],
            ':avatar' => $data['avatar'] ?? null,
            ':id' => $userId
        ]);
    }
}