<?php

namespace App\Controllers;

use Validators\UserValidator;

class UserController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function register($data)
    {
        try {
            // 1. Validate
            $errors = UserValidator::validateRegistration($this->conn, $data);

            if (!empty($errors)) {
                return [
                    'status' => false,
                    'errors' => $errors
                ];
            }

            // 2. Sanitize / prepare data
            $firstname = $data['firstname'] ?? $data['name'] ?? '';
            $lastname = $data['lastname'] ?? '';
            $email = $data['email'];
            $role = $data['role'];
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // 3. Insert safely (prepared statement)
            $stmt = $this->conn->prepare("
                INSERT INTO users (firstname, lastname, email, password, role)
                VALUES (?, ?, ?, ?, ?)
            ");

            if (!$stmt) {
                error_log("Database error - prepare failed: " . $this->conn->error);
                return [
                    'status' => false,
                    'errors' => ["Database error: " . $this->conn->error]
                ];
            }

            $stmt->bind_param("sssss", $firstname, $lastname, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                return [
                    'status' => true
                ];
            }

            return [
                'status' => false,
                'errors' => ["Database error: " . $stmt->error]
            ];
        } catch (\mysqli_sql_exception $e) {
            error_log("Database error in register: " . $e->getMessage());
            return [
                'status' => false,
                'errors' => ["Database error occurred"]
            ];
        }
    }
}