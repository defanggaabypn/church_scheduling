<?php
class Schedule {
    private $conn;
    private $table = 'schedules';
    
    public $id;
    public $booking_id;
    public $activity_type;
    public $title;
    public $date;
    public $start_time;
    public $end_time;
    public $organization;
    public $is_fixed;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create schedule
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET booking_id = :booking_id, 
                      activity_type = :activity_type, 
                      title = :title, 
                      date = :date, 
                      start_time = :start_time, 
                      end_time = :end_time, 
                      organization = :organization, 
                      is_fixed = :is_fixed';
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $this->booking_id = $this->booking_id ? htmlspecialchars(strip_tags($this->booking_id)) : null;
        $this->activity_type = htmlspecialchars(strip_tags($this->activity_type));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->start_time = htmlspecialchars(strip_tags($this->start_time));
        $this->end_time = htmlspecialchars(strip_tags($this->end_time));
        $this->organization = htmlspecialchars(strip_tags($this->organization));
        $this->is_fixed = (int)$this->is_fixed;
        
        // Bind data
        $stmt->bindParam(':booking_id', $this->booking_id);
        $stmt->bindParam(':activity_type', $this->activity_type);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':end_time', $this->end_time);
        $stmt->bindParam(':organization', $this->organization);
        $stmt->bindParam(':is_fixed', $this->is_fixed);
        
        if($stmt->execute()) {
            return true;
        }
        
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get all schedules
    public function read() {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY date ASC, start_time ASC';
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get schedules by date range
    public function read_by_date_range($start_date, $end_date) {
        $query = 'SELECT * FROM ' . $this->table . ' 
                  WHERE date BETWEEN :start_date AND :end_date 
                  ORDER BY date ASC, start_time ASC';
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Check for schedule conflicts
    public function check_conflicts($date, $start_time, $end_time) {
        $query = 'SELECT * FROM ' . $this->table . ' 
                  WHERE date = :date AND 
                  ((start_time <= :start_time AND end_time > :start_time) OR 
                   (start_time < :end_time AND end_time >= :end_time) OR 
                   (start_time >= :start_time AND end_time <= :end_time))';
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Delete schedule
    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Create fixed schedules
    public function create_fixed_schedules() {
        try {
            // TK & PAUD (Senin-Jumat, 08:00-12:00)
            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            $start_date = new DateTime('now');
            $end_date = new DateTime('+3 months');
            
            while ($start_date <= $end_date) {
                $day_of_week = $start_date->format('l');
                
                if (in_array($day_of_week, $weekdays)) {
                    $this->activity_type = 'tk_paud';
                    $this->title = 'Kegiatan Belajar Mengajar TK & PAUD';
                    $this->date = $start_date->format('Y-m-d');
                    $this->start_time = '08:00:00';
                    $this->end_time = '12:00:00';
                    $this->organization = 'TK & PAUD';
                    $this->is_fixed = 1;
                    $this->create();
                }
                
                // Ibadah Doa (Sabtu, 18:00)
                if ($day_of_week == 'Saturday') {
                    $this->activity_type = 'doa';
                    $this->title = 'Ibadah Doa';
                    $this->date = $start_date->format('Y-m-d');
                    $this->start_time = '18:00:00';
                    $this->end_time = '20:00:00';
                    $this->organization = 'Gereja';
                    $this->is_fixed = 1;
                    $this->create();
                }
                
                // Ibadah Minggu (09:30)
                if ($day_of_week == 'Sunday') {
                    $this->activity_type = 'minggu';
                    $this->title = 'Ibadah Minggu Pagi';
                    $this->date = $start_date->format('Y-m-d');
                    $this->start_time = '09:30:00';
                    $this->end_time = '11:30:00';
                    $this->organization = 'Gereja';
                    $this->is_fixed = 1;
                    $this->create();
                }
                
                $start_date->modify('+1 day');
            }
            
            return true;
        } catch (Exception $e) {
            // Log error
            error_log('Error creating fixed schedules: ' . $e->getMessage());
            return false;
        }
    }
}
?>
