<?php include_once 'templates/header.php'; ?>

<!-- Pastikan header.php sudah menyertakan CDN Bootstrap dan Font Awesome -->
<!-- Jika tidak, tambahkan kode berikut di bagian atas file -->

<?php
// Cek apakah header.php sudah menyertakan CDN Bootstrap dan Font Awesome
// Jika tidak, tambahkan di sini
if (!defined('BOOTSTRAP_LOADED')) {
    echo '
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    ';
}
?>

<!-- Hero Section dengan Background Image -->
<div class="hero-section position-relative">
    <div class="overlay"></div>
    <div class="container position-relative z-index-1">
        <div class="row min-vh-75 align-items-center py-5">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-4 fw-bold mb-3 animate__animated animate__fadeInDown">
                    <i class="fas fa-church me-2"></i> Gereja Baptis Syalom Karor
                </h1>
                <p class="lead mb-4 animate__animated animate__fadeIn animate__delay-1s">
                    Sistem Penjadwalan Penggunaan Gedung Gereja dengan Algoritma Genetika
                </p>
                <hr class="my-4 bg-light opacity-25 w-50 mx-auto">
                <p class="mb-5 animate__animated animate__fadeIn animate__delay-1s">
                    Memudahkan jemaat untuk melakukan booking penggunaan gedung gereja dan membantu pengurus dalam mengelola jadwal kegiatan.
                </p>
                
                <?php if(!isLoggedIn()) : ?>
                    <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                        <a class="btn btn-primary btn-lg px-4 me-2 shadow-sm" href="auth/login.php" role="button">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a class="btn btn-outline-light btn-lg px-4 shadow-sm" href="auth/register.php" role="button">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </a>
                    </div>
                <?php else : ?>
                    <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                        <?php if($_SESSION['user_role'] == 'admin') : ?>
                            <a class="btn btn-primary btn-lg px-4 shadow-sm" href="admin/dashboard.php" role="button">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin
                            </a>
                        <?php else : ?>
                            <a class="btn btn-primary btn-lg px-4 shadow-sm" href="user/dashboard.php" role="button">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard User
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Wave Divider -->
    <div class="wave-divider">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 100">
            <path fill="#ffffff" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,100L1360,100C1280,100,1120,100,960,100C800,100,640,100,480,100C320,100,160,100,80,100L0,100Z"></path>
        </svg>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <!-- Feature Cards -->
    <div class="text-center mb-5">
        <h2 class="fw-bold">Fitur Utama Aplikasi</h2>
        <p class="text-muted">Nikmati kemudahan pengelolaan jadwal dengan fitur-fitur unggulan kami</p>
    </div>
    
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm hover-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h5 class="card-title">Booking Mudah</h5>
                    <p class="card-text text-muted">Ajukan permintaan penggunaan gedung dengan mudah dan cepat melalui sistem online.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm hover-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon bg-success bg-gradient text-white rounded-circle mb-3">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5 class="card-title">Lihat Jadwal</h5>
                    <p class="card-text text-muted">Pantau jadwal kegiatan gereja secara real-time dengan tampilan kalender yang interaktif.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm hover-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon bg-info bg-gradient text-white rounded-circle mb-3">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h5 class="card-title">Notifikasi</h5>
                    <p class="card-text text-muted">Dapatkan notifikasi status booking dan pengingat kegiatan yang akan datang.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- About Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <img src="http://localhost/church_scheduling/assets/img/gereja.PNG" alt="Church Illustration" class="img-fluid rounded shadow-sm">
        </div>
        <div class="col-lg-6">
            <h2 class="fw-bold mb-3">Tentang Aplikasi</h2>
            <p class="lead">Solusi modern untuk pengelolaan jadwal gereja</p>
            <p>Aplikasi Penjadwalan Penggunaan Gedung Gereja Baptis Syalom Karor adalah sistem berbasis web yang dikembangkan untuk mempermudah proses penjadwalan penggunaan gedung gereja.</p>
            <p>Dengan menerapkan algoritma genetika, aplikasi ini dapat mengoptimalkan jadwal kegiatan untuk meminimalkan konflik dan memastikan penggunaan gedung yang efisien.</p>
            <div class="d-flex mt-4">
                <a href="schedule/calendar.php" class="btn btn-primary">
                    <i class="far fa-calendar-alt me-1"></i> Lihat Jadwal
                </a>
            </div>
        </div>
    </div>
    
    <!-- Ketentuan Penggunaan -->
    <div class="card border-0 shadow-sm mb-5 overflow-hidden">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i> Ketentuan Penggunaan Gedung Gereja</h5>
            <span class="badge bg-light text-primary">Penting</span>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-info d-flex align-items-center mb-4">
                <i class="fas fa-info-circle fs-4 me-3"></i>
                <div>Harap membaca ketentuan berikut dengan seksama sebelum mengajukan permintaan penggunaan gedung.</div>
            </div>
            
            <div class="accordion" id="ketentuanAccordion">
                <!-- Permintaan Kegiatan -->
                <div class="accordion-item border mb-3">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <i class="fas fa-calendar-check me-2 text-primary"></i> 1. Permintaan Kegiatan
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#ketentuanAccordion">
                        <div class="accordion-body">
                            <p>Permintaan kegiatan tidak dapat diajukan untuk ibadah dan kegiatan yang sudah memiliki jadwal tetap setiap minggunya, kecuali ditandai sebagai <span class="badge bg-danger">URGENT</span>.</p>
                            
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kegiatan</th>
                                            <th>Hari</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Kegiatan Belajar Mengajar TK & PAUD</strong></td>
                                            <td>Senin – Jumat</td>
                                            <td>08.00 – 12.00 WITA</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ibadah Doa</strong></td>
                                            <td>Sabtu</td>
                                            <td>18.00 WITA – Selesai</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ibadah Minggu Pagi</strong></td>
                                            <td>Minggu</td>
                                            <td>09.30 WITA – Selesai</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="alert alert-danger d-flex mb-0">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Status URGENT:</h6>
                                    <p class="mb-0">Untuk mengajukan permintaan pada jadwal tetap, permintaan harus ditandai sebagai URGENT dan akan memerlukan persetujuan khusus dari pengurus gereja.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pengajuan Permintaan -->
                <div class="accordion-item border mb-3">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="fas fa-file-alt me-2 text-primary"></i> 2. Pengajuan Permintaan
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#ketentuanAccordion">
                        <div class="accordion-body">
                            <div class="alert alert-warning d-flex align-items-center mb-0">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                </div>
                                <div>
                                    <p class="mb-0"><strong>Penting:</strong> Permintaan kegiatan wajib diajukan minimal 1 minggu sebelum kegiatan berlangsung.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Jadwal Bertabrakan -->
                <div class="accordion-item border">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            <i class="fas fa-random me-2 text-primary"></i> 3. Jadwal Bertabrakan
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#ketentuanAccordion">
                        <div class="accordion-body">
                            <div class="alert alert-secondary mb-0">
                                <p class="mb-2">Jika ada jadwal yang bertabrakan, gedung gereja akan digunakan untuk kegiatan yang <strong>pertama kali</strong> mengajukan permohonan penggunaan gedung.</p>
                                <p class="mb-0 fst-italic"><small>Catatan: Permintaan dengan status <span class="badge bg-danger">URGENT</span> akan dipertimbangkan secara khusus.</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 bg-primary text-white text-center shadow p-4">
                <div class="card-body py-5">
                    <h3 class="fw-bold mb-3">Siap untuk mengajukan permintaan?</h3>
                    <p class="lead mb-4">Ajukan permintaan penggunaan gedung gereja sekarang untuk kegiatan Anda.</p>
                    <?php if(isLoggedIn()) : ?>
                        <a href="user/create_request.php" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-plus-circle me-2"></i> Buat Permintaan
                        </a>
                    <?php else : ?>
                        <a href="auth/login.php" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-sign-in-alt me-2"></i> Login untuk Mengajukan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- CSS Tambahan -->
