<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $subject = trim($_POST['subject'] ?? '');
        $section = trim($_POST['section'] ?? '');
        $teacher_id = $_SESSION['user']['id'];

        if ($subject === '' || $section === '') {
            die('Subject and section are required.');
        }

        // Validate section: must be exactly 1 letter (A-Z, case insensitive)
        if (strlen($section) !== 1 || !preg_match('/^[A-Za-z]$/', $section)) {
            die('Section must be exactly one letter (A-Z).');
        }

        // Convert section to uppercase for consistency
        $section = strtoupper($section);

        $class_code = strtoupper(substr(md5((string) time()), 0, 6));

        $stmt = $conn->prepare(
            'INSERT INTO classes (subject, section, user_id, class_code) VALUES (?, ?, ?, ?)' 
        );

        if (!$stmt) {
            die('Database error: ' . $conn->error);
        }

        $stmt->bind_param('ssis', $subject, $section, $teacher_id, $class_code);

        if ($stmt->execute()) {
            header('Location: ../pages/dashboard.php');
            exit();
        }

        die('Error creating class: ' . $stmt->error);
    } catch (\mysqli_sql_exception $e) {
        error_log("Database error in store_class: " . $e->getMessage());
        die('Database error occurred while creating class.');
    }
}