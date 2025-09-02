<?php
// Hapus session_start() di sini karena sudah ada di session_helper.php
// session_start();

require_once '../config/database.php';
require_once '../models/User.php';
require_once '../helpers/session_helper.php';

class AuthController {
    private $db;
    private $user;
    
    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        
        $this->db = $db;
        $this->user = new User($db);
    }
    
    public function register() {
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Init data
        $data = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'confirm_password' => trim($_POST['confirm_password']),
            'role' => isset($_POST['role']) ? trim($_POST['role']) : 'user', // Ambil role dari form atau default ke 'user'
            'name_err' => '',
            'email_err' => '',
            'password_err' => '',
            'confirm_password_err' => ''
        ];
        
        // Validate name
        if(empty($data['name'])) {
            $data['name_err'] = 'Silakan masukkan nama';
        }
        
        // Validate email
        if(empty($data['email'])) {
            $data['email_err'] = 'Silakan masukkan email';
        } else {
            // Check if email exists
            $this->user->email = $data['email'];
            $result = $this->user->login();
            
            if($result->rowCount() > 0) {
                $data['email_err'] = 'Email sudah terdaftar';
            }
        }
        
        // Validate password
        if(empty($data['password'])) {
            $data['password_err'] = 'Silakan masukkan password';
        } elseif(strlen($data['password']) < 6) {
            $data['password_err'] = 'Password minimal 6 karakter';
        }
        
        // Validate confirm password
        if(empty($data['confirm_password'])) {
            $data['confirm_password_err'] = 'Silakan konfirmasi password';
        } else {
            if($data['password'] != $data['confirm_password']) {
                $data['confirm_password_err'] = 'Password tidak cocok';
            }
        }
        
        // Make sure errors are empty
        if(empty($data['name_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Opsi Keamanan Tambahan: Cek jika role adalah admin
            if ($data['role'] == 'admin') {
                // Cek apakah sudah ada admin
                $adminExists = $this->user->getAdminUser();
                if ($adminExists) {
                    // Jika sudah ada admin, tidak boleh membuat admin baru
                    $data['role'] = 'user';
                    flash('register_error', 'Admin sudah ada, akun dibuat sebagai user');
                }
            }
            
            // Register user
            $this->user->name = $data['name'];
            $this->user->email = $data['email'];
            $this->user->password = $data['password'];
            $this->user->role = $data['role']; // Gunakan role dari form
            
            if($this->user->create()) {
                flash('register_success', 'Anda telah terdaftar dan dapat login');
                redirect('../views/auth/login.php');
            } else {
                die('Terjadi kesalahan');
            }
        } else {
            // Load view with errors
            include_once '../views/auth/register.php';
        }
    }
    
    public function login() {
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Init data
        $data = [
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'email_err' => '',
            'password_err' => ''
        ];
        
        // Validate email
        if(empty($data['email'])) {
            $data['email_err'] = 'Silakan masukkan email';
        }
        
        // Validate password
        if(empty($data['password'])) {
            $data['password_err'] = 'Silakan masukkan password';
        }
        
        // Check for user/email
        $this->user->email = $data['email'];
        $result = $this->user->login();
        
        if($result->rowCount() == 0) {
            $data['email_err'] = 'Email tidak ditemukan';
        }
        
        // Make sure errors are empty
        if(empty($data['email_err']) && empty($data['password_err'])) {
            // Validated
            // Check and set logged in user
            $loggedInUser = $result->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($data['password'], $loggedInUser['password'])) {
                // Create session
                $_SESSION['user_id'] = $loggedInUser['id'];
                $_SESSION['user_name'] = $loggedInUser['name'];
                $_SESSION['user_email'] = $loggedInUser['email'];
                $_SESSION['user_role'] = $loggedInUser['role'];
                
                if($loggedInUser['role'] == 'admin') {
                    redirect('../views/admin/dashboard.php');
                } else {
                    redirect('../views/user/dashboard.php');
                }
            } else {
                $data['password_err'] = 'Password salah';
                include_once '../views/auth/login.php';
            }
        } else {
            // Load view with errors
            include_once '../views/auth/login.php';
        }
    }
    
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        session_destroy();
        redirect('../index.php');
    }
}

// Process form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $auth = new AuthController();
    
    if(isset($_POST['register'])) {
        $auth->register();
    } elseif(isset($_POST['login'])) {
        $auth->login();
    }
} elseif(isset($_GET['action']) && $_GET['action'] == 'logout') {
    $auth = new AuthController();
    $auth->logout();
}
?>