<style>
    /* Hero Section */
    .hero-section {
        background: url('http://localhost/church_scheduling/assets/img/gereja.PNG') no-repeat center center;
        background-size: cover;
        color: white;
        position: relative;
    }
    
    /* Jika gambar tidak ada, gunakan warna latar belakang */
    .hero-section {
        background-color: #343a40;
    }
    
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
    }
    
    .min-vh-75 {
        min-height: 75vh;
    }
    
    .z-index-1 {
        z-index: 1;
    }
    
    /* Wave Divider */
    .wave-divider {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        overflow: hidden;
        line-height: 0;
    }
    
    /* Feature Icons */
    .feature-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4rem;
        height: 4rem;
        font-size: 2rem;
    }
    
    /* Hover Effects */
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }
    
    /* Accordion Styles */
    .accordion-button:not(.collapsed) {
        background-color: rgba(13, 110, 253, 0.05);
        color: var(--bs-primary);
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(13, 110, 253, 0.25);
    }
    
    /* Animation Classes */
    .animate__animated {
        animation-duration: 1s;
    }
    
    .animate__delay-1s {
        animation-delay: 0.5s;
    }
    
    .animate__delay-2s {
        animation-delay: 1s;
    }
</style>

<!-- Script untuk Bootstrap dan Animasi -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tambahkan class untuk animasi saat scroll
        const animateOnScroll = function() {
            const elements = document.querySelectorAll('.card, .accordion-item, h2, .feature-icon');
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if (elementPosition < screenPosition) {
                    element.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        };
        
        // Jalankan saat halaman dimuat
        animateOnScroll();
        
        // Jalankan saat scroll
        window.addEventListener('scroll', animateOnScroll);
    });
</script>

<?php include_once 'templates/footer.php'; ?>
