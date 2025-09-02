<?php
class Booking {
    private $conn;
    private $table = 'bookings';
    
    public $id;
    public $user_id;
    public $activity_type;
    public $title;
    public $description;
    public $date;
    public $start_time;
    public $end_time;
    public $status;
    public $rejection_reason; // Tambahkan property baru
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create booking
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET user_id = :user_id, 
                      activity_type = :activity_type, 
                      title = :title, 
                      description = :description, 
                      date = :date, 
                      start_time = :start_time, 
                      end_time = :end_time';
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->activity_type = htmlspecialchars(strip_tags($this->activity_type));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->start_time = htmlspecialchars(strip_tags($this->start_time));
        $this->end_time = htmlspecialchars(strip_tags($this->end_time));
        
        // Bind data
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':activity_type', $this->activity_type);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':end_time', $this->end_time);
        
        if($stmt->execute()) {
            return true;
        }
        
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Get all bookings
    public function read() {
        $query = 'SELECT b.*, u.name as user_name 
                  FROM ' . $this->table . ' b
                  LEFT JOIN users u ON b.user_id = u.id
                  ORDER BY b.created_at DESC';
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get bookings by user
    public function read_by_user() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id ORDER BY created_at DESC';
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $this->user_id);
        
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get single booking
    public function read_single() {
        $query = 'SELECT b.*, u.name as user_name 
                  FROM ' . $this->table . ' b
                  LEFT JOIN users u ON b.user_id = u.id
                  WHERE b.id = :id';
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->user_id = $row['user_id'];
            $this->activity_type = $row['activity_type'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->date = $row['date'];
            $this->start_time = $row['start_time'];
            $this->end_time = $row['end_time'];
            $this->status = $row['status'];
            $this->rejection_reason = $row['rejection_reason'] ?? null; // Tambahkan ini
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }
    
    // Update booking status
    public function update_status() {
        // Jika status rejected dan ada rejection_reason
        if($this->status == 'rejected' && isset($this->rejection_reason)) {
            $query = 'UPDATE ' . $this->table . ' SET status = :status, rejection_reason = :rejection_reason WHERE id = :id';
            
            $stmt = $this->conn->prepare($query);
            
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->rejection_reason = htmlspecialchars(strip_tags($this->rejection_reason));
            $this->id = htmlspecialchars(strip_tags($this->id));
            
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':rejection_reason', $this->rejection_reason);
            $stmt->bindParam(':id', $this->id);
        } else {
            // Original query for other statuses
            $query = 'UPDATE ' . $this->table . ' SET status = :status WHERE id = :id';
            
            $stmt = $this->conn->prepare($query);
            
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->id = htmlspecialchars(strip_tags($this->id));
            
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':id', $this->id);
        }
        
        if($stmt->execute()) {
            return true;
        }
        
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Delete booking
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
}
?>
