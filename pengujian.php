<?php
// Koneksi ke database
$host = 'localhost';
$dbname = 'church_scheduling';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi database gagal: " . $e->getMessage();
    die();
}

// Include class GeneticAlgorithm dengan path yang benar
require_once 'models/GeneticAlgorithm.php';

// Tambahkan data booking konflik untuk pengujian
function addTestBookings($conn) {
    // Hapus booking pengujian yang mungkin sudah ada
    $stmt = $conn->prepare("DELETE FROM bookings WHERE title LIKE 'Test Booking%'");
    $stmt->execute();
    
    // Tambahkan beberapa booking untuk pengujian
    $test_bookings = [
        // Booking yang bertabrakan dengan jadwal tetap TK & PAUD
        [
            'user_id' => 9,
            'activity_type' => 'pemuda',
            'title' => 'Test Booking 1 - Konflik dengan TK PAUD',
            'description' => 'Booking ini akan bertabrakan dengan jadwal TK & PAUD',
            'date' => '2025-09-03',
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
            'status' => 'pending'
        ],
        // Booking yang bertabrakan dengan Ibadah Minggu
        [
            'user_id' => 9,
            'activity_type' => 'pemuda',
            'title' => 'Test Booking 2 - Konflik dengan Ibadah Minggu',
            'description' => 'Booking ini akan bertabrakan dengan Ibadah Minggu',
            'date' => '2025-09-07',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'pending'
        ],
        // Booking yang bertabrakan dengan Ibadah Doa
        [
            'user_id' => 7,
            'activity_type' => 'wanita',
            'title' => 'Test Booking 3 - Konflik dengan Ibadah Doa',
            'description' => 'Booking ini akan bertabrakan dengan Ibadah Doa',
            'date' => '2025-09-06',
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'status' => 'pending'
        ],
        // Booking yang bertabrakan dengan booking yang sudah disetujui
        [
            'user_id' => 7,
            'activity_type' => 'sekolah_minggu',
            'title' => 'Test Booking 4 - Konflik dengan Booking Disetujui',
            'description' => 'Booking ini akan bertabrakan dengan booking yang sudah disetujui',
            'date' => '2025-09-21',
            'start_time' => '18:30:00',
            'end_time' => '20:30:00',
            'status' => 'pending'
        ],
        // Booking yang tidak bertabrakan dengan apapun
        [
            'user_id' => 8,
            'activity_type' => 'rayon',
            'title' => 'Test Booking 5 - Tidak Ada Konflik',
            'description' => 'Booking ini tidak memiliki konflik',
            'date' => '2025-09-04',
            'start_time' => '18:00:00',
            'end_time' => '20:00:00',
            'status' => 'pending'
        ],
        // Booking yang tidak bertabrakan dengan apapun
        [
            'user_id' => 8,
            'activity_type' => 'rayon',
            'title' => 'Test Booking 6 - Tidak Ada Konflik',
            'description' => 'Booking ini tidak memiliki konflik',
            'date' => '2025-09-05',
            'start_time' => '18:00:00',
            'end_time' => '20:00:00',
            'status' => 'pending'
        ],
        // Booking yang bertabrakan dengan booking lain yang juga pending
        [
            'user_id' => 9,
            'activity_type' => 'pria',
            'title' => 'Test Booking 7 - Konflik dengan Booking Pending',
            'description' => 'Booking ini akan bertabrakan dengan booking pending lainnya',
            'date' => '2025-09-05',
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'status' => 'pending'
        ],
        // Booking yang membutuhkan slot waktu alternatif
        [
            'user_id' => 7,
            'activity_type' => 'wanita',
            'title' => 'Test Booking 8 - Membutuhkan Slot Alternatif',
            'description' => 'Booking ini membutuhkan slot waktu alternatif',
            'date' => '2025-09-22',
            'start_time' => '18:30:00',
            'end_time' => '20:30:00',
            'status' => 'pending'
        ],
        // Booking yang membutuhkan slot waktu alternatif
        [
            'user_id' => 8,
            'activity_type' => 'pemuda',
            'title' => 'Test Booking 9 - Membutuhkan Slot Alternatif',
            'description' => 'Booking ini membutuhkan slot waktu alternatif',
            'date' => '2025-09-22',
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'status' => 'pending'
        ],
        // Booking yang membutuhkan slot waktu alternatif
        [
            'user_id' => 9,
            'activity_type' => 'sekolah_minggu',
            'title' => 'Test Booking 10 - Membutuhkan Slot Alternatif',
            'description' => 'Booking ini membutuhkan slot waktu alternatif',
            'date' => '2025-09-22',
            'start_time' => '17:00:00',
            'end_time' => '19:00:00',
            'status' => 'pending'
        ]
    ];
    
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, activity_type, title, description, date, start_time, end_time, status, created_at) 
                           VALUES (:user_id, :activity_type, :title, :description, :date, :start_time, :end_time, :status, NOW())");
    
    foreach ($test_bookings as $booking) {
        $stmt->bindParam(':user_id', $booking['user_id']);
        $stmt->bindParam(':activity_type', $booking['activity_type']);
        $stmt->bindParam(':title', $booking['title']);
        $stmt->bindParam(':description', $booking['description']);
        $stmt->bindParam(':date', $booking['date']);
        $stmt->bindParam(':start_time', $booking['start_time']);
        $stmt->bindParam(':end_time', $booking['end_time']);
        $stmt->bindParam(':status', $booking['status']);
        $stmt->execute();
    }
    
    return count($test_bookings);
}

