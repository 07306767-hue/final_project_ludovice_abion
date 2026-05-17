<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $activity_id = $_POST['activity_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $total_points = trim($_POST['total_points'] ?? '');
        $due_date = $_POST['due_date'] ?? '';
        $faculty_id = $_SESSION['user']['id'];

        if (!$activity_id || !is_numeric($activity_id)) {
            die('Invalid activity ID.');
        }

        if ($name === '' || $description === '' || $total_points === '') {
            die('Activity name, description, and total points are required.');
        }

        // Verify faculty owns this activity
        $stmt = $conn->prepare('
            SELECT a.id FROM activities a
            JOIN classes c ON a.class_id = c.classes_id
            WHERE a.id = ? AND c.user_id = ?
        ');
        $stmt->bind_param('ii', $activity_id, $faculty_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            die('Access denied. You do not own this activity.');
        }
        $stmt->close();

        // Handle file upload
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/activities/';

            // Validate file size (10MB max)
            if ($_FILES['attachment']['size'] > 10 * 1024 * 1024) {
                die('File size too large. Maximum size is 10MB.');
            }

            // Validate file type
            $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
            $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));

            if (!in_array($file_extension, $allowed_types)) {
                die('Invalid file type. Allowed types: ' . implode(', ', $allowed_types));
            }

            // Generate unique filename
            $unique_filename = uniqid('activity_' . $activity_id . '_', true) . '.' . $file_extension;
            $target_path = $upload_dir . $unique_filename;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
                $attachment_path = 'uploads/activities/' . $unique_filename;
            } else {
                die('Failed to upload file.');
            }
        }

        // Update activity
        $due_date_sql = !empty($due_date) ? $due_date : null;

        if ($attachment_path) {
            $stmt = $conn->prepare('
                UPDATE activities
                SET name = ?, description = ?, total_points = ?, due_date = ?, attachment_path = ?
                WHERE id = ?
            ');
            $stmt->bind_param('sssssi', $name, $description, $total_points, $due_date_sql, $attachment_path, $activity_id);
        } else {
            $stmt = $conn->prepare('
                UPDATE activities
                SET name = ?, description = ?, total_points = ?, due_date = ?
                WHERE id = ?
            ');
            $stmt->bind_param('ssssi', $name, $description, $total_points, $due_date_sql, $activity_id);
        }

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Activity updated successfully!';
            header('Location: ../pages/activity.php?activity_id=' . $activity_id);
            exit();
        }

        die('Error updating activity: ' . $stmt->error);
    } catch (\mysqli_sql_exception $e) {
        error_log("Database error in update_activity: " . $e->getMessage());
        die('Database error occurred while updating activity.');
    }
}
