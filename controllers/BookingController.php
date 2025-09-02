<?php
// Pastikan config.php sudah diload
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/models/Booking.php';
require_once ROOT_PATH . '/models/Schedule.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/helpers/session_helper.php';
require_once ROOT_PATH . '/helpers/notification_helper.php';

class BookingController {
    private $db;
    private $booking;
    private $schedule;
    private $user;
    
    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        
        $this->db = $db;
        $this->booking = new Booking($db);
        $this->schedule = new Schedule($db);
        $this->user = new User($db);
    }
    
    public function create() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect(BASE_URL . 'views/auth/login.php');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Init data
        $data = [
            'user_id' => $_SESSION['user_id'],
            'activity_type' => trim($_POST['activity_type']),
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'date' => trim($_POST['date']),
            'start_time' => trim($_POST['start_time']),
            'end_time' => trim($_POST['end_time']),
            'activity_type_err' => '',
            'title_err' => '',
            'date_err' => '',
            'start_time_err' => '',
            'end_time_err' => ''
        ];
        
        // Validate activity type
        if(empty($data['activity_type'])) {
            $data['activity_type_err'] = 'Silakan pilih jenis kegiatan';
        }
        
        // Validate title
        if(empty($data['title'])) {
            $data['title_err'] = 'Silakan masukkan judul kegiatan';
        }
        
        // Validate date
        if(empty($data['date'])) {
            $data['date_err'] = 'Silakan pilih tanggal';
        } else {
            // Check if date is at least 1 week from now
            $booking_date = new DateTime($data['date']);
            $min_date = new DateTime();
            $min_date->modify('+1 week');
            
            if($booking_date < $min_date) {
                $data['date_err'] = 'Tanggal harus minimal 1 minggu dari sekarang';
            }
        }
        
        // Validate start time
        if(empty($data['start_time'])) {
            $data['start_time_err'] = 'Silakan pilih waktu mulai';
        }
        
        // Validate end time
        if(empty($data['end_time'])) {
            $data['end_time_err'] = 'Silakan pilih waktu selesai';
        } elseif($data['end_time'] <= $data['start_time']) {
            $data['end_time_err'] = 'Waktu selesai harus setelah waktu mulai';
        }
        
        // Check for fixed schedules
        if(!empty($data['date'])) {
            $day_of_week = date('l', strtotime($data['date']));
            
            // Check for TK & PAUD (Monday-Friday, 08:00-12:00)
            if(in_array($day_of_week, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])) {
                $tk_paud_start = '08:00:00';
                $tk_paud_end = '12:00:00';
                
                if(
                    ($data['start_time'] <= $tk_paud_start && $data['end_time'] > $tk_paud_start) ||
                    ($data['start_time'] < $tk_paud_end && $data['end_time'] >= $tk_paud_end) ||
                    ($data['start_time'] >= $tk_paud_start && $data['end_time'] <= $tk_paud_end)
                ) {
                    $data['date_err'] = 'Jadwal bertabrakan dengan kegiatan TK & PAUD (08:00-12:00)';
                }
            }
            
            // Check for Ibadah Doa (Saturday, 18:00)
            if($day_of_week == 'Saturday') {
                $doa_start = '18:00:00';
                $doa_end = '20:00:00'; // Assuming 2 hours
                
                if(
                    ($data['start_time'] <= $doa_start && $data['end_time'] > $doa_start) ||
                    ($data['start_time'] < $doa_end && $data['end_time'] >= $doa_end) ||
                    ($data['start_time'] >= $doa_start && $data['end_time'] <= $doa_end)
                ) {
                    $data['date_err'] = 'Jadwal bertabrakan dengan Ibadah Doa (18:00)';
                }
            }
            
            // Check for Ibadah Minggu (09:30)
            if($day_of_week == 'Sunday') {
                $minggu_start = '09:30:00';
                $minggu_end = '11:30:00'; // Assuming 2 hours
                
                if(
                    ($data['start_time'] <= $minggu_start && $data['end_time'] > $minggu_start) ||
                    ($data['start_time'] < $minggu_end && $data['end_time'] >= $minggu_end) ||
                    ($data['start_time'] >= $minggu_start && $data['end_time'] <= $minggu_end)
                ) {
                    $data['date_err'] = 'Jadwal bertabrakan dengan Ibadah Minggu Pagi (09:30)';
                }
            }
        }
        
        // Make sure errors are empty
        if(empty($data['activity_type_err']) && empty($data['title_err']) && empty($data['date_err']) && empty($data['start_time_err']) && empty($data['end_time_err'])) {
            // Create booking
            $this->booking->user_id = $data['user_id'];
            $this->booking->activity_type = $data['activity_type'];
            $this->booking->title = $data['title'];
            $this->booking->description = $data['description'];
            $this->booking->date = $data['date'];
            $this->booking->start_time = $data['start_time'];
            $this->booking->end_time = $data['end_time'];
            
            if($this->booking->create()) {
                // Send notification to admin
                create_notification('admin', 'Ada permohonan booking baru dari ' . $_SESSION['user_name']);
                
                flash('booking_success', 'Permohonan booking berhasil diajukan');
                redirect(BASE_URL . 'views/booking/list.php');
            } else {
                die('Terjadi kesalahan');
            }
        } else {
            // Load view with errors
            include_once ROOT_PATH . '/views/booking/create.php';
        }
    }
    
    public function getBookings() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect(BASE_URL . 'views/auth/login.php');
        }
        
        if($_SESSION['user_role'] == 'admin') {
            $result = $this->booking->read();
        } else {
            $this->booking->user_id = $_SESSION['user_id'];
            $result = $this->booking->read_by_user();
        }
        
        $bookings = $result->fetchAll(PDO::FETCH_ASSOC);
        
        return $bookings;
    }
    
    public function getBooking($id) {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect(BASE_URL . 'views/auth/login.php');
        }
        
        $this->booking->id = $id;
        
        if($this->booking->read_single()) {
            // Check if user has access to this booking
            if($_SESSION['user_role'] != 'admin' && $this->booking->user_id != $_SESSION['user_id']) {
                redirect(BASE_URL . 'views/booking/list.php');
            }
            
            return [
                'id' => $this->booking->id,
                'user_id' => $this->booking->user_id,
                'activity_type' => $this->booking->activity_type,
                'title' => $this->booking->title,
                'description' => $this->booking->description,
                'date' => $this->booking->date,
                'start_time' => $this->booking->start_time,
                'end_time' => $this->booking->end_time,
                'status' => $this->booking->status,
                'rejection_reason' => $this->booking->rejection_reason,
                'created_at' => $this->booking->created_at
            ];
        } else {
            redirect(BASE_URL . 'views/booking/list.php');
        }
    }
    
    public function updateStatus() {
        // Check if user is admin
        if(!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
            redirect(BASE_URL . 'views/auth/login.php');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        $id = $_POST['id'];
        $status = $_POST['status'];
        $user_id = $_POST['user_id'];
        
        // Tambahkan alasan penolakan jika ada
        $rejection_reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : null;
        
        $this->booking->id = $id;
        $this->booking->status = $status;
        
        // Set alasan penolakan jika status rejected dan alasan diberikan
        if($status == 'rejected' && !empty($rejection_reason)) {
            $this->booking->rejection_reason = $rejection_reason;
        }
        
        if($this->booking->update_status()) {
            // If approved, create schedule
            if($status == 'approved') {
                $this->booking->read_single();
                
                $this->schedule->booking_id = $this->booking->id;
                $this->schedule->activity_type = $this->booking->activity_type;
                $this->schedule->title = $this->booking->title;
                $this->schedule->date = $this->booking->date;
                $this->schedule->start_time = $this->booking->start_time;
                $this->schedule->end_time = $this->booking->end_time;
                $this->schedule->organization = ''; // Get from form if needed
                $this->schedule->is_fixed = 0;
                
                $this->schedule->create();
                
                // Send notification to user
                create_notification($user_id, 'Permohonan booking Anda telah disetujui');
            } else if($status == 'rejected') {
                // Send notification to user with reason if available
                $message = 'Permohonan booking Anda ditolak';
                if(!empty($rejection_reason)) {
                    $message .= '. Alasan: ' . $rejection_reason;
                }
                create_notification($user_id, $message);
            }
            
            flash('booking_message', 'Status booking berhasil diperbarui');
            redirect(BASE_URL . 'views/admin/manage_bookings.php');
        } else {
            die('Terjadi kesalahan');
        }
    }
    
    public function delete() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect(BASE_URL . 'views/auth/login.php');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        $id = $_POST['id'];
        
        $this->booking->id = $id;
        $this->booking->read_single();
        
        // Check if user has access to delete this booking
        if($_SESSION['user_role'] != 'admin' && $this->booking->user_id != $_SESSION['user_id']) {
            redirect(BASE_URL . 'views/booking/list.php');
        }
        
        // Only allow deletion if status is pending
        if($this->booking->status != 'pending') {
            flash('booking_message', 'Hanya booking dengan status pending yang dapat dihapus', 'alert alert-danger');
            redirect(BASE_URL . 'views/booking/list.php');
        }
        
        if($this->booking->delete()) {
            flash('booking_message', 'Booking berhasil dihapus');
            redirect(BASE_URL . 'views/booking/list.php');
        } else {
            die('Terjadi kesalahan');
        }
    }
}

// Process form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_controller = new BookingController();
    
    if(isset($_POST['create_booking'])) {
        $booking_controller->create();
    } elseif(isset($_POST['update_status'])) {
        $booking_controller->updateStatus();
    } elseif(isset($_POST['delete_booking'])) {
        $booking_controller->delete();
    }
}
?>
