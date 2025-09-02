<?php 
include_once '../templates/header.php'; 

// Check if user is admin
if(!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
    redirect('../auth/login.php');
}

// Perbaiki path ke BookingController.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/controllers/BookingController.php';
$booking_controller = new BookingController();
$bookings = $booking_controller->getBookings();

// Count bookings by status
$pending_count = 0;
$approved_count = 0;
$rejected_count = 0;

foreach($bookings as $booking) {
    if($booking['status'] == 'pending') {
        $pending_count++;
    } elseif($booking['status'] == 'approved') {
        $approved_count++;
    } elseif($booking['status'] == 'rejected') {
        $rejected_count++;
    }
}

// Get recent bookings
$recent_bookings = array_slice($bookings, 0, 5);
?>
<h1>Dashboard Admin</h1>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Menunggu Persetujuan</h5>
                <p class="card-text display-4"><?php echo $pending_count; ?></p>
                <a href="manage_bookings.php?status=pending" class="btn btn-light btn-sm">Lihat Detail</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Booking Disetujui</h5>
                <p class="card-text display-4"><?php echo $approved_count; ?></p>
                <a href="manage_bookings.php?status=approved" class="btn btn-light btn-sm">Lihat Detail</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title">Booking Ditolak</h5>
                <p class="card-text display-4"><?php echo $rejected_count; ?></p>
                <a href="manage_bookings.php?status=rejected" class="btn btn-light btn-sm">Lihat Detail</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Booking Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if(empty($recent_bookings)) : ?>
                    <div class="alert alert-info">Belum ada booking yang diajukan.</div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_bookings as $booking) : ?>
                                    <tr>
                                        <td><?php echo $booking['title']; ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($booking['date'])); ?></td>
                                        <td>
                                            <?php if($booking['status'] == 'pending') : ?>
                                                <span class="badge badge-warning">Menunggu</span>
                                            <?php elseif($booking['status'] == 'approved') : ?>
                                                <span class="badge badge-success">Disetujui</span>
                                            <?php elseif($booking['status'] == 'rejected') : ?>
                                                <span class="badge badge-danger">Ditolak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="../booking/view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="manage_bookings.php" class="btn btn-primary">Lihat Semua Booking</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Menu Cepat</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="../schedule/calendar.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt mr-2"></i> Lihat Jadwal
                    </a>
                    <a href="manage_bookings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-clipboard-list mr-2"></i> Kelola Booking
                    </a>
                    <a href="manage_users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users mr-2"></i> Kelola User
                    </a>
                    <a href="../schedule/optimize.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cogs mr-2"></i> Optimasi Jadwal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>
