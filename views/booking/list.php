<?php
// Pastikan config.php sudah diload
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/config/config.php';
require_once ROOT_PATH . '/helpers/session_helper.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect(BASE_URL . 'views/auth/login.php');
}

// Include header
include_once ROOT_PATH . '/views/templates/header.php';

// Gunakan path absolut untuk BookingController
require_once ROOT_PATH . '/controllers/BookingController.php';
$booking_controller = new BookingController();
$bookings = $booking_controller->getBookings();
?>

<h1>Daftar Booking</h1>
<?php flash('booking_success'); ?>
<?php flash('booking_message'); ?>

<div class="mb-3">
    <a href="<?php echo BASE_URL; ?>views/booking/create.php" class="btn btn-primary">Buat Booking Baru</a>
</div>

<?php if(empty($bookings)) : ?>
    <div class="alert alert-info">Belum ada booking yang diajukan.</div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Jenis Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Urgent</th> <!-- Tambahkan kolom untuk status urgent -->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $booking) : ?>
                    <tr>
                        <td><?php echo $booking['title']; ?></td>
                        <td>
                            <?php 
                                switch($booking['activity_type']) {
                                    case 'pemuda':
                                        echo 'Pemuda';
                                        break;
                                    case 'pria':
                                        echo 'Pria';
                                        break;
                                    case 'wanita':
                                        echo 'Wanita';
                                        break;
                                    case 'sekolah_minggu':
                                        echo 'Sekolah Minggu';
                                        break;
                                    case 'rayon':
                                        echo 'Rayon';
                                        break;
                                    default:
                                        echo $booking['activity_type'];
                                }
                            ?>
                        </td>
                        <td><?php echo date('d-m-Y', strtotime($booking['date'])); ?></td>
                        <td><?php echo date('H:i', strtotime($booking['start_time'])) . ' - ' . date('H:i', strtotime($booking['end_time'])); ?></td>
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
                            <?php if(isset($booking['is_urgent']) && $booking['is_urgent']) : ?>
                                <span class="badge badge-danger">URGENT</span>
                            <?php else : ?>
                                <span class="badge badge-secondary">Normal</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>views/booking/view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">Detail</a>
                            
                            <?php if($booking['status'] == 'pending') : ?>
                                <form class="d-inline" action="<?php echo BASE_URL; ?>controllers/BookingController.php" method="post">
                                    <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="delete_booking" class="btn btn-sm btn-danger" onclick="return confirm('Anda yakin ingin menghapus booking ini?')">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include_once ROOT_PATH . '/views/templates/footer.php'; ?>
