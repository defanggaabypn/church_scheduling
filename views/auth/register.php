<?php 
require_once __DIR__ . '/../../config/config.php'; 
include_once VIEWS_PATH . 'templates/header.php'; 
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2>Register</h2>
            <p>Silakan isi form untuk mendaftar</p>

            <form action="<?php echo BASE_URL . 'controllers/AuthController.php'; ?>" method="post">
                <div class="form-group">
                    <label for="name">Nama</label>
                    <input type="text" name="name" 
                        class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo isset($data['name']) ? htmlspecialchars($data['name']) : ''; ?>">
                    <span class="invalid-feedback">
                        <?php echo isset($data['name_err']) ? $data['name_err'] : ''; ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" 
                        class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo isset($data['email']) ? htmlspecialchars($data['email']) : ''; ?>">
                    <span class="invalid-feedback">
                        <?php echo isset($data['email_err']) ? $data['email_err'] : ''; ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" 
                        class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo isset($data['password']) ? htmlspecialchars($data['password']) : ''; ?>">
                    <span class="invalid-feedback">
                        <?php echo isset($data['password_err']) ? $data['password_err'] : ''; ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" 
                        class="form-control <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo isset($data['confirm_password']) ? htmlspecialchars($data['confirm_password']) : ''; ?>">
                    <span class="invalid-feedback">
                        <?php echo isset($data['confirm_password_err']) ? $data['confirm_password_err'] : ''; ?>
                    </span>
                </div>
<div class="form-group">
    <label for="role">Role</label>
    <select name="role" class="form-control">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>
</div>

                <div class="row">
                    <div class="col">
                        <input type="submit" name="register" value="Register" class="btn btn-success btn-block">
                    </div>
                    <div class="col">
                        <a href="<?php echo BASE_URL . 'views/auth/login.php'; ?>" class="btn btn-light btn-block">
                            Sudah punya akun? Login
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once VIEWS_PATH . 'templates/footer.php'; ?>
