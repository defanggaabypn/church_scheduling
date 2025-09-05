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
    <style>
        /* Perbaikan responsivitas untuk navbar */
        @media (max-width: 991.98px) {
            .navbar-brand {
                max-width: 70%;
                white-space: normal;
                word-wrap: break-word;
                font-size: 1.1rem;
            }
            
            .dropdown-menu {
                border: none;
                background-color: transparent;
                padding-left: 1rem;
            }
            
            .dropdown-item {
                color: rgba(255,255,255,.75) !important;
                padding: 0.25rem 0;
            }
            
            .dropdown-item:hover, .dropdown-item:focus {
                background-color: transparent;
                color: #fff !important;
            }
            
            .dropdown-divider {
                border-color: rgba(255,255,255,.25);
            }
        }
        
        /* Perbaikan responsivitas untuk konten */
        .container {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        /* Perbaikan responsivitas untuk FullCalendar */
        @media (max-width: 767.98px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .fc .fc-toolbar-title {
                font-size: 1.2em;
                margin: 0.5rem 0;
            }
            
            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .fc .fc-button-group {
                display: flex;
                justify-content: center;
            }
            
            .fc .fc-button {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }
        }
        
        /* Perbaikan responsivitas untuk konten kalender */
        @media (max-width: 575.98px) {
            .fc .fc-daygrid-day-number {
                font-size: 0.8rem;
                padding: 2px;
            }
            
            .fc .fc-event-title {
                font-size: 0.75rem;
            }
            
            .fc .fc-list-event-title {
                font-size: 0.9rem;
            }
        }
    </style>
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
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
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
