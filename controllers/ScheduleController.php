<?php
// Pastikan config.php sudah diload
require_once $_SERVER['DOCUMENT_ROOT'] . '/church_scheduling/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/models/Booking.php';
require_once ROOT_PATH . '/models/Schedule.php';

class ScheduleController {
    private $db;
    private $booking;
    private $schedule;
    
    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        
        $this->db = $db;
        $this->booking = new Booking($db);
        $this->schedule = new Schedule($db);
    }
    
    // Get all schedules
    public function getSchedules($start_date = null, $end_date = null) {
        if ($start_date && $end_date) {
            $schedules = $this->schedule->read_by_date_range($start_date, $end_date);
            
            // Tambahkan informasi is_urgent ke dalam data jadwal
            foreach ($schedules as &$schedule) {
                // Jika ini adalah jadwal dari booking (bukan jadwal tetap)
                if (!$schedule['is_fixed'] && isset($schedule['booking_id'])) {
                    $booking_id = $schedule['booking_id'];
                    // Ambil status urgent dari tabel bookings
                    $query = "SELECT is_urgent FROM bookings WHERE id = :booking_id";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':booking_id', $booking_id);
                    $stmt->execute();
                    
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result && isset($result['is_urgent'])) {
                        $schedule['is_urgent'] = (bool)$result['is_urgent'];
                    } else {
                        $schedule['is_urgent'] = false;
                    }
                } else {
                    $schedule['is_urgent'] = false;
                }
            }
            
            return $schedules;
        } else {
            $schedules = $this->schedule->read();
            
            // Tambahkan informasi is_urgent ke dalam data jadwal
            foreach ($schedules as &$schedule) {
                // Jika ini adalah jadwal dari booking (bukan jadwal tetap)
                if (!$schedule['is_fixed'] && isset($schedule['booking_id'])) {
                    $booking_id = $schedule['booking_id'];
                    // Ambil status urgent dari tabel bookings
                    $query = "SELECT is_urgent FROM bookings WHERE id = :booking_id";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':booking_id', $booking_id);
                    $stmt->execute();
                    
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result && isset($result['is_urgent'])) {
                        $schedule['is_urgent'] = (bool)$result['is_urgent'];
                    } else {
                        $schedule['is_urgent'] = false;
                    }
                } else {
                    $schedule['is_urgent'] = false;
                }
            }
            
            return $schedules;
        }
    }
    
    // Get all bookings for a specific period
    public function getAllBookings($start_date, $end_date) {
        $query = "SELECT b.*, u.name as user_name 
                  FROM bookings b
                  LEFT JOIN users u ON b.user_id = u.id
                  WHERE b.date BETWEEN :start_date AND :end_date
                  ORDER BY b.created_at ASC"; // Diurutkan berdasarkan waktu pengajuan
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Check for schedule conflicts
    public function checkScheduleConflicts($start_date, $end_date) {
        $bookings = $this->getAllBookings($start_date, $end_date);
        $conflicts = [];
        
        // Group bookings by date
        $bookings_by_date = [];
        foreach($bookings as $booking) {
            // Hanya periksa booking yang statusnya pending atau approved
            if($booking['status'] == 'pending' || $booking['status'] == 'approved') {
                $date = $booking['date'];
                if(!isset($bookings_by_date[$date])) {
                    $bookings_by_date[$date] = [];
                }
                $bookings_by_date[$date][] = $booking;
            }
        }
        
        // Check for conflicts on each date
        foreach($bookings_by_date as $date => $day_bookings) {
            // Sort by is_urgent first (urgent bookings get priority), then by created_at
            usort($day_bookings, function($a, $b) {
                // Jika salah satu booking adalah urgent dan yang lain tidak
                if (isset($a['is_urgent']) && isset($b['is_urgent']) && $a['is_urgent'] != $b['is_urgent']) {
                    return $b['is_urgent'] - $a['is_urgent']; // Urgent booking comes first
                }
                // Jika keduanya sama-sama urgent atau sama-sama tidak urgent, urutkan berdasarkan created_at
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });
            
            // Check each pair of bookings
            for($i = 0; $i < count($day_bookings); $i++) {
                for($j = $i + 1; $j < count($day_bookings); $j++) {
                    $booking1 = $day_bookings[$i];
                    $booking2 = $day_bookings[$j];
                    
                    // Check if time ranges overlap
                    if($this->timeRangesOverlap(
                        $booking1['start_time'], $booking1['end_time'],
                        $booking2['start_time'], $booking2['end_time']
                    )) {
                        // Determine which booking gets priority
                        $priority_booking = $booking1;
                        $other_booking = $booking2;
                        
                        // If booking2 is urgent and booking1 is not, booking2 gets priority
                        if (isset($booking2['is_urgent']) && $booking2['is_urgent'] && 
                            (!isset($booking1['is_urgent']) || !$booking1['is_urgent'])) {
                            $priority_booking = $booking2;
                            $other_booking = $booking1;
                        }
                        
                        // Add to conflicts
                        $conflicts[] = [
                            'date' => $date,
                            'booking1' => [
                                'id' => $booking1['id'],
                                'title' => $booking1['title'],
                                'time' => date('H:i', strtotime($booking1['start_time'])) . ' - ' . date('H:i', strtotime($booking1['end_time'])),
                                'created_at' => $booking1['created_at'],
                                'is_urgent' => isset($booking1['is_urgent']) ? $booking1['is_urgent'] : false
                            ],
                            'booking2' => [
                                'id' => $booking2['id'],
                                'title' => $booking2['title'],
                                'time' => date('H:i', strtotime($booking2['start_time'])) . ' - ' . date('H:i', strtotime($booking2['end_time'])),
                                'created_at' => $booking2['created_at'],
                                'is_urgent' => isset($booking2['is_urgent']) ? $booking2['is_urgent'] : false
                            ],
                            'recommendation' => [
                                'id' => $priority_booking['id'],
                                'title' => $priority_booking['title'],
                                'time' => date('H:i', strtotime($priority_booking['start_time'])) . ' - ' . date('H:i', strtotime($priority_booking['end_time'])),
                                'is_urgent' => isset($priority_booking['is_urgent']) ? $priority_booking['is_urgent'] : false
                            ]
                        ];
                    }
                }
            }
        }
        
        return $conflicts;
    }
    
    // Helper function to check if time ranges overlap
    private function timeRangesOverlap($start1, $end1, $start2, $end2) {
        return (
            ($start1 < $end2 && $end1 > $start2) ||
            ($start2 < $end1 && $end2 > $start1)
        );
    }
    
    // Optimize schedule using genetic algorithm
    public function optimizeSchedule() {
        // Get start and end date from POST
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        // Get all bookings for the period
        $all_bookings = $this->getAllBookings($start_date, $end_date);
        
        // Filter only pending bookings
        $pending_bookings = array_filter($all_bookings, function($booking) {
            return $booking['status'] == 'pending';
        });
        
        // Group bookings by date
        $bookings_by_date = [];
        foreach($all_bookings as $booking) {
            // Include all bookings (pending, approved, rejected) for conflict checking
            $date = $booking['date'];
            if(!isset($bookings_by_date[$date])) {
                $bookings_by_date[$date] = [];
            }
            $bookings_by_date[$date][] = $booking;
        }
        
        // Sort each day's bookings by is_urgent first, then by created_at
        foreach($bookings_by_date as $date => &$day_bookings) {
            usort($day_bookings, function($a, $b) {
                // Jika salah satu booking adalah urgent dan yang lain tidak
                if (isset($a['is_urgent']) && isset($b['is_urgent']) && $a['is_urgent'] != $b['is_urgent']) {
                    return $b['is_urgent'] - $a['is_urgent']; // Urgent booking comes first
                }
                // Jika keduanya sama-sama urgent atau sama-sama tidak urgent, urutkan berdasarkan created_at
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });
        }
        
        // Initialize optimized bookings array
        $optimized_bookings = [];
        
        // Process each pending booking
        foreach($pending_bookings as $booking) {
            $date = $booking['date'];
            $day_bookings = $bookings_by_date[$date];
            
            // Check for conflicts with approved bookings
            $has_conflict = false;
            foreach($day_bookings as $existing_booking) {
                // Skip if it's the same booking or not approved
                if($existing_booking['id'] == $booking['id'] || $existing_booking['status'] != 'approved') {
                    continue;
                }
                
                // Check if time ranges overlap
                if($this->timeRangesOverlap(
                    $booking['start_time'], $booking['end_time'],
                    $existing_booking['start_time'], $existing_booking['end_time']
                )) {
                    // Check if current booking is urgent and existing is not
                    $current_is_urgent = isset($booking['is_urgent']) && $booking['is_urgent'];
                    $existing_is_urgent = isset($existing_booking['is_urgent']) && $existing_booking['is_urgent'];
                    
                    if ($current_is_urgent && !$existing_is_urgent) {
                        // Current booking is urgent but existing is not, so no conflict
                        continue;
                    } else if (!$current_is_urgent && $existing_is_urgent) {
                        // Current booking is not urgent but existing is, so there's a conflict
                        $has_conflict = true;
                        break;
                    } else {
                        // Both are urgent or both are not urgent, check created_at
                        if(strtotime($booking['created_at']) > strtotime($existing_booking['created_at'])) {
                            // Current booking was created later, so it has conflict
                            $has_conflict = true;
                            break;
                        }
                    }
                }
            }
            
            // If no conflict with approved bookings, check with other pending bookings
            if(!$has_conflict) {
                foreach($day_bookings as $existing_booking) {
                    // Skip if it's the same booking or not pending
                    if($existing_booking['id'] == $booking['id'] || $existing_booking['status'] != 'pending') {
                        continue;
                    }
                    
                    // Check if time ranges overlap
                    if($this->timeRangesOverlap(
                        $booking['start_time'], $booking['end_time'],
                        $existing_booking['start_time'], $existing_booking['end_time']
                    )) {
                        // Check if current booking is urgent and existing is not
                        $current_is_urgent = isset($booking['is_urgent']) && $booking['is_urgent'];
                        $existing_is_urgent = isset($existing_booking['is_urgent']) && $existing_booking['is_urgent'];
                        
                        if ($current_is_urgent && !$existing_is_urgent) {
                            // Current booking is urgent but existing is not, so no conflict
                            continue;
                        } else if (!$current_is_urgent && $existing_is_urgent) {
                            // Current booking is not urgent but existing is, so there's a conflict
                            $has_conflict = true;
                            break;
                        } else {
                            // Both are urgent or both are not urgent, check created_at
                            if(strtotime($booking['created_at']) > strtotime($existing_booking['created_at'])) {
                                // Current booking was created later, so it has conflict
                                $has_conflict = true;
                                break;
                            }
                        }
                    }
                }
            }
            
            // If no conflict, add to optimized bookings
            if(!$has_conflict) {
                $optimized_bookings[] = $booking;
            }
        }
        
        return $optimized_bookings;
    }
    
    // Create fixed schedules
    public function createFixedSchedules() {
        try {
            return $this->schedule->create_fixed_schedules();
        } catch (Exception $e) {
            error_log('Error creating fixed schedules: ' . $e->getMessage());
            return false;
        }
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ScheduleController();
    
    // Handle create_fixed_schedules action
    if (isset($_POST['create_fixed_schedules'])) {
        if ($controller->createFixedSchedules()) {
            $_SESSION['message'] = 'Jadwal tetap berhasil dibuat untuk 3 bulan ke depan';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal membuat jadwal tetap';
            $_SESSION['message_type'] = 'danger';
        }
        
        // Redirect back to calendar page menggunakan BASE_URL
        header('Location: ' . BASE_URL . 'views/schedule/calendar.php');
        exit;
    }
    
    // Handle delete_schedule action
    if (isset($_POST['delete_schedule'])) {
        $schedule = new Schedule($controller->db);
        $schedule->id = $_POST['id'];
        
        if ($schedule->delete()) {
            $_SESSION['message'] = 'Jadwal berhasil dihapus';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal menghapus jadwal';
            $_SESSION['message_type'] = 'danger';
        }
        
        // Redirect back to calendar page menggunakan BASE_URL
        header('Location: ' . BASE_URL . 'views/schedule/calendar.php');
        exit;
    }
}
?>
