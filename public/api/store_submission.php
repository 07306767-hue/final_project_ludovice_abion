<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
}

try {
    $activity_id = trim($_POST['activity_id'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $student_id = $_SESSION['user']['id'];

    if (!$activity_id || !is_numeric($activity_id) || $answer === '') {
        die('Invalid input.');
    }

    // Verify access - student must be enrolled and activity creator is not the submitter
    $stmt = $conn->prepare('
        SELECT a.id
        FROM activities a
        JOIN classes c ON a.class_id = c.classes_id
        JOIN enrollments e ON c.classes_id = e.class_id
        WHERE a.id = ? AND e.user_id = ? AND c.user_id != ?
    ');
    $stmt->bind_param('iii', $activity_id, $student_id, $student_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        die('Access denied. You cannot submit to this activity.');
    }
    $stmt->close();

    // Check if already submitted
    $stmt = $conn->prepare('SELECT id FROM submissions WHERE activity_id = ? AND student_id = ?');
    $stmt->bind_param('ii', $activity_id, $student_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        die('Already submitted.');
    }
    $stmt->close();

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

    // Create submissions table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        activity_id INT NOT NULL,
        student_id INT NOT NULL,
        answer TEXT NOT NULL,
        attachment_path VARCHAR(500) NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        grade VARCHAR(50) NULL,
        graded_at TIMESTAMP NULL,
        graded_by INT NULL
    )");

    $stmt = $conn->prepare('INSERT INTO submissions (activity_id, student_id, answer, attachment_path) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iiss', $activity_id, $student_id, $answer, $attachment_path);

    if ($stmt->execute()) {
        flashSuccess('Submission successful! You can now edit your submission.');
        header('Location: ../pages/activity.php?activity_id=' . $activity_id);
        exit();
    } else {
        die('Error submitting activity.');
    }
} catch (\mysqli_sql_exception $e) {
    error_log("Database error in store_submission: " . $e->getMessage());
    die('Database error occurred while submitting activity.');
}
?>