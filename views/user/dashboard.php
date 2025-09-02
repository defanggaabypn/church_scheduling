<?php 
// Pastikan config.php sudah diload
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/config/config.php';
include_once ROOT_PATH . '/views/templates/header.php'; 

// Check if user is logged in
if(!isLoggedIn()) {
    redirect(BASE_URL . 'views/auth/login.php');
}

// Gunakan path absolut untuk BookingController
require_once ROOT_PATH . '/controllers/BookingController.php';

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

<h1>Dashboard User</h1>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Menunggu Persetujuan</h5>
                <p class="card-text display-4"><?php echo $pending_count; ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Booking Disetujui</h5>
                <p class="card-text display-4"><?php echo $approved_count; ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title">Booking Ditolak</h5>
                <p class="card-text display-4"><?php echo $rejected_count; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Booking Terbaru Saya</h5>
            </div>
            <div class="card-body">
                <?php if(empty($recent_bookings)) : ?>
                    <div class="alert alert-info">Anda belum memiliki booking.</div>
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
                                            <a href="<?php echo BASE_URL; ?>views/booking/view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?php echo BASE_URL; ?>views/booking/list.php" class="btn btn-primary">Lihat Semua Booking</a>
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
                    <a href="<?php echo BASE_URL; ?>views/booking/create.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus-circle mr-2"></i> Buat Booking Baru
                    </a>
                    <a href="<?php echo BASE_URL; ?>views/booking/list.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-clipboard-list mr-2"></i> Booking Saya
                    </a>
                    <a href="<?php echo BASE_URL; ?>views/schedule/calendar.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt mr-2"></i> Lihat Jadwal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/templates/footer.php'; ?>
