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

<h1>Jadwal Kegiatan</h1>
<?php flash('schedule_message'); ?>

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
    <div class="col-md-6 text-right">
        <?php if(isLoggedIn()) : ?>
            <a href="../booking/create.php" class="btn btn-success">Buat Booking Baru</a>
            
            <?php if($_SESSION['user_role'] == 'admin') : ?>
                <a href="optimize.php" class="btn btn-info">Optimasi Jadwal</a>
                
<form action="../../controllers/ScheduleController.php" method="post" class="d-inline">
    <button type="submit" name="create_fixed_schedules" class="btn btn-warning" onclick="return confirm('Anda yakin ingin membuat jadwal tetap untuk 3 bulan ke depan?')">Buat Jadwal Tetap</button>
</form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
                initialDate: '<?php echo $year . '-' . $month . '-01'; ?>',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        events: [
            <?php foreach($schedules as $schedule) : ?>
            {
                id: '<?php echo $schedule['id']; ?>',
                title: '<?php echo $schedule['title']; ?>',
                start: '<?php echo $schedule['date'] . 'T' . $schedule['start_time']; ?>',
                end: '<?php echo $schedule['date'] . 'T' . $schedule['end_time']; ?>',
                backgroundColor: '<?php echo $schedule['is_fixed'] ? '#f0ad4e' : '#5bc0de'; ?>',
                borderColor: '<?php echo $schedule['is_fixed'] ? '#f0ad4e' : '#5bc0de'; ?>',
                textColor: '#fff',
                extendedProps: {
                    activity_type: '<?php echo $schedule['activity_type']; ?>',
                    is_fixed: <?php echo $schedule['is_fixed']; ?>
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
                '<div class="modal-body">' +
                '<h4>' + title + '</h4>' +
                '<p><strong>Tanggal:</strong> ' + formattedDate + '</p>' +
                '<p><strong>Waktu:</strong> ' + formattedStartTime + ' - ' + formattedEndTime + '</p>' +
                '<p><strong>Jenis Kegiatan:</strong> ' + getActivityTypeName(activity_type) + '</p>' +
                '<p><strong>Status:</strong> ' + (is_fixed ? 'Jadwal Tetap' : 'Booking') + '</p>' +
                '</div>' +
                '<div class="modal-footer">';
            
            <?php if(isLoggedIn() && $_SESSION['user_role'] == 'admin') : ?>
                if (!is_fixed) {
                    modalContent += '<form action="../controllers/ScheduleController.php" method="post">' +
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
        }
    });
    
    calendar.render();
    
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!-- Content will be filled by JavaScript -->
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>

