<?php require_once __DIR__ . '/../../config/config.php'; ?>

<?php include_once VIEWS_PATH . 'templates/header.php'; ?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-body bg-light mt-5">
            <?php flash('register_success'); ?>
            <h2>Login</h2>
            <p>Silakan login untuk mengakses aplikasi</p>
            <form action="<?php echo BASE_URL . 'controllers/AuthController.php'; ?>" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo isset($data['email']) ? $data['email'] : ''; ?>">
                    <span class="invalid-feedback"><?php echo isset($data['email_err']) ? $data['email_err'] : ''; ?></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo isset($data['password']) ? $data['password'] : ''; ?>">
                    <span class="invalid-feedback"><?php echo isset($data['password_err']) ? $data['password_err'] : ''; ?></span>
                </div>
                <div class="row">
                    <div class="col">
                        <input type="submit" name="login" value="Login" class="btn btn-primary btn-block">
                    </div>
                    <div class="col">
                        <a href="register.php" class="btn btn-light btn-block">Belum punya akun? Register</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once VIEWS_PATH . 'templates/footer.php'; ?>
