<?php 
include_once '../templates/header.php'; 

// Check if user is admin
if(!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
    redirect('../auth/login.php');
}

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
                    <h4>Tambah User Baru</h4>
                </div>
                <div class="card-body">
                    <form action="../../controllers/UserController.php" method="post">
                        <div class="form-group mb-3">
                            <label for="name">Nama</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="confirm_password">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="add_user" class="btn btn-primary">Tambah User</button>
                            <a href="manage_users.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>
