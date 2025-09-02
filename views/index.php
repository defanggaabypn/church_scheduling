<?php include_once 'templates/header.php'; ?>

<div class="jumbotron text-center">
    <h1 class="display-4">Aplikasi Penjadwalan Penggunaan Gedung Gereja Baptis Syalom Karor</h1>
    <p class="lead">Sistem manajemen penjadwalan penggunaan gedung gereja dengan algoritma genetika</p>
    <hr class="my-4">
    <p>Aplikasi ini memudahkan jemaat untuk melakukan booking penggunaan gedung gereja dan membantu pengurus gereja dalam mengelola jadwal kegiatan.</p>
    
    <?php if(!isLoggedIn()) : ?>
        <div class="mt-4">
            <a class="btn btn-primary btn-lg mr-2" href="auth/login.php" role="button">Login</a>
            <a class="btn btn-success btn-lg" href="auth/register.php" role="button">Register</a>
        </div>
    <?php else : ?>
        <div class="mt-4">
            <?php if($_SESSION['user_role'] == 'admin') : ?>
                <a class="btn btn-primary btn-lg" href="admin/dashboard.php" role="button">Dashboard Admin</a>
            <?php else : ?>
                <a class="btn btn-primary btn-lg" href="user/dashboard.php" role="button">Dashboard User</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Tentang Aplikasi</h5>
                </div>
                <div class="card-body">
                    <p>Aplikasi Penjadwalan Penggunaan Gedung Gereja Baptis Syalom Karor adalah sistem berbasis web yang dikembangkan untuk mempermudah proses penjadwalan penggunaan gedung gereja.</p>
                    <p>Dengan menerapkan algoritma genetika, aplikasi ini dapat mengoptimalkan jadwal kegiatan untuk meminimalkan konflik dan memastikan penggunaan gedung yang efisien.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Fitur Utama</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>Booking kegiatan dengan mudah</li>
                        <li>Lihat jadwal kegiatan yang telah terjadwal</li>
                        <li>Notifikasi untuk status booking</li>
                        <li>Optimasi jadwal dengan algoritma genetika</li>
                        <li>Manajemen booking dan jadwal untuk admin</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Ketentuan Penggunaan Gedung Gereja</h5>
                </div>
                <div class="card-body">
                    <h6>1. Permintaan Kegiatan:</h6>
                    <p>Permintaan kegiatan tidak dapat diajukan untuk ibadah dan kegiatan yang sudah memiliki jadwal tetap setiap minggunya, yaitu:</p>
                    <ul>
                        <li>
                            <strong>Kegiatan Belajar Mengajar TK & PAUD</strong><br>
                            Hari: Senin – Jumat<br>
                            Pukul: 08.00 – 12.00 WITA
                        </li>
                        <li>
                            <strong>Ibadah Doa</strong><br>
                            Hari: Sabtu<br>
                            Pukul: 18.00 WITA – Selesai
                        </li>
                        <li>
                            <strong>Ibadah Minggu Pagi</strong><br>
                            Hari: Minggu<br>
                            Pukul: 09.30 WITA – Selesai
                        </li>
                    </ul>
                    
                    <h6>2. Pengajuan Permintaan:</h6>
                    <p>Permintaan kegiatan wajib diajukan minimal 1 minggu sebelum kegiatan berlangsung.</p>
                    
                    <h6>3. Jadwal Bertabrakan:</h6>
                    <p>Jika ada jadwal yang bertabrakan, gedung gereja akan digunakan untuk kegiatan yang pertama kali mengajukan permohonan penggunaan gedung.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'templates/footer.php'; ?>
