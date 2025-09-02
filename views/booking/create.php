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
?>

<div class="card card-body bg-light mt-5">
    <h2>Buat Booking Baru</h2>
    <p>Silakan isi form untuk mengajukan permohonan booking</p>
    
    <form action="<?php echo BASE_URL; ?>controllers/BookingController.php" method="post">
        <div class="form-group">
            <label for="activity_type">Jenis Kegiatan</label>
            <select name="activity_type" class="form-control <?php echo (!empty($data['activity_type_err'])) ? 'is-invalid' : ''; ?>">
                <option value="" selected disabled>Pilih Jenis Kegiatan</option>
                <option value="pemuda" <?php echo (isset($data['activity_type']) && $data['activity_type'] == 'pemuda') ? 'selected' : ''; ?>>Pemuda</option>
                <option value="pria" <?php echo (isset($data['activity_type']) && $data['activity_type'] == 'pria') ? 'selected' : ''; ?>>Pria</option>
                <option value="wanita" <?php echo (isset($data['activity_type']) && $data['activity_type'] == 'wanita') ? 'selected' : ''; ?>>Wanita</option>
                <option value="sekolah_minggu" <?php echo (isset($data['activity_type']) && $data['activity_type'] == 'sekolah_minggu') ? 'selected' : ''; ?>>Sekolah Minggu</option>
                <option value="rayon" <?php echo (isset($data['activity_type']) && $data['activity_type'] == 'rayon') ? 'selected' : ''; ?>>Rayon</option>
            </select>
            <span class="invalid-feedback"><?php echo isset($data['activity_type_err']) ? $data['activity_type_err'] : ''; ?></span>
        </div>
        
        <div class="form-group">
            <label for="title">Judul Kegiatan</label>
            <input type="text" name="title" class="form-control <?php echo (!empty($data['title_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo isset($data['title']) ? $data['title'] : ''; ?>">
            <span class="invalid-feedback"><?php echo isset($data['title_err']) ? $data['title_err'] : ''; ?></span>
        </div>
        
        <div class="form-group">
            <label for="description">Deskripsi Kegiatan</label>
            <textarea name="description" class="form-control" rows="3"><?php echo isset($data['description']) ? $data['description'] : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="date">Tanggal</label>
            <input type="date" name="date" class="form-control <?php echo (!empty($data['date_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo isset($data['date']) ? $data['date'] : ''; ?>" min="<?php echo date('Y-m-d', strtotime('+1 week')); ?>">
            <small class="form-text text-muted">Tanggal harus minimal 1 minggu dari sekarang</small>
            <span class="invalid-feedback"><?php echo isset($data['date_err']) ? $data['date_err'] : ''; ?></span>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="start_time">Waktu Mulai</label>
                    <input type="time" name="start_time" class="form-control <?php echo (!empty($data['start_time_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo isset($data['start_time']) ? $data['start_time'] : ''; ?>">
                    <span class="invalid-feedback"><?php echo isset($data['start_time_err']) ? $data['start_time_err'] : ''; ?></span>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="end_time">Waktu Selesai</label>
                    <input type="time" name="end_time" class="form-control <?php echo (!empty($data['end_time_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo isset($data['end_time']) ? $data['end_time'] : ''; ?>">
                    <span class="invalid-feedback"><?php echo isset($data['end_time_err']) ? $data['end_time_err'] : ''; ?></span>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info">
            <strong>Perhatian:</strong>
            <ul>
                <li>Permintaan kegiatan wajib diajukan minimal 1 minggu sebelum kegiatan berlangsung.</li>
                <li>Permintaan tidak dapat diajukan untuk jadwal tetap (TK & PAUD, Ibadah Doa, Ibadah Minggu).</li>
                <li>Jika ada jadwal yang bertabrakan, gedung akan digunakan untuk kegiatan yang pertama kali mengajukan permohonan.</li>
            </ul>
        </div>
        
        <div class="form-group">
            <input type="submit" name="create_booking" value="Ajukan Booking" class="btn btn-primary">
            <a href="<?php echo BASE_URL; ?>views/booking/list.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include_once ROOT_PATH . '/views/templates/footer.php'; ?>
