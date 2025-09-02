<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/helpers/url_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/helpers/session_helper.php';

class UserController {
    private $user;
    
    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        $this->user = new User($db);
        
        // Handle form submissions
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(isset($_POST['add_user'])) {
                $this->addUser();
            } elseif(isset($_POST['update_user'])) {
                $this->updateUser();
            } elseif(isset($_POST['delete_user'])) {
                $this->deleteUser();
            }
        }
    }
    
    public function addUser() {
        // Check if user is admin
        if(!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
            redirect('/church_scheduling/views/auth/login.php');
            return;
        }
        
        // Validate form data
        if($_POST['password'] != $_POST['confirm_password']) {
            $_SESSION['user_message'] = 'Password dan konfirmasi password tidak cocok';
            $_SESSION['user_message_type'] = 'danger';
            redirect('/church_scheduling/views/admin/add_user.php');
            return;
        }
        
        // Set user properties
        $this->user->name = $_POST['name'];
        $this->user->email = $_POST['email'];
        $this->user->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $this->user->role = $_POST['role'];
        
        // Create user
        if($this->user->create()) {
            $_SESSION['user_message'] = 'User berhasil ditambahkan';
            $_SESSION['user_message_type'] = 'success';
            redirect('/church_scheduling/views/admin/manage_users.php');
        } else {
            $_SESSION['user_message'] = 'Gagal menambahkan user';
            $_SESSION['user_message_type'] = 'danger';
            redirect('/church_scheduling/views/admin/add_user.php');
        }
    }
    
    public function updateUser() {
        // Check if user is admin
        if(!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
            redirect('/church_scheduling/views/auth/login.php');
            return;
        }
        
        // Set user properties
        $this->user->id = $_POST['id'];
        $this->user->name = $_POST['name'];
        $this->user->email = $_POST['email'];
        $this->user->role = $_POST['role'];
        
        // Update user basic info
        if($this->user->update()) {
            // Update password if provided
            if(!empty($_POST['password'])) {
                // Validate password confirmation
                if($_POST['password'] != $_POST['confirm_password']) {
                    $_SESSION['user_message'] = 'Password dan konfirmasi password tidak cocok';
                    $_SESSION['user_message_type'] = 'danger';
                    redirect('/church_scheduling/views/admin/edit_user.php?id=' . $_POST['id']);
                    return;
                }
                
                // Hash password
                $this->user->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                // Update password
                if(!$this->user->update_password()) {
                    $_SESSION['user_message'] = 'Berhasil update user, tetapi gagal update password';
                    $_SESSION['user_message_type'] = 'warning';
                    redirect('/church_scheduling/views/admin/manage_users.php');
                    return;
                }
            }
            
            $_SESSION['user_message'] = 'User berhasil diupdate';
            $_SESSION['user_message_type'] = 'success';
            redirect('/church_scheduling/views/admin/manage_users.php');
        } else {
            $_SESSION['user_message'] = 'Gagal mengupdate user';
            $_SESSION['user_message_type'] = 'danger';
            redirect('/church_scheduling/views/admin/edit_user.php?id=' . $_POST['id']);
        }
    }
    
    public function deleteUser() {
        // Check if user is admin
        if(!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
            redirect('/church_scheduling/views/auth/login.php');
            return;
        }
        
        // Set user id
        $this->user->id = $_POST['id'];
        
        // Delete user
        if($this->user->delete()) {
            $_SESSION['user_message'] = 'User berhasil dihapus';
            $_SESSION['user_message_type'] = 'success';
        } else {
            $_SESSION['user_message'] = 'Gagal menghapus user';
            $_SESSION['user_message_type'] = 'danger';
        }
        
        redirect('/church_scheduling/views/admin/manage_users.php');
    }
}

// Instantiate controller
$init = new UserController();
?>
