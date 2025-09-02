<?php
// Pastikan config.php sudah diload
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/config/config.php';
require_once ROOT_PATH . '/config/database.php';

function create_notification($user_id, $message) {
    $database = new Database();
    $db = $database->connect();
    
    // Jika user_id adalah 'admin', cari ID admin yang sebenarnya
    if ($user_id === 'admin') {
        $query = 'SELECT id FROM users WHERE role = :role LIMIT 1';
        $stmt = $db->prepare($query);
        $stmt->bindValue(':role', 'admin');
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $user_id = $admin['id'];
        } else {
            // Jika tidak ada admin, tambahkan admin baru
            $query = 'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)';
            $stmt = $db->prepare($query);
            $stmt->bindValue(':name', 'Administrator');
            $stmt->bindValue(':email', 'admin@church.com');
            $stmt->bindValue(':password', password_hash('admin123', PASSWORD_DEFAULT));
            $stmt->bindValue(':role', 'admin');
            $stmt->execute();
            
            $user_id = $db->lastInsertId();
        }
    }
    
    $query = 'INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)';
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);
    
    $stmt->execute();
}

function get_notifications($user_id) {
    $database = new Database();
    $db = $database->connect();
    
    $query = 'SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC';
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':user_id', $user_id);
    
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function mark_notification_as_read($id) {
    $database = new Database();
    $db = $database->connect();
    
    $query = 'UPDATE notifications SET is_read = 1 WHERE id = :id';
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $id);
    
    $stmt->execute();
}
?>
