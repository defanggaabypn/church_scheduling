<?php
// Pastikan config.php sudah diload
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/config/config.php';
require_once ROOT_PATH . '/helpers/session_helper.php';
require_once ROOT_PATH . '/controllers/BookingController.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect(BASE_URL . 'views/auth/login.php');
}

// Get booking id from URL
$id = isset($_GET['id']) ? $_GET['id'] : die('ID tidak valid');

// Instantiate BookingController
$booking_controller = new BookingController();
$booking = $booking_controller->getBooking($id);

// Include header
include_once ROOT_PATH . '/views/templates/header.php';
?>

<div class="card card-body bg-light mt-5">
    <h2>Detail Booking</h2>
    
    <div class="row">
        <div class="col-md-6">
            <p><strong>Judul Kegiatan:</strong> <?php echo $booking['title']; ?></p>
            <p><strong>Jenis Kegiatan:</strong> 
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
            </p>
            <p><strong>Tanggal:</strong> <?php echo date('d F Y', strtotime($booking['date'])); ?></p>
            <p><strong>Waktu:</strong> <?php echo substr($booking['start_time'], 0, 5) . ' - ' . substr($booking['end_time'], 0, 5); ?></p>
            
            <!-- Tambahkan penanda URGENT jika booking urgent -->
            <?php if($booking['is_urgent']): ?>
            <p><span class="badge badge-danger">URGENT</span></p>
            <?php endif; ?>
        </div>
        
        <div class="col-md-6">
            <p><strong>Status:</strong> 
                <?php 
                    switch($booking['status']) {
                        case 'pending':
                            echo '<span class="badge badge-warning">Menunggu</span>';
                            break;
                        case 'approved':
                            echo '<span class="badge badge-success">Disetujui</span>';
                            break;
                        case 'rejected':
                            echo '<span class="badge badge-danger">Ditolak</span>';
                            break;
                        default:
                            echo $booking['status'];
                    }
                ?>
            </p>
            
            <?php if($booking['status'] == 'rejected' && !empty($booking['rejection_reason'])): ?>
            <p><strong>Alasan Penolakan:</strong> <?php echo $booking['rejection_reason']; ?></p>
            <?php endif; ?>
            
            <p><strong>Tanggal Pengajuan:</strong> <?php echo date('d F Y H:i', strtotime($booking['created_at'])); ?></p>
        </div>
    </div>
    
    <div class="mt-3">
        <h4>Deskripsi Kegiatan</h4>
        <p><?php echo !empty($booking['description']) ? $booking['description'] : 'Tidak ada deskripsi'; ?></p>
    </div>
    
    <?php if($booking['status'] == 'pending'): ?>
    <div class="mt-4">
        <?php if($_SESSION['user_role'] == 'admin'): ?>
        <!-- Form untuk admin -->
        <form action="<?php echo BASE_URL; ?>controllers/BookingController.php" method="post">
            <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
            
            <div class="form-group">
                <label for="status">Update Status:</label>
                <select name="status" id="status" class="form-control" onchange="showRejectionReason(this.value)">
                    <option value="approved">Setujui</option>
                    <option value="rejected">Tolak</option>
                </select>
            </div>
            
            <div id="rejection_reason_div" style="display: none;">
                <div class="form-group">
                    <label for="rejection_reason">Alasan Penolakan:</label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3"></textarea>
                </div>
            </div>
            
            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
        </form>
        <?php else: ?>
        <!-- Form untuk user -->
        <form action="<?php echo BASE_URL; ?>controllers/BookingController.php" method="post">
            <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
            <button type="submit" name="delete_booking" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus booking ini?')">Hapus Booking</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>views/booking/list.php" class="btn btn-secondary">Kembali ke Daftar Booking</a>
    </div>
</div>

<script>
function showRejectionReason(status) {
    var rejectionDiv = document.getElementById('rejection_reason_div');
    if(status === 'rejected') {
        rejectionDiv.style.display = 'block';
    } else {
        rejectionDiv.style.display = 'none';
    }
}
</script>

<?php include_once ROOT_PATH . '/views/templates/footer.php'; ?>
