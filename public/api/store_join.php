<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $class_code = trim($_POST['class_code'] ?? '');

        if ($class_code === '') {
            flashInput('class_code', $class_code);
            flashError('Class code is required.');
            redirect('../pages/join_class.php');
        }

        if (!preg_match('/^[A-Za-z0-9\-]+$/', $class_code)) {
            flashInput('class_code', $class_code);
            flashError('Class code must contain only letters, numbers, and hyphens.');
            redirect('../pages/join_class.php');
        }

        // Create enrollments table if not exists
        if (!$conn->query("CREATE TABLE IF NOT EXISTS enrollments (
            enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            class_id INT NOT NULL,
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_enrollment (user_id, class_id)
        )")) {
            flashInput('class_code', $class_code);
            flashError('Database error: ' . $conn->error);
            redirect('../pages/join_class.php');
        }

        // Find class by code
        $stmt = $conn->prepare('SELECT classes_id FROM classes WHERE class_code = ?');
        if (!$stmt) {
            $_SESSION['error'] = 'Database error.';
            header('Location: ../pages/join_class.php');
            exit();
        }
        $stmt->bind_param('s', $class_code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $_SESSION['error'] = 'Class not found.';
            header('Location: ../pages/join_class.php');
            exit();
        }
        $class = $result->fetch_assoc();
        $class_id = $class['classes_id'];
        $stmt->close();

        // Check if already joined
        $stmt = $conn->prepare('SELECT 1 FROM enrollments WHERE user_id = ? AND class_id = ?');
        if (!$stmt) {
            $_SESSION['error'] = 'Database error: ' . $conn->error;
            header('Location: ../pages/join_class.php');
            exit();
        }
        $stmt->bind_param('ii', $_SESSION['user']['id'], $class_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['error'] = 'Already joined this class.';
            header('Location: ../pages/join_class.php');
            exit();
        }
        $stmt->close();

        // Join the class
        $stmt = $conn->prepare('INSERT INTO enrollments (user_id, class_id) VALUES (?, ?)');
        if (!$stmt) {
            $_SESSION['error'] = 'Database error: ' . $conn->error;
            header('Location: ../pages/join_class.php');
            exit();
        }
        $stmt->bind_param('ii', $_SESSION['user']['id'], $class_id);
        if ($stmt->execute()) {
            header('Location: ../pages/dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = 'Error joining class: ' . $stmt->error;
            header('Location: ../pages/join_class.php');
            exit();
        }
    } catch (\mysqli_sql_exception $e) {
        error_log("Database error in store_join: " . $e->getMessage());
        flashError('Database error occurred while joining class.');
        redirect('../pages/join_class.php');
    }
}
?>