// Fungsi untuk menampilkan hasil optimasi dalam format yang mudah dibaca
function displayOptimizationResults($result) {
    ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Hasil Optimasi Jadwal</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">Parameter Algoritma</div>
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Ukuran Populasi
                                    <span class="badge bg-primary rounded-pill"><?= $result['parameters']['population_size'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Maksimum Generasi
                                    <span class="badge bg-primary rounded-pill"><?= $result['parameters']['max_generations'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Tingkat Crossover
                                    <span class="badge bg-primary rounded-pill"><?= $result['parameters']['crossover_rate'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Tingkat Mutasi
                                    <span class="badge bg-primary rounded-pill"><?= $result['parameters']['mutation_rate'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Jumlah Elitisme
                                    <span class="badge bg-primary rounded-pill"><?= $result['parameters']['elitism_count'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">Informasi Data</div>
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Jumlah Booking Pending
                                    <span class="badge bg-warning rounded-pill"><?= $result['data']['pending_count'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Jumlah Jadwal Tetap
                                    <span class="badge bg-secondary rounded-pill"><?= $result['data']['fixed_count'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Jumlah Booking Disetujui
                                    <span class="badge bg-success rounded-pill"><?= $result['data']['approved_count'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-success text-white">Hasil Optimasi</div>
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Fitness Terbaik
                                    <span class="badge bg-success rounded-pill"><?= $result['best_fitness'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Jumlah Booking Optimal
                                    <span class="badge bg-success rounded-pill"><?= $result['optimal_booking_count'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Jumlah Slot Waktu Alternatif
                                    <span class="badge bg-info rounded-pill"><?= $result['alternative_slots_used'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Waktu Eksekusi
                                    <span class="badge bg-secondary rounded-pill"><?= $result['execution_time'] ?> detik</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Tingkat Konvergensi
                                    <span class="badge bg-primary rounded-pill"><?= $result['convergence_rate'] ?>%</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Total Generasi
                                    <span class="badge bg-dark rounded-pill"><?= $result['total_generations'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h5 class="card-title">Jadwal Optimal</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Jenis Aktivitas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['best_schedule'] as $booking): ?>
                            <tr>
                                <td><?= $booking['id'] ?></td>
                                <td><?= $booking['title'] ?></td>
                                <td><?= $booking['date'] ?></td>
                                <td><?= $booking['scheduled_start_time'] ?> - <?= $booking['scheduled_end_time'] ?></td>
                                <td><span class="badge bg-info"><?= $booking['activity_type'] ?></span></td>
                                <td>
                                    <?php if ($booking['is_alternative']): ?>
                                        <span class="badge bg-warning">Waktu Alternatif</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Waktu Asli</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if (!empty($result['data']['conflicts'])): ?>
            <div class="mt-4">
                <h5 class="card-title">Konflik yang Terdeteksi</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-danger">
                            <tr>
                                <th>Booking</th>
                                <th>Konflik Dengan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['data']['conflicts'] as $conflict): ?>
                            <tr>
                                <td><?= $conflict['event1']['title'] ?> (<?= $conflict['event1']['date'] ?> <?= $conflict['event1']['time'] ?>)</td>
                                <td><?= $conflict['event2']['title'] ?> (<?= $conflict['event2']['date'] ?> <?= $conflict['event2']['time'] ?>)</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-success mt-4">
                <i class="fas fa-check-circle"></i> Tidak Ada Konflik yang Terdeteksi
            </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <h5 class="card-title">Grafik Evolusi Fitness</h5>
                <div class="card">
                    <div class="card-body">
                        <canvas id="fitnessChart" width="400" height="200"></canvas>
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const ctx = document.getElementById('fitnessChart').getContext('2d');
                                
                                // Persiapkan data untuk grafik
                                const generations = <?= json_encode(array_keys($result['generations'])) ?>;
                                const bestFitness = <?= json_encode(array_map(function($gen) { return $gen['best_fitness']; }, $result['generations'])) ?>;
                                const avgFitness = <?= json_encode(array_map(function($gen) { return $gen['avg_fitness']; }, $result['generations'])) ?>;
                                
                                const chart = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: generations.map(g => 'Gen ' + (parseInt(g) + 1)),
                                        datasets: [{
                                            label: 'Fitness Terbaik',
                                            data: bestFitness,
                                            borderColor: 'rgb(75, 192, 192)',
                                            tension: 0.1,
                                            fill: false
                                        }, {
                                            label: 'Fitness Rata-rata',
                                            data: avgFitness,
                                            borderColor: 'rgb(255, 99, 132)',
                                            tension: 0.1,
                                            fill: false
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function displayRecommendations($recommendations, $booking_id) {
    if ($recommendations['success'] && !empty($recommendations['alternative_slots'])) {
        $preferred_slot = $recommendations['alternative_slots'][0];
        ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Rekomendasi Slot Waktu untuk Booking ID <?= $booking_id ?></h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <strong>Slot ID:</strong> <?= $preferred_slot['slot_id'] ?><br>
                    <strong>Waktu:</strong> <?= $preferred_slot['start_time'] ?> - <?= $preferred_slot['end_time'] ?>
                </div>
                
                <h6>Semua Rekomendasi Slot Waktu:</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead class="table-primary">
                            <tr>
                                <th>Slot ID</th>
                                <th>Waktu</th>
                                <th>Skor</th>
                                <th>Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recommendations['alternative_slots'] as $slot): ?>
                            <tr>
                                <td><?= $slot['slot_id'] ?></td>
                                <td><?= $slot['start_time'] ?> - <?= $slot['end_time'] ?></td>
                                <td><?= isset($slot['score']) ? $slot['score'] : 'N/A' ?></td>
                                <td><?= isset($slot['reason']) ? $slot['reason'] : 'Tidak ada konflik' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Tidak ada rekomendasi slot waktu alternatif untuk booking ID <?= $booking_id ?>
        </div>
        <?php
    }
}

function displaySlotStatistics($statistics) {
    ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Statistik Penggunaan Slot Waktu</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Slot ID</th>
                            <th>Rentang Waktu</th>
                            <th>Jumlah Penggunaan</th>
                            <th>Persentase</th>
                            <th>Visualisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statistics as $stat): ?>
                        <tr>
                            <td><?= $stat['slot_id'] ?></td>
                            <td><?= $stat['time_range'] ?></td>
                            <td><?= $stat['usage_count'] ?></td>
                            <td><?= $stat['usage_percentage'] ?>%</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $stat['usage_percentage'] ?>%;" 
                                         aria-valuenow="<?= $stat['usage_percentage'] ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?= $stat['usage_percentage'] ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

function displayScheduleRecommendations($recommendations) {
    ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Rekomendasi Jadwal</h5>
        </div>
        <div class="card-body">
            <div class="accordion" id="recommendationsAccordion">
                <?php $i = 0; foreach ($recommendations as $booking_id => $recommendation): $i++; ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $i ?>">
                        <button class="accordion-button <?= $i > 1 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapse<?= $i ?>" aria-expanded="<?= $i === 1 ? 'true' : 'false' ?>" 
                                aria-controls="collapse<?= $i ?>">
                            <?= $recommendation['booking']['title'] ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $i === 1 ? 'show' : '' ?>" 
                         aria-labelledby="heading<?= $i ?>" data-bs-parent="#recommendationsAccordion">
                        <div class="accordion-body">
                            <?php if (!empty($recommendation['recommended_slots'])): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-info">
                                        <tr>
                                            <th>Slot ID</th>
                                            <th>Waktu</th>
                                            <th>Penggunaan</th>
                                            <th>Perbedaan dari Waktu Asli</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recommendation['recommended_slots'] as $slot): ?>
                                        <tr>
                                            <td><?= $slot['slot_id'] ?></td>
                                            <td><?= $slot['start_time'] ?> - <?= $slot['end_time'] ?></td>
                                            <td><?= $slot['usage_count'] ?></td>
                                            <td><?= $slot['time_difference'] ?> menit</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                Tidak ada rekomendasi slot waktu yang tersedia
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengujian Algoritma Genetika</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding-top: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .test-section {
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
        }
        .section-header {
            background-color: #343a40;
            color: white;
            padding: 15px;
            margin-bottom: 0;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            font-weight: bold;
        }
        .badge {
            font-size: 0.9em;
        }
        .progress {
            height: 20px;
        }
        .accordion-button:not(.collapsed) {
            background-color: #e7f1ff;
        }
        .alert {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h1 class="mb-0"><i class="fas fa-dna me-2"></i>Pengujian Algoritma Genetika untuk Optimasi Jadwal</h1>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php
                            // Tambahkan data pengujian
                            $added_count = addTestBookings($conn);
                            echo "Berhasil menambahkan $added_count booking untuk pengujian";
                            ?>
                        </div>
                        
                        <?php
                        // Inisialisasi algoritma genetika
                        $ga = new GeneticAlgorithm($conn);

                        // Tentukan rentang tanggal untuk optimasi
                        $start_date = '2025-09-01';
                        $end_date = '2025-09-30';

                        // Load data
                        $ga->load_data($start_date, $end_date);
                        ?>
                        
                        <div class="alert alert-primary">
                            <strong><i class="fas fa-calendar-alt me-2"></i>Rentang tanggal:</strong> <?= $start_date ?> hingga <?= $end_date ?>
                        </div>
                    </div>
                </div>
                
                <!-- Pengujian 1: Optimasi standar -->
                <div class="test-section">
                    <h2 class="section-header"><i class="fas fa-flask me-2"></i>Pengujian 1: Optimasi Standar</h2>
                    <?php
                    $result = $ga->optimize_with_details();
                    displayOptimizationResults($result);
                    ?>
                </div>
                
                <!-- Pengujian 2: Optimasi dengan preferensi slot waktu -->
                <div class="test-section">
                    <h2 class="section-header"><i class="fas fa-sliders-h me-2"></i>Pengujian 2: Optimasi dengan Preferensi Slot Waktu</h2>
                                    <?php
                    // Dapatkan rekomendasi slot waktu untuk booking tertentu
                    $booking_id = 8; // Test Booking 8
                    $recommendations = $ga->get_alternative_recommendations($booking_id);
                    
                    displayRecommendations($recommendations, $booking_id);
                    
                    if ($recommendations['success'] && !empty($recommendations['alternative_slots'])) {
                        $preferred_slot_id = $recommendations['alternative_slots'][0]['slot_id'];
                        
                        // Optimasi dengan preferensi slot waktu
                        $preferred_slots = [$booking_id => $preferred_slot_id];
                        $result_with_preferences = $ga->optimize_with_preferred_slots($preferred_slots);
                        
                        // Analisis hasil
                        $analysis = $ga->analyze_chromosome($ga->get_chromosome_visualization($result_with_preferences));
                        ?>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Hasil Optimasi dengan Preferensi Slot Waktu</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Total Booking Terjadwal
                                                <span class="badge bg-success rounded-pill"><?= $analysis['total_scheduled'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Total Konflik
                                                <span class="badge bg-danger rounded-pill"><?= $analysis['total_conflicts'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Fitness
                                                <span class="badge bg-primary rounded-pill"><?= $analysis['fitness'] ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Slot Alternatif yang Digunakan
                                                <span class="badge bg-info rounded-pill"><?= $analysis['alternative_slots_used'] ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h5 class="card-title">Jadwal dengan Preferensi</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Judul</th>
                                                    <th>Tanggal</th>
                                                    <th>Waktu</th>
                                                    <th>Jenis Aktivitas</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($analysis['scheduled_bookings'] as $booking): ?>
                                                <tr <?= ($booking['id'] == $booking_id) ? 'class="table-success"' : '' ?>>
                                                    <td><?= $booking['id'] ?></td>
                                                    <td><?= $booking['title'] ?></td>
                                                    <td><?= $booking['date'] ?></td>
                                                    <td><?= $booking['scheduled_start_time'] ?> - <?= $booking['scheduled_end_time'] ?></td>
                                                    <td><span class="badge bg-info"><?= $booking['activity_type'] ?></span></td>
                                                    <td>
                                                        <?php if ($booking['is_alternative']): ?>
                                                            <span class="badge bg-warning">Waktu Alternatif</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Waktu Asli</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <!-- Pengujian 3: Statistik penggunaan slot waktu -->
                <div class="test-section">
                    <h2 class="section-header"><i class="fas fa-chart-bar me-2"></i>Pengujian 3: Statistik Penggunaan Slot Waktu</h2>
                    <?php
                    $slot_statistics = $ga->get_time_slot_statistics();
                    displaySlotStatistics($slot_statistics);
                    ?>
                </div>
                
                <!-- Pengujian 4: Rekomendasi jadwal berdasarkan pola penggunaan -->
                <div class="test-section">
                    <h2 class="section-header"><i class="fas fa-lightbulb me-2"></i>Pengujian 4: Rekomendasi Jadwal</h2>
                    <?php
                    $schedule_recommendations = $ga->get_schedule_recommendations();
                    displayScheduleRecommendations($schedule_recommendations);
                    ?>
                </div>
                
                <!-- Pengujian 5: Menyimpan hasil optimasi -->
                <div class="test-section">
                    <h2 class="section-header"><i class="fas fa-save me-2"></i>Pengujian 5: Menyimpan Hasil Optimasi</h2>
                    
                    <?php
                    // Modifikasi metode save_optimized_schedule di kelas GeneticAlgorithm
                    // untuk menangani kolom is_alternative
                    
                    // Cek apakah kolom is_alternative sudah ada di tabel bookings
                    $column_exists = false;
                    try {
                        $check_column = $conn->query("SHOW COLUMNS FROM bookings LIKE 'is_alternative'");
                        $column_exists = ($check_column->rowCount() > 0);
                    } catch (PDOException $e) {
                        // Kolom tidak ada
                    }
                    
                    // Tambahkan kolom jika belum ada
                    if (!$column_exists) {
                        try {
                            $conn->exec("ALTER TABLE bookings ADD COLUMN is_alternative TINYINT(1) DEFAULT 0");
                            echo '<div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Berhasil menambahkan kolom is_alternative ke tabel bookings
                            </div>';
                        } catch (PDOException $e) {
                            echo '<div class="alert alert-danger mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Gagal menambahkan kolom: ' . $e->getMessage() . '
                            </div>';
                        }
                    }
                    
                    // Simpan hasil optimasi ke database
                    $save_result = $ga->save_optimized_schedule($result['best_schedule']);
                    
                    if ($save_result['success']) {
                        echo '<div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Berhasil menyimpan ' . $save_result['approved_count'] . ' booking ke database
                        </div>';
                    } else {
                        echo '<div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Gagal menyimpan hasil optimasi: ' . $save_result['message'] . '
                        </div>';
                        
                        // Tambahkan kode untuk memperbaiki metode save_optimized_schedule
                        echo '<div class="card">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0">Perbaikan Metode save_optimized_schedule</h5>
                            </div>
                            <div class="card-body">
                                <p>Perlu memperbaiki metode <code>save_optimized_schedule</code> di kelas <code>GeneticAlgorithm</code> untuk menangani kolom <code>is_alternative</code>.</p>
                                <pre class="bg-light p-3"><code>
public function save_optimized_schedule($schedule) {
    try {
        $approved_count = 0;
        
        foreach ($schedule as $booking) {
            $stmt = $this->conn->prepare("
                UPDATE bookings 
                SET status = :status, 
                    scheduled_start_time = :start_time, 
                    scheduled_end_time = :end_time,
                    is_alternative = :is_alternative
                WHERE id = :id
            ");
            
            $status = "approved";
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":start_time", $booking["scheduled_start_time"]);
            $stmt->bindParam(":end_time", $booking["scheduled_end_time"]);
            $stmt->bindParam(":is_alternative", $booking["is_alternative"], PDO::PARAM_BOOL);
            $stmt->bindParam(":id", $booking["id"]);
            
            if ($stmt->execute()) {
                $approved_count++;
            }
        }
        
        return [
            "success" => true,
            "approved_count" => $approved_count
        ];
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ];
    }
}
                                </code></pre>
                            </div>
                        </div>';
                    }
                    ?>
                </div>
                
                <!-- Pengujian 6: Visualisasi Jadwal -->
                <div class="test-section">
                    <h2 class="section-header"><i class="fas fa-calendar-week me-2"></i>Pengujian 6: Visualisasi Jadwal</h2>
                    
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Kalender Jadwal</h5>
                        </div>
                        <div class="card-body">
                            <div id="calendar"></div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Persiapkan data untuk kalender
                                    const events = <?= json_encode(array_map(function($booking) {
                                        return [
                                            'id' => $booking['id'],
                                            'title' => $booking['title'],
                                            'start' => $booking['date'] . 'T' . $booking['scheduled_start_time'],
                                            'end' => $booking['date'] . 'T' . $booking['scheduled_end_time'],
                                            'backgroundColor' => $booking['is_alternative'] ? '#ffc107' : '#28a745',
                                            'borderColor' => $booking['is_alternative'] ? '#ffc107' : '#28a745',
                                            'extendedProps' => [
                                                'activity_type' => $booking['activity_type'],
                                                'is_alternative' => $booking['is_alternative']
                                            ]
                                        ];
                                    }, $result['best_schedule'])) ?>;
                                    
                                    // Inisialisasi kalender FullCalendar
                                    const calendarEl = document.getElementById('calendar');
                                    const calendar = new FullCalendar.Calendar(calendarEl, {
                                        initialView: 'dayGridMonth',
                                        initialDate: '2025-09-01',
                                        headerToolbar: {
                                            left: 'prev,next today',
                                            center: 'title',
                                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                                        },
                                        events: events,
                                        eventClick: function(info) {
                                            const event = info.event;
                                            const isAlternative = event.extendedProps.is_alternative;
                                            
                                            alert(
                                                'Judul: ' + event.title + '\n' +
                                                'Tanggal: ' + event.start.toLocaleDateString() + '\n' +
                                                'Waktu: ' + event.start.toLocaleTimeString() + ' - ' + event.end.toLocaleTimeString() + '\n' +
                                                'Jenis Aktivitas: ' + event.extendedProps.activity_type + '\n' +
                                                'Status: ' + (isAlternative ? 'Waktu Alternatif' : 'Waktu Asli')
                                            );
                                        }
                                    });
                                    
                                    calendar.render();
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
</body>
</html>

