<?php 
include_once '../templates/header.php'; 

// Check if user is admin
if(!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
    redirect('../auth/login.php');
}

// Perbaiki path ke ScheduleController.php menggunakan path absolut
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/controllers/ScheduleController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/models/GeneticAlgorithm.php';
$schedule_controller = new ScheduleController();

// Get current month and year
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Get start and end date for the selected month
$start_date = $year . '-' . $month . '-01';
$end_date = date('Y-m-t', strtotime($start_date));

// Check if form is submitted
$optimized_bookings = [];
$debug_info = [];
$ga_process = [];
if(isset($_POST['optimize'])) {
    // Tambahkan debugging
    try {
        $_POST['start_date'] = $start_date;
        $_POST['end_date'] = $end_date;
        
        // Tambahkan debugging untuk melihat apakah ada booking yang perlu dioptimasi
        $debug_info['start_date'] = $start_date;
        $debug_info['end_date'] = $end_date;
        
        // Inisialisasi algoritma genetika
        $database = new Database();
        $db = $database->connect();
        $ga = new GeneticAlgorithm($db);
        $ga->load_data($start_date, $end_date);
        
        // Capture proses algoritma genetika
        $ga_process = $ga->optimize_with_details();
        
        // Panggil metode optimizeSchedule
        $optimized_bookings = $schedule_controller->optimizeSchedule();
        
        // Tambahkan informasi hasil
        $debug_info['optimized_count'] = count($optimized_bookings);
        
    } catch (Exception $e) {
        $debug_info['error'] = $e->getMessage();
    }
}

// Get all bookings for the selected month
$all_bookings = $schedule_controller->getAllBookings($start_date, $end_date);

// Check for schedule conflicts
$schedule_conflicts = $schedule_controller->checkScheduleConflicts($start_date, $end_date);
?>

<h1>Optimasi Jadwal</h1>

<div class="row mb-3">
    <div class="col-md-6">
        <form class="form-inline" method="get">
            <div class="form-group mr-2">
                <select name="month" class="form-control">
                    <?php for($i = 1; $i <= 12; $i++) : ?>
                        <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php echo $month == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group mr-2">
                <select name="year" class="form-control">
                    <?php for($i = date('Y') - 1; $i <= date('Y') + 2; $i++) : ?>
                        <option value="<?php echo $i; ?>" <?php echo $year == $i ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </form>
    </div>
<div class="col-md-6 mb-3 mb-md-0">
    <div class="d-flex justify-content-start justify-content-md-end">
        <form method="post">
            <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
            <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
            <button type="submit" name="optimize" class="btn btn-success">Jalankan Optimasi</button>
        </form>
    </div>
</div>

</div>

<div class="card">
    <div class="card-header">
        <h5>Algoritma Genetika untuk Optimasi Jadwal</h5>
    </div>
    <div class="card-body">
        <p>Algoritma genetika adalah metode pencarian heuristik yang meniru proses seleksi alam. Algoritma ini digunakan untuk mengoptimalkan jadwal penggunaan gedung gereja dengan mempertimbangkan berbagai faktor seperti:</p>
        <ul>
            <li>Meminimalkan konflik jadwal</li>
            <li>Memaksimalkan penggunaan gedung</li>
            <li>Memprioritaskan jadwal tetap</li>
            <li>Mempertimbangkan permohonan booking berdasarkan waktu pengajuan</li>
        </ul>
        
        <p>Klik tombol "Jalankan Optimasi" untuk mengoptimalkan jadwal bulan ini.</p>
    </div>
</div>

