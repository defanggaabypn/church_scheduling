<?php 
include_once '../templates/header.php'; 

// Check if user is admin
if(!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
    redirect('../auth/login.php');
}

// Check if id is set
if(!isset($_GET['id'])) {
    redirect('manage_users.php');
}

// Perbaiki path ke database.php dan User.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/models/User.php';

$database = new Database();
$db = $database->connect();
$user = new User($db);

$user->id = $_GET['id'];
$user->read_single();

// Flash message
if(isset($_SESSION['user_message'])) {
    echo '<div class="alert alert-' . $_SESSION['user_message_type'] . '">' . $_SESSION['user_message'] . '</div>';
    unset($_SESSION['user_message']);
    unset($_SESSION['user_message_type']);
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>Edit User</h4>
                </div>
                <div class="card-body">
                    <form action="../../controllers/UserController.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $user->id; ?>">
                        <div class="form-group mb-3">
                            <label for="name">Nama</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo $user->name; ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo $user->email; ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Password (Kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="confirm_password">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control">
                                <option value="user" <?php echo $user->role == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $user->role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                            <a href="manage_users.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>
