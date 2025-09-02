<?php 
include_once '../templates/header.php'; 

// Check if user is admin
if(!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
    redirect('../auth/login.php');
}

// Perbaiki path ke database.php dan User.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/models/User.php';

$database = new Database();
$db = $database->connect();
$user = new User($db);

$result = $user->read();
$users = $result->fetchAll(PDO::FETCH_ASSOC);

// Flash message
if(isset($_SESSION['user_message'])) {
    echo '<div class="alert alert-' . $_SESSION['user_message_type'] . '">' . $_SESSION['user_message'] . '</div>';
    unset($_SESSION['user_message']);
    unset($_SESSION['user_message_type']);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Kelola User</h1>
    <a href="add_user.php" class="btn btn-success">Tambah User Baru</a>
</div>

<?php if(empty($users)) : ?>
    <div class="alert alert-info">Belum ada user yang terdaftar.</div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user_data) : ?>
                    <tr>
                        <td><?php echo $user_data['name']; ?></td>
                        <td><?php echo $user_data['email']; ?></td>
                        <td>
                            <?php if($user_data['role'] == 'admin') : ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php else : ?>
                                <span class="badge bg-secondary">User</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d-m-Y', strtotime($user_data['created_at'])); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user_data['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            
                            <?php if($user_data['id'] != $_SESSION['user_id']) : ?>
                                <form action="../../controllers/UserController.php" method="post" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus user ini?');">
                                    <input type="hidden" name="id" value="<?php echo $user_data['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include_once '../templates/footer.php'; ?>