<!-- Bagian untuk menampilkan proses algoritma genetika -->
<?php if(!empty($ga_process)) : ?>
<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h5>Proses Algoritma Genetika</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Parameter Algoritma:</h6>
                <ul>
                    <li>Ukuran Populasi: <?php echo $ga_process['parameters']['population_size']; ?></li>
                    <li>Maksimum Generasi: <?php echo $ga_process['parameters']['max_generations']; ?></li>
                    <li>Tingkat Crossover: <?php echo $ga_process['parameters']['crossover_rate']; ?></li>
                    <li>Tingkat Mutasi: <?php echo $ga_process['parameters']['mutation_rate']; ?></li>
                    <li>Jumlah Elitisme: <?php echo $ga_process['parameters']['elitism_count']; ?></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Data Input:</h6>
                <ul>
                    <li>Jumlah Booking Pending: <?php echo $ga_process['data']['pending_count']; ?></li>
                    <li>Jumlah Jadwal Tetap: <?php echo $ga_process['data']['fixed_count']; ?></li>
                    <li>Jumlah Booking Disetujui: <?php echo $ga_process['data']['approved_count']; ?></li>
                </ul>
            </div>
        </div>
        
        <h6 class="mt-4">Evolusi Fitness:</h6>
        <div class="chart-container" style="position: relative; height:300px;">
            <canvas id="fitnessChart"></canvas>
        </div>
        
        <h6 class="mt-4">Visualisasi Proses Evolusi:</h6>
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container" style="position: relative; height:300px;">
                    <canvas id="fitnessDetailChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container" style="position: relative; height:300px;">
                    <canvas id="bookingCountChart"></canvas>
                </div>
            </div>
        </div>
        
        <h6 class="mt-4">Detail Proses Evolusi:</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered evolution-table">
                <thead>
                    <tr>
                        <th>Generasi</th>
                        <th>Fitness Terbaik</th>
                        <th>Fitness Rata-rata</th>
                        <th>Jumlah Booking Optimal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ga_process['generations'] as $gen => $data) : ?>
                        <tr>
                            <td><?php echo $gen + 1; ?></td>
                            <td><?php echo $data['best_fitness']; ?></td>
                            <td><?php echo $data['avg_fitness']; ?></td>
                            <td><?php echo $data['optimal_count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <h6 class="mt-4">Visualisasi Kromosom Terbaik:</h6>
        <div class="chromosome-visualization">
            <?php if(isset($ga_process['best_chromosome'])): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="chromosome-container">
                            <?php foreach($ga_process['best_chromosome'] as $index => $gene): ?>
                                <div class="gene-container">
                                    <span class="gene gene-<?php echo $gene; ?>"><?php echo $gene; ?></span>
                                    <?php if(isset($ga_process['data']['pending_bookings'][$index])): ?>
                                        <div class="gene-tooltip">
                                            <?php echo $ga_process['data']['pending_bookings'][$index]['title']; ?><br>
                                            <?php echo date('d/m/Y', strtotime($ga_process['data']['pending_bookings'][$index]['date'])); ?><br>
                                            <?php echo date('H:i', strtotime($ga_process['data']['pending_bookings'][$index]['start_time'])); ?> - 
                                            <?php echo date('H:i', strtotime($ga_process['data']['pending_bookings'][$index]['end_time'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <div class="legend">
                                <span class="gene-legend gene-1"></span> <span>Booking dimasukkan dalam jadwal</span>
                                <span class="gene-legend gene-0 ml-3"></span> <span>Booking tidak dimasukkan dalam jadwal</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <h6 class="mt-4">Analisis Konflik:</h6>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6>Konflik Terdeteksi:</h6>
                        <p>Jumlah konflik: <?php echo count($schedule_conflicts); ?></p>
                        <p>Algoritma genetika berhasil menyelesaikan konflik dengan memprioritaskan booking yang diajukan lebih awal.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6>Performa Algoritma:</h6>
                        <p>Waktu eksekusi: <?php echo isset($ga_process['execution_time']) ? $ga_process['execution_time'] . ' detik' : 'N/A'; ?></p>
                        <p>Tingkat konvergensi: <?php echo isset($ga_process['convergence_rate']) ? $ga_process['convergence_rate'] . '%' : 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <h6 class="mt-4">Analisis Performa Algoritma:</h6>
        <div class="row">
            <div class="col-md-4">
                <div class="card analysis-card">
                    <div class="card-body">
                        <h6 class="card-title">Waktu Eksekusi</h6>
                        <h2 class="text-center"><?php echo isset($ga_process['execution_time']) ? $ga_process['execution_time'] : 'N/A'; ?> <small>detik</small></h2>
                        <p class="text-muted text-center">Waktu yang diperlukan untuk menjalankan algoritma</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card analysis-card">
                    <div class="card-body">
                        <h6 class="card-title">Tingkat Konvergensi</h6>
                        <h2 class="text-center"><?php echo isset($ga_process['convergence_rate']) ? $ga_process['convergence_rate'] : '0'; ?>%</h2>
                        <div class="progress mt-2 convergence-progress">
                            <div class="progress-bar bg-success" role="progressbar" 
                                style="width: <?php echo isset($ga_process['convergence_rate']) ? $ga_process['convergence_rate'] : '0'; ?>%" 
                                aria-valuenow="<?php echo isset($ga_process['convergence_rate']) ? $ga_process['convergence_rate'] : '0'; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>
                        <p class="text-muted text-center mt-2">Peningkatan fitness dari generasi awal hingga akhir</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card analysis-card">
                    <div class="card-body">
                        <h6 class="card-title">Generasi yang Dijalankan</h6>
                        <h2 class="text-center"><?php echo count($ga_process['generations']); ?> <small>dari <?php echo $ga_process['parameters']['max_generations']; ?></small></h2>
                        <p class="text-muted text-center">Jumlah generasi yang dijalankan sebelum konvergen</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Penjelasan Proses Algoritma Genetika</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>1. Inisialisasi Populasi</h6>
                        <p>Algoritma dimulai dengan membuat <?php echo $ga_process['parameters']['population_size']; ?> kromosom acak. Setiap kromosom merepresentasikan kombinasi booking yang dimasukkan dalam jadwal.</p>
                        
                        <h6>2. Evaluasi Fitness</h6>
                        <p>Setiap kromosom dievaluasi berdasarkan jumlah konflik jadwal. Kromosom dengan konflik yang lebih sedikit memiliki nilai fitness yang lebih tinggi.</p>
                        
                        <h6>3. Seleksi</h6>
                        <p>Algoritma memilih <?php echo $ga_process['parameters']['elitism_count']; ?> kromosom terbaik untuk dipertahankan (elitisme) dan menggunakan metode turnamen untuk memilih kromosom yang akan digunakan untuk reproduksi.</p>
                    </div>
                    <div class="col-md-6">
                        <h6>4. Crossover</h6>
                        <p>Kromosom yang terpilih mengalami persilangan dengan probabilitas <?php echo $ga_process['parameters']['crossover_rate'] * 100; ?>%, menghasilkan kromosom baru yang mewarisi sifat dari kedua induknya.</p>
                        
                        <h6>5. Mutasi</h6>
                        <p>Gen dalam kromosom baru dapat bermutasi dengan probabilitas <?php echo $ga_process['parameters']['mutation_rate'] * 100; ?>%, yang membantu menjaga keragaman populasi.</p>
                        
                        <h6>6. Iterasi</h6>
                        <p>Proses ini diulang hingga <?php echo $ga_process['parameters']['max_generations']; ?> generasi atau hingga konvergensi tercapai.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart untuk fitness
    const ctxFitness = document.getElementById('fitnessChart').getContext('2d');
    
    // Extract data dari PHP
    const generations = <?php echo json_encode(array_keys($ga_process['generations'])); ?>;
    const bestFitness = <?php echo json_encode(array_column($ga_process['generations'], 'best_fitness')); ?>;
    const avgFitness = <?php echo json_encode(array_column($ga_process['generations'], 'avg_fitness')); ?>;
    
    // Buat chart fitness
    const fitnessChart = new Chart(ctxFitness, {
        type: 'line',
        data: {
            labels: generations.map(gen => 'Gen ' + (parseInt(gen) + 1)),
            datasets: [
                {
                    label: 'Fitness Terbaik',
                    data: bestFitness,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'Fitness Rata-rata',
                    data: avgFitness,
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nilai Fitness'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Generasi'
                    }
                }
            }
        }
    });
    
    // Chart untuk fitness detail
    const ctxFitnessDetail = document.getElementById('fitnessDetailChart').getContext('2d');
    
    // Buat chart fitness detail
    const fitnessDetailChart = new Chart(ctxFitnessDetail, {
        type: 'line',
        data: {
            labels: generations.map(gen => 'Gen ' + (parseInt(gen) + 1)),
            datasets: [
                {
                    label: 'Fitness Terbaik',
                    data: bestFitness,
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Fitness Rata-rata',
                    data: avgFitness,
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Evolusi Nilai Fitness',
                    font: {
                        size: 16
                    }
                },
                tooltip: {
                    usePointStyle: true,
                    callbacks: {
                        title: function(context) {
                            return 'Generasi ' + (parseInt(context[0].dataIndex) + 1);
                        }
                    }
                },
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nilai Fitness'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Generasi'
                    },
                    ticks: {
                        maxTicksLimit: 10
                    }
                }
            }
        }
    });
    
    // Chart untuk jumlah booking optimal
    const ctxBookingCount = document.getElementById('bookingCountChart').getContext('2d');
    const optimalCount = <?php echo json_encode(array_column($ga_process['generations'], 'optimal_count')); ?>;
    
    // Buat chart booking count
    const bookingCountChart = new Chart(ctxBookingCount, {
        type: 'bar',
        data: {
            labels: generations.map(gen => 'Gen ' + (parseInt(gen) + 1)),
            datasets: [
                {
                    label: 'Jumlah Booking Optimal',
                    data: optimalCount,
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Jumlah Booking Optimal per Generasi',
                    font: {
                        size: 16
                    }
                },
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Booking'
                    },
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Generasi'
                    },
                    ticks: {
                        maxTicksLimit: 10
                    }
                }
            }
        }
    });
    
    // Tambahkan efek hover pada gen
    const genes = document.querySelectorAll('.gene');
    genes.forEach(gene => {
        gene.addEventListener('mouseover', function() {
            this.classList.add('pulse');
        });
        gene.addEventListener('mouseout', function() {
            this.classList.remove('pulse');
        });
    });
});
</script>

