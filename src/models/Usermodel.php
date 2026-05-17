<?php
namespace App\Models;

class UserModel {
    private $conn;

    public function __construct($database) {
        $this->conn = $database->getConnection();
    }

    public function selectAll() {
        try {
            $sql = "SELECT * FROM users ORDER BY id DESC";
            $result = $this->conn->query($sql);
            $users = [];

            if ($result->rowCount() > 0) {
                while($row = $result->fetch()) {
                    $users[] = $row;
                }
            }
            
            return $users;
        } catch (\PDOException $e) {
            // Log the error or handle it appropriately
            error_log("Database error in selectAll: " . $e->getMessage());
            return [];
        }
    }

    public function getUser($id) {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result;
        } catch (\PDOException $e) {
            error_log("Database error in getUser: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByUsername($username) {
        try {
            $sql = "SELECT * FROM users WHERE CONCAT(firstname, ' ', lastname) = :username OR firstname = :username OR lastname = :username";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result;
        } catch (\PDOException $e) {
            error_log("Database error in getUserByUsername: " . $e->getMessage());
            return false;
        }
    }

    public function updateUser($data) {
        try {
            $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":firstname", $data['firstname'] ?? $data['username']);
            $stmt->bindParam(":lastname", $data['lastname'] ?? '');
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":id", $data['id']);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Database error in updateUser: " . $e->getMessage());
            return false;
        }
    }

    public function insert($data) {
        try {
            $sql = "INSERT INTO users (firstname, lastname, password, email, role) VALUES (:firstname, :lastname, :password, :email, :role)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":firstname", $data['firstname'] ?? $data['username']);
            $stmt->bindParam(":lastname", $data['lastname'] ?? '');
            $stmt->bindParam(":password", $data['password']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":role", $data['role'] ?? 'student');
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            error_log("Database error in insert: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Database error in deleteUser: " . $e->getMessage());
            return false;
        }
    }

    public function checkUsernameAvailability($username) {
        $sql = "SELECT id FROM users WHERE CONCAT(firstname, ' ', lastname) = :username OR firstname = :username OR lastname = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result === false;
    }
    
    public function checkEmailAvailability($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result === false;
    }
}
?>