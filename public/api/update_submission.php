<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $activity_id = $_POST['activity_id'] ?? '';
        $answer = trim($_POST['answer'] ?? '');
        $student_id = $_SESSION['user']['id'];

        if (!$activity_id || !is_numeric($activity_id)) {
            die('Invalid activity ID.');
        }

        if ($answer === '') {
            die('Answer cannot be empty.');
        }

        // Get activity and check deadline
        $stmt = $conn->prepare('
            SELECT a.id, a.due_date FROM activities a
            JOIN classes c ON a.class_id = c.classes_id
            JOIN enrollments e ON c.classes_id = e.class_id
            WHERE a.id = ? AND e.user_id = ?
        ');
        $stmt->bind_param('ii', $activity_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            die('Activity not found or access denied.');
        }
        $activity = $result->fetch_assoc();
        $stmt->close();

        // Check if submission exists
        $stmt = $conn->prepare('SELECT id FROM submissions WHERE activity_id = ? AND student_id = ?');
        $stmt->bind_param('ii', $activity_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $submission = $result->fetch_assoc();
        $stmt->close();

        if (!$submission) {
            die('No submission found to update.');
        }

        // Handle file upload
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/submissions/';

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
            $unique_filename = uniqid('submission_' . $activity_id . '_' . $student_id . '_', true) . '.' . $file_extension;
            $target_path = $upload_dir . $unique_filename;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
                $attachment_path = 'uploads/submissions/' . $unique_filename;
            } else {
                die('Failed to upload file.');
            }
        }

        // Update submission
        if ($attachment_path) {
            $stmt = $conn->prepare('
                UPDATE submissions
                SET answer = ?, attachment_path = ?
                WHERE activity_id = ? AND student_id = ?
            ');
            $stmt->bind_param('ssii', $answer, $attachment_path, $activity_id, $student_id);
        } else {
            $stmt = $conn->prepare('
                UPDATE submissions
                SET answer = ?
                WHERE activity_id = ? AND student_id = ?
            ');
            $stmt->bind_param('sii', $answer, $activity_id, $student_id);
        }

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Submission updated successfully!';
            header('Location: ../pages/activity.php?activity_id=' . $activity_id);
            exit();
        }

        die('Error updating submission: ' . $stmt->error);
    } catch (\mysqli_sql_exception $e) {
        error_log("Database error in update_submission: " . $e->getMessage());
        die('Database error occurred while updating submission.');
    }
}