<style>
.chromosome-container {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 10px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 5px;
    justify-content: center;
}

.gene-container {
    position: relative;
    margin: 5px;
}

.gene {
    display: inline-flex;
    width: 40px;
    height: 40px;
    text-align: center;
    line-height: 40px;
    border-radius: 50%;
    font-weight: bold;
    font-size: 18px;
    justify-content: center;
    align-items: center;
    transition: transform 0.3s;
    cursor: pointer;
}

.gene:hover {
    transform: scale(1.2);
}

.gene-1 {
    background-color: #28a745;
    color: white;
}

.gene-0 {
    background-color: #dc3545;
    color: white;
}

.gene-tooltip {
    display: none;
    position: absolute;
    background: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    width: 180px;
    z-index: 100;
    top: -80px;
    left: -70px;
}

.gene-container:hover .gene-tooltip {
    display: block;
}

.gene-tooltip:after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #333 transparent transparent transparent;
}

.legend {
    display: flex;
    align-items: center;
    justify-content: center;
}

.gene-legend {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-right: 5px;
}

/* Animasi untuk visualisasi */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 2s infinite;
}

/* Styling untuk tabel evolusi */
.evolution-table {
    font-size: 0.9rem;
}

.evolution-table th {
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
}

/* Styling untuk kartu analisis */
.analysis-card {
    transition: transform 0.3s;
}

