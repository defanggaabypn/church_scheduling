<?php 
include_once '../templates/header.php'; 

require_once '../../controllers/ScheduleController.php';
$schedule_controller = new ScheduleController();

// Get current month and year
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Get start and end date for the selected month
$start_date = $year . '-' . $month . '-01';
$end_date = date('Y-m-t', strtotime($start_date));

// Get schedules for the selected month
$schedules = $schedule_controller->getSchedules($start_date, $end_date);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <h1 class="mb-3 mb-md-0">Jadwal Kegiatan</h1>
                <?php if(isLoggedIn()) : ?>
                    <div class="d-flex flex-wrap">
                        <a href="../booking/create.php" class="btn btn-success mb-2 me-2 mr-2">Buat Booking Baru</a>
                        
                        <?php if($_SESSION['user_role'] == 'admin') : ?>
                            <a href="optimize.php" class="btn btn-info mb-2 me-2 mr-2">Optimasi Jadwal</a>
                            
                            <form action="../../controllers/ScheduleController.php" method="post" class="d-inline">
                                <button type="submit" name="create_fixed_schedules" class="btn btn-warning mb-2" onclick="return confirm('Anda yakin ingin membuat jadwal tetap untuk 3 bulan ke depan?')">Buat Jadwal Tetap</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php flash('schedule_message'); ?>

    <div class="row mb-4">
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <form method="get">
                        <div class="row">
                            <div class="col-sm-5 mb-3 mb-sm-0">
                                <label for="month" class="form-label">Bulan:</label>
                                <select name="month" id="month" class="form-select form-control">
                                    <?php for($i = 1; $i <= 12; $i++) : ?>
                                        <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php echo $month == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-sm-4 mb-3 mb-sm-0">
                                <label for="year" class="form-label">Tahun:</label>
                                <select name="year" id="year" class="form-select form-control">
                                    <?php for($i = date('Y') - 1; $i <= date('Y') + 2; $i++) : ?>
                                        <option value="<?php echo $i; ?>" <?php echo $year == $i ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-sm-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 mt-3 mt-sm-0">Tampilkan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Keterangan</h5>
                    <div class="row">
                        <div class="col-sm-4 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="badge-pill me-2 mr-2" style="width: 20px; height: 20px; background-color: #f0ad4e;"></div>
                                <span>Jadwal Tetap</span>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="badge-pill me-2 mr-2" style="width: 20px; height: 20px; background-color: #5bc0de;"></div>
                                <span>Booking Normal</span>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="badge-pill me-2 mr-2" style="width: 20px; height: 20px; background-color: #d9534f;"></div>
                                <span>Booking Urgent</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="calendar" class="fc fc-media-screen fc-direction-ltr"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Perbaikan responsivitas untuk toolbar kalender */
