<?php


// Deteksi level folder untuk menentukan path yang benar
$current_dir = dirname($_SERVER['SCRIPT_NAME']);
$path_parts = explode('/', trim($current_dir, '/'));

// Hitung berapa level dari root
$levels_from_root = count($path_parts) - 1; // -1 karena church_scheduling adalah root

// Tentukan path berdasarkan level
if ($levels_from_root <= 1) {
    // File di views/ atau root
    $helpers_path = '../helpers/session_helper.php';
    $assets_path = '../assets/';
    $root_path = '../';
} else {
    // File di subfolder views/
    $helpers_path = '../../helpers/session_helper.php';
    $assets_path = '../../assets/';
    $root_path = '../../';
}

require_once $helpers_path;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan Gedung Gereja Baptis Syalom Karor</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $assets_path; ?>css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $root_path; ?>index.php">Gereja Baptis Syalom Karor</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $root_path; ?>index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $root_path; ?>views/schedule/calendar.php">Jadwal</a>
                    </li>
                    
                    <?php if(isLoggedIn()) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $root_path; ?>views/booking/list.php">Booking Saya</a>
                        </li>
                        
                        <?php if($_SESSION['user_role'] == 'admin') : ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Admin
                                </a>
                                <div class="dropdown-menu" aria-labelledby="adminDropdown">
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>views/admin/dashboard.php">Dashboard</a>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>views/admin/manage_bookings.php">Kelola Booking</a>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>views/admin/manage_users.php">Kelola User</a>
                                </div>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo $_SESSION['user_name']; ?>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?php echo $root_path; ?>views/booking/create.php">Buat Booking</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?php echo $root_path; ?>controllers/AuthController.php?action=logout">Logout</a>
                            </div>
                        </li>
                    <?php else : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $root_path; ?>views/auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $root_path; ?>views/auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