.analysis-card:hover {
    transform: translateY(-5px);
}

/* Styling untuk progress bar konvergensi */
.convergence-progress {
    height: 10px;
}
</style>
<?php endif; ?>

<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h5>Ringkasan Hasil Optimasi</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h1 class="display-4"><?php echo count($optimized_bookings); ?></h1>
                        <p class="lead">Booking Optimal</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h1 class="display-4"><?php echo count($schedule_conflicts); ?></h1>
                        <p class="lead">Konflik Terdeteksi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h1 class="display-4"><?php echo isset($ga_process['best_fitness']) ? $ga_process['best_fitness'] : '0'; ?></h1>
                        <p class="lead">Fitness Terbaik</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i> 
            <?php if(isset($_POST['optimize']) && count($optimized_bookings) > 0): ?>
                Algoritma genetika berhasil mengoptimalkan <?php echo count($optimized_bookings); ?> booking dari total <?php echo isset($ga_process['data']['pending_count']) ? $ga_process['data']['pending_count'] : '0'; ?> booking yang menunggu.
            <?php elseif(isset($_POST['optimize'])): ?>
                Algoritma genetika tidak menemukan solusi optimal karena terlalu banyak konflik jadwal.
            <?php else: ?>
                Klik tombol "Jalankan Optimasi" untuk memulai proses optimasi jadwal.
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bagian untuk menampilkan konflik jadwal -->
<?php if(!empty($schedule_conflicts)) : ?>
<div class="card mt-4">
    <div class="card-header bg-danger text-white">
        <h5>Konflik Jadwal Terdeteksi</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <strong>Perhatian!</strong> Ditemukan <?php echo count($schedule_conflicts); ?> konflik jadwal pada bulan ini.
            Berdasarkan aturan, jadwal yang diajukan lebih awal akan diprioritaskan.
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kegiatan 1</th>
                        <th>Waktu 1</th>
                        <th>Kegiatan 2</th>
                        <th>Waktu 2</th>
                        <th>Rekomendasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($schedule_conflicts as $conflict) : ?>
                    <tr>
                        <td><?php echo date('d-m-Y', strtotime($conflict['date'])); ?></td>
                        <td><?php echo $conflict['booking1']['title']; ?></td>
                        <td><?php echo $conflict['booking1']['time']; ?></td>
                        <td><?php echo $conflict['booking2']['title']; ?></td>
                        <td><?php echo $conflict['booking2']['time']; ?></td>
                        <td>
                            <span class="badge badge-success">
                                <?php echo $conflict['recommendation']['title']; ?> (<?php echo $conflict['recommendation']['time']; ?>)
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(isset($_POST['optimize'])): ?>
    <!-- Debug information -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5>Debug Information</h5>
        </div>
        <div class="card-body">
            <pre><?php print_r($debug_info); ?></pre>
            <p>Rentang Tanggal Optimasi: <?php echo $start_date; ?> sampai <?php echo $end_date; ?></p>
            <p>Jumlah Booking yang Dioptimasi: <?php echo count($optimized_bookings); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if(!empty($optimized_bookings)) : ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5>Hasil Optimasi</h5>
        </div>
        <div class="card-body">
            <p>Algoritma genetika telah menghasilkan jadwal optimal berikut:</p>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Jenis Kegiatan</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($optimized_bookings as $booking) : ?>
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
                                <td><span class="badge badge-warning">Menunggu</span></td>
                                <td>
                                    <form action="/church_scheduling/controllers/BookingController.php" method="post" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success">Setujui</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal<?php echo $booking['id']; ?>">
                                        Tolak
                                    </button>
                                    
                                    <!-- Modal for rejection reason -->
                                    <div class="modal fade" id="rejectModal<?php echo $booking['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel<?php echo $booking['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="rejectModalLabel<?php echo $booking['id']; ?>">Alasan Penolakan</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="/church_scheduling/controllers/BookingController.php" method="post">
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="rejection_reason">Alasan Penolakan:</label>
                                                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                                                        </div>
                                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                        <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <button type="submit" name="update_status" class="btn btn-danger">Tolak Booking</button>
                                                    </div>
                                                </form>
                                            </div>
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
<?php elseif(isset($_POST['optimize'])) : ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5>Hasil Optimasi</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                Tidak ada booking yang perlu dioptimasi untuk periode ini atau semua booking sudah memiliki jadwal yang optimal.
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Tampilkan semua booking -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Semua Jadwal Bulan Ini</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Jenis Kegiatan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all_bookings as $booking) : ?>
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
                                <?php if($booking['status'] == 'approved'): ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php elseif($booking['status'] == 'pending'): ?>
                                    <span class="badge badge-warning">Menunggu</span>
                                <?php elseif($booking['status'] == 'rejected'): ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($booking['status'] == 'pending'): ?>
                                    <form action="/church_scheduling/controllers/BookingController.php" method="post" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success">Setujui</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal<?php echo $booking['id']; ?>">
                                        Tolak
                                    </button>
                                    
                                    <!-- Modal for rejection reason -->
                                    <div class="modal fade" id="rejectModal<?php echo $booking['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel<?php echo $booking['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="rejectModalLabel<?php echo $booking['id']; ?>">Alasan Penolakan</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="/church_scheduling/controllers/BookingController.php" method="post">
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="rejection_reason">Alasan Penolakan:</label>
                                                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                                                        </div>
                                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                        <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <button type="submit" name="update_status" class="btn btn-danger">Tolak Booking</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif($booking['status'] == 'approved'): ?>
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#cancelModal<?php echo $booking['id']; ?>">
                                        Batalkan
                                    </button>
                                    
                                    <!-- Modal for cancellation reason -->
                                    <div class="modal fade" id="cancelModal<?php echo $booking['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel<?php echo $booking['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="cancelModalLabel<?php echo $booking['id']; ?>">Alasan Pembatalan</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="/church_scheduling/controllers/BookingController.php" method="post">
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="rejection_reason">Alasan Pembatalan:</label>
                                                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                                                        </div>
                                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                        <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <button type="submit" name="update_status" class="btn btn-danger">Batalkan Booking</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif($booking['status'] == 'rejected'): ?>
                                    <form action="/church_scheduling/controllers/BookingController.php" method="post" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success">Setujui</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>
