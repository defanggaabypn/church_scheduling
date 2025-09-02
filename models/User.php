<?php
class User {
    private $conn;
    private $table = 'users';
    
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new user
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' SET name = :name, email = :email, password = :password, role = :role';
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        // Tidak melakukan hashing di sini karena sudah dilakukan di controller
        $this->role = htmlspecialchars(strip_tags($this->role));
        
        // Bind data
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);
        
        if($stmt->execute()) {
            return true;
        }
        
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Login user
    public function login() {
        $query = 'SELECT id, name, email, password, role FROM ' . $this->table . ' WHERE email = :email';
        
        $stmt = $this->conn->prepare($query);
        
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        $stmt->bindParam(':email', $this->email);
        
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get all users
    public function getAdminUser() {
    $query = 'SELECT id FROM ' . $this->table . ' WHERE role = :role LIMIT 1';
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':role', 'admin');
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    public function read() {
        $query = 'SELECT * FROM ' . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get single user
    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id';
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->name = $row['name'];
        $this->email = $row['email'];
        $this->role = $row['role'];
        $this->created_at = $row['created_at'];
    }
    
    // Update user
    public function update() {
        $query = 'UPDATE ' . $this->table . ' SET name = :name, email = :email, role = :role WHERE id = :id';
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    
    // Delete user
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

// Update user password
public function update_password() {
    $query = 'UPDATE ' . $this->table . ' SET password = :password WHERE id = :id';
    
    $stmt = $this->conn->prepare($query);
    
    $this->id = htmlspecialchars(strip_tags($this->id));
    
    $stmt->bindParam(':password', $this->password);
    $stmt->bindParam(':id', $this->id);
    
    if($stmt->execute()) {
        return true;
    }
    
    printf("Error: %s.\n", $stmt->error);
    return false;
}

}
?>
