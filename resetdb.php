<?php
// Konfigurasi koneksi database
$host = 'localhost';
$dbname = 'church_scheduling';
$username = 'root';
$password = '';

try {
    // Membuat koneksi ke database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Menonaktifkan foreign key checks sementara
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Daftar tabel yang akan direset (semua tabel kecuali users)
    $tables_to_reset = ['bookings', 'notifications', 'schedules'];
    
    // Variabel untuk melacak status operasi
    $success = true;
    $messages = [];
    
    // Melakukan TRUNCATE pada setiap tabel
    foreach ($tables_to_reset as $table) {
        $sql = "TRUNCATE TABLE `$table`";
        if ($conn->exec($sql) !== false) {
            $messages[] = "Tabel '$table' berhasil direset";
        } else {
            $success = false;
            $messages[] = "Error saat mereset tabel '$table'";
        }
    }
    
    // Memeriksa user ID yang diperlukan (6, 7, 8)
    $required_user_ids = [6, 7, 8];
    $existing_users = [];
    
    $stmt = $conn->query("SELECT id FROM users WHERE id IN (6, 7, 8)");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existing_users[] = $row['id'];
    }
    
    // Membuat user yang diperlukan jika belum ada
    foreach ($required_user_ids as $user_id) {
        if (!in_array($user_id, $existing_users)) {
            $stmt = $conn->prepare("INSERT INTO users (id, name, email, password, role, created_at) 
                                   VALUES (:id, :name, :email, :password, :role, NOW())");
            
            $name = "Test User " . $user_id;
            $email = "testuser" . $user_id . "@example.com";
            $password = password_hash("password123", PASSWORD_DEFAULT);
            $role = "user";
            
            $stmt->bindParam(':id', $user_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':role', $role);
            
            if ($stmt->execute()) {
                $messages[] = "User ID $user_id berhasil dibuat";
            } else {
                $success = false;
                $messages[] = "Error saat membuat User ID $user_id";
            }
        } else {
            $messages[] = "User ID $user_id sudah ada";
        }
    }
    
    // Mengaktifkan kembali foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Menutup koneksi
    $conn = null;
    
    // Menampilkan hasil
    echo "<html>
    <head>
        <title>Reset Database dan Persiapan Pengujian</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 40px;
                line-height: 1.6;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #f9f9f9;
            }
            h1 {
                color: #333;
                text-align: center;
            }
            .success {
                color: green;
                font-weight: bold;
            }
            .error {
                color: red;
                font-weight: bold;
            }
            .warning {
                color: orange;
                font-weight: bold;
            }
            ul {
                margin-top: 20px;
            }
            .btn {
                display: inline-block;
                padding: 10px 15px;
                background-color: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 20px;
            }
            .btn:hover {
                background-color: #45a049;
            }
            .btn-warning {
                background-color: #ff9800;
            }
            .btn-warning:hover {
                background-color: #e68a00;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Reset Database dan Persiapan Pengujian</h1>";
    
    if ($success) {
        echo "<p class='success'>Operasi berhasil dilakukan!</p>";
    } else {
        echo "<p class='error'>Terjadi kesalahan saat melakukan operasi.</p>";
    }
    
    echo "<h3>Log Operasi:</h3>
          <ul>";
    foreach ($messages as $message) {
        echo "<li>$message</li>";
    }
    echo "</ul>
            <div style='margin-top: 20px;'>
                <a href='javascript:history.back()' class='btn'>Kembali</a>
                <a href='pengujian.php' class='btn btn-warning' style='margin-left: 10px;'>Jalankan Pengujian</a>
            </div>
            <div style='margin-top: 20px;'>
                <p class='warning'>Catatan: Jika masih mengalami error, pastikan file pengujian.php Anda menggunakan user_id yang valid (6, 7, 8, atau 9).</p>
            </div>
        </div>
    </body>
    </html>";
    
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage() . "<br>Line: " . $e->getLine());
}
?>
