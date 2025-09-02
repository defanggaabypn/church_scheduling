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

// Filter by status if provided
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
if($status_filter) {
    $bookings = array_filter($bookings, function($booking) use ($status_filter) {
        return $booking['status'] == $status_filter;
    });
}
?>

<h1>Kelola Booking</h1>
<?php flash('booking_message'); ?>

<div class="mb-3">
    <div class="btn-group">
        <a href="manage_bookings.php" class="btn btn-outline-primary <?php echo !$status_filter ? 'active' : ''; ?>">Semua</a>
        <a href="manage_bookings.php?status=pending" class="btn btn-outline-warning <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">Menunggu</a>
        <a href="manage_bookings.php?status=approved" class="btn btn-outline-success <?php echo $status_filter == 'approved' ? 'active' : ''; ?>">Disetujui</a>
                <a href="manage_bookings.php?status=rejected" class="btn btn-outline-danger <?php echo $status_filter == 'rejected' ? 'active' : ''; ?>">Ditolak</a>
    </div>
</div>

<?php if(empty($bookings)) : ?>
    <div class="alert alert-info">Tidak ada booking yang ditemukan.</div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Pemohon</th>
                    <th>Jenis Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $booking) : ?>
                    <tr>
                        <td><?php echo $booking['title']; ?></td>
                        <td><?php echo $booking['user_name']; ?></td>
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
                            <a href="../booking/view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">Detail</a>
                            
                            <?php if($booking['status'] == 'pending') : ?>
                                <div class="btn-group">
                                    <form action="<?php echo BASE_URL; ?>controllers/BookingController.php" method="post" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success">Setujui</button>
                                    </form>

                                    <!-- Ganti tombol tolak dengan button yang memanggil modal -->
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal<?php echo $booking['id']; ?>">Tolak</button>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal untuk alasan penolakan -->
<?php foreach($bookings as $booking) : ?>
    <?php if($booking['status'] == 'pending') : ?>
    <div class="modal fade" id="rejectModal<?php echo $booking['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel<?php echo $booking['id']; ?>" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="rejectModalLabel<?php echo $booking['id']; ?>">Alasan Penolakan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="<?php echo BASE_URL; ?>controllers/BookingController.php" method="post">
            <div class="modal-body">
              <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
              <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
              <input type="hidden" name="status" value="rejected">
              <div class="form-group">
                <label for="rejection_reason<?php echo $booking['id']; ?>">Alasan Penolakan:</label>
                <textarea class="form-control" id="rejection_reason<?php echo $booking['id']; ?>" name="rejection_reason" rows="3" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
              <button type="submit" name="update_status" class="btn btn-danger">Tolak Booking</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php include_once '../templates/footer.php'; ?>

