<?php 
include_once '../templates/header.php'; 

// Check if user is logged in
if(!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Check for ID parameter
if(!isset($_GET['id'])) {
    redirect('list.php');
}

// Perbaikan path ke BookingController.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/controllers/BookingController.php';
$booking_controller = new BookingController();
$booking = $booking_controller->getBooking($_GET['id']);
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Detail Booking</h3>
                <?php if(isset($booking['is_urgent']) && $booking['is_urgent']) : ?>
                    <span class="badge badge-danger">URGENT</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <h4 class="card-title"><?php echo $booking['title']; ?></h4>
                
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Status:</div>
                    <div class="col-md-8">
                        <?php if($booking['status'] == 'pending') : ?>
                            <span class="badge badge-warning">Menunggu</span>
                        <?php elseif($booking['status'] == 'approved') : ?>
                            <span class="badge badge-success">Disetujui</span>
                        <?php elseif($booking['status'] == 'rejected') : ?>
                            <span class="badge badge-danger">Ditolak</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if($booking['status'] == 'rejected' && !empty($booking['rejection_reason'])) : ?>
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Alasan Penolakan:</div>
                    <div class="col-md-8"><?php echo $booking['rejection_reason']; ?></div>
                </div>
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Jenis Kegiatan:</div>
                    <div class="col-md-8">
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
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Tanggal:</div>
                    <div class="col-md-8"><?php echo date('d-m-Y', strtotime($booking['date'])); ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Waktu:</div>
                    <div class="col-md-8"><?php echo date('H:i', strtotime($booking['start_time'])) . ' - ' . date('H:i', strtotime($booking['end_time'])); ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Deskripsi:</div>
                    <div class="col-md-8"><?php echo $booking['description'] ? $booking['description'] : '-'; ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Diajukan pada:</div>
                    <div class="col-md-8"><?php echo date('d-m-Y H:i', strtotime($booking['created_at'])); ?></div>
                </div>
                
                <!-- Tambahkan baris untuk menampilkan status urgent -->
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Status Prioritas:</div>
                    <div class="col-md-8">
                        <?php if(isset($booking['is_urgent']) && $booking['is_urgent']) : ?>
                            <span class="badge badge-danger">URGENT</span>
                            <small class="text-muted ml-2">Booking ini memiliki prioritas tinggi dan dapat dipertimbangkan meskipun bertabrakan dengan jadwal tetap.</small>
                        <?php else : ?>
                            <span class="badge badge-secondary">Normal</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if($_SESSION['user_role'] == 'admin' && $booking['status'] == 'pending') : ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <form action="<?php echo BASE_URL; ?>controllers/BookingController.php" method="post" class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" name="update_status" class="btn btn-success">Setujui</button>
                            </form>
                            
                            <!-- Ganti tombol tolak dengan button yang memanggil modal -->
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">Tolak</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="list.php" class="btn btn-secondary">Kembali</a>
                
                <?php if($booking['status'] == 'pending' && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_id'] == $booking['user_id'])) : ?>
                    <form class="d-inline float-right" action="<?php echo BASE_URL; ?>controllers/BookingController.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                        <button type="submit" name="delete_booking" class="btn btn-danger" onclick="return confirm('Anda yakin ingin menghapus booking ini?')">Hapus</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk alasan penolakan -->
<?php if($_SESSION['user_role'] == 'admin' && $booking['status'] == 'pending') : ?>
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectModalLabel">Alasan Penolakan</h5>
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
            <label for="rejection_reason">Alasan Penolakan:</label>
            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
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

<?php include_once '../templates/footer.php'; ?>