@media (max-width: 767.98px) {
    .fc .fc-toolbar {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .fc .fc-toolbar-title {
        font-size: 1.2em;
        margin: 0.5rem 0;
    }
    
    .fc .fc-toolbar-chunk {
        display: flex;
        justify-content: center;
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .fc .fc-button-group {
        display: flex;
        justify-content: center;
    }
    
    .fc .fc-button {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
}

/* Perbaikan responsivitas untuk konten kalender */
@media (max-width: 575.98px) {
    .fc .fc-daygrid-day-number {
        font-size: 0.8rem;
        padding: 2px;
    }
    
    .fc .fc-event-title {
        font-size: 0.75rem;
    }
    
    .fc .fc-list-event-title {
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',
        initialDate: '<?php echo $year . '-' . $month . '-01'; ?>',
        height: 'auto', // Membuat tinggi kalender menyesuaikan konten
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        views: {
            dayGridMonth: {
                // Opsi khusus untuk tampilan bulan
                titleFormat: { year: 'numeric', month: 'long' }
            },
            timeGridWeek: {
                // Opsi khusus untuk tampilan minggu
                titleFormat: { year: 'numeric', month: 'short', day: '2-digit' }
            },
            listMonth: {
                // Opsi khusus untuk tampilan list
                titleFormat: { year: 'numeric', month: 'long' }
            }
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            list: 'Daftar'
        },
        events: [
            <?php foreach($schedules as $schedule) : ?>
            {
                id: '<?php echo $schedule['id']; ?>',
                title: '<?php echo $schedule['title']; ?><?php echo (isset($schedule['is_urgent']) && $schedule['is_urgent']) ? ' [URGENT]' : ''; ?>',
                start: '<?php echo $schedule['date'] . 'T' . $schedule['start_time']; ?>',
                end: '<?php echo $schedule['date'] . 'T' . $schedule['end_time']; ?>',
                backgroundColor: '<?php 
                    if($schedule['is_fixed']) {
                        echo '#f0ad4e'; // Warna untuk jadwal tetap
                    } else if(isset($schedule['is_urgent']) && $schedule['is_urgent']) {
                        echo '#d9534f'; // Warna merah untuk booking urgent
                    } else {
                        echo '#5bc0de'; // Warna biru untuk booking normal
                    }
                ?>',
                borderColor: '<?php 
                    if($schedule['is_fixed']) {
                        echo '#f0ad4e';
                    } else if(isset($schedule['is_urgent']) && $schedule['is_urgent']) {
                        echo '#d9534f';
                    } else {
                        echo '#5bc0de';
                    }
                ?>',
                textColor: '#fff',
                extendedProps: {
                    activity_type: '<?php echo $schedule['activity_type']; ?>',
                    is_fixed: <?php echo $schedule['is_fixed']; ?>,
                    is_urgent: <?php echo isset($schedule['is_urgent']) && $schedule['is_urgent'] ? 'true' : 'false'; ?>
                }
            },
            <?php endforeach; ?>
        ],
        eventClick: function(info) {
            var event = info.event;
            var id = event.id;
            var title = event.title;
            var start = event.start;
            var end = event.end;
            var activity_type = event.extendedProps.activity_type;
            var is_fixed = event.extendedProps.is_fixed;
            var is_urgent = event.extendedProps.is_urgent;
            
            // Format dates
            var formattedDate = start.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            var formattedStartTime = start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            var formattedEndTime = end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            
            // Create modal content
            var modalContent = '<div class="modal-header">' +
                '<h5 class="modal-title">Detail Kegiatan</h5>' +
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>' +
                '<div class="modal-body">';
                
            // Judul dengan badge urgent jika diperlukan
            if (is_urgent) {
                modalContent += '<div class="d-flex flex-wrap align-items-center mb-3">' +
                    '<h4 class="mb-0 me-2 mr-2">' + title.replace(' [URGENT]', '') + '</h4>' +
                    '<span class="badge badge-danger">URGENT</span>' +
                    '</div>';
            } else {
                modalContent += '<h4 class="mb-3">' + title + '</h4>';
            }
            
            modalContent += '<div class="card mb-3">' +
                '<div class="card-body">' +
                '<div class="row mb-2">' +
                '<div class="col-5 col-sm-4 fw-bold font-weight-bold">Tanggal:</div>' +
                '<div class="col-7 col-sm-8">' + formattedDate + '</div>' +
                '</div>' +
                '<div class="row mb-2">' +
                '<div class="col-5 col-sm-4 fw-bold font-weight-bold">Waktu:</div>' +
                '<div class="col-7 col-sm-8">' + formattedStartTime + ' - ' + formattedEndTime + '</div>' +
                '</div>' +
                '<div class="row mb-2">' +
                '<div class="col-5 col-sm-4 fw-bold font-weight-bold">Jenis Kegiatan:</div>' +
                '<div class="col-7 col-sm-8">' + getActivityTypeName(activity_type) + '</div>' +
                '</div>' +
                '<div class="row mb-2">' +
                '<div class="col-5 col-sm-4 fw-bold font-weight-bold">Status:</div>' +
                '<div class="col-7 col-sm-8">';
            
            if (is_fixed) {
                modalContent += '<span class="badge badge-warning">Jadwal Tetap</span>';
            } else {
                modalContent += '<span class="badge badge-info">Booking</span>';
            }
            
            modalContent += '</div>' +
                '</div>';
            
            if (is_urgent) {
                modalContent += '<div class="row mb-2">' +
                    '<div class="col-5 col-sm-4 fw-bold font-weight-bold">Prioritas:</div>' +
                    '<div class="col-7 col-sm-8">' +
                    '<span class="badge badge-danger">URGENT</span> ' +
                    '<small class="text-muted">Booking ini memiliki prioritas tinggi</small>' +
                    '</div>' +
                    '</div>';
            }
            
            modalContent += '</div>' +
                '</div>';
                
            modalContent += '</div>' +
                '<div class="modal-footer">';
            
            <?php if(isLoggedIn() && $_SESSION['user_role'] == 'admin') : ?>
                if (!is_fixed) {
                    modalContent += '<form action="../../controllers/ScheduleController.php" method="post" class="me-2 mr-2">' +
                        '<input type="hidden" name="id" value="' + id + '">' +
                        '<button type="submit" name="delete_schedule" class="btn btn-danger" onclick="return confirm(\'Anda yakin ingin menghapus jadwal ini?\')">Hapus</button>' +
                        '</form>';
                }
            <?php endif; ?>
            
            modalContent += '<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>' +
                '</div>';
            
            // Show modal
            $('#eventModal .modal-content').html(modalContent);
            $('#eventModal').modal('show');
        },
        windowResize: function(view) {
            if (window.innerWidth < 768) {
                calendar.changeView('listMonth');
            } else {
                calendar.changeView('dayGridMonth');
            }
        }
    });
    
    calendar.render();
    
    // Menambahkan kelas responsif ke tombol kalender setelah kalender dirender
    setTimeout(function() {
        var buttons = document.querySelectorAll('.fc-button');
        buttons.forEach(function(button) {
            button.classList.add('btn-sm');
        });
    }, 100);
    
    function getActivityTypeName(activity_type) {
        switch(activity_type) {
            case 'pemuda':
                return 'Pemuda';
            case 'pria':
                return 'Pria';
            case 'wanita':
                return 'Wanita';
            case 'sekolah_minggu':
                return 'Sekolah Minggu';
            case 'rayon':
                return 'Rayon';
            case 'tk_paud':
                return 'TK & PAUD';
            case 'doa':
                return 'Ibadah Doa';
            case 'minggu':
                return 'Ibadah Minggu';
            default:
                return activity_type;
        }
    }
});
</script>

<!-- Modal for event details -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- Content will be filled by JavaScript -->
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>
