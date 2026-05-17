<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $class_id = trim($_POST['class_id'] ?? '');
        $faculty_id = $_SESSION['user']['id'];

        if ($title === '' || $content === '' || $class_id === '') {
            die('Title, content, and class are required.');
        }

        // Verify that the faculty owns this class
        $stmt = $conn->prepare('SELECT user_id FROM classes WHERE classes_id = ?');
        if (!$stmt) {
            die('Database error: ' . $conn->error);
        }
        $stmt->bind_param('i', $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $class = $result->fetch_assoc();
        $stmt->close();

        if (!$class || $class['user_id'] != $faculty_id) {
            die('You are not authorized to create announcements for this class.');
        }

        $stmt = $conn->prepare(
            'INSERT INTO announcements (class_id, faculty_id, title, content, created_at) VALUES (?, ?, ?, ?, NOW())'
        );

        if (!$stmt) {
            die('Database error: ' . $conn->error);
        }

        $stmt->bind_param('iiss', $class_id, $faculty_id, $title, $content);

        if ($stmt->execute()) {
            header('Location: ../pages/create_announcement.php?success=1');
            exit();
        }

        die('Error creating announcement: ' . $stmt->error);
    } catch (\mysqli_sql_exception $e) {
        error_log("Database error in store_announcement: " . $e->getMessage());
        die('Database error occurred while creating announcement.');
    }
}
