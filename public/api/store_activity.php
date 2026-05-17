<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $class_id = $_POST['class_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $total_points = $_POST['total_points'] ?? '';
    $created_by = $_SESSION['user']['id'];

    if (!$class_id || !$name || !$description || !$due_date || !$total_points) {
        die('All fields are required.');
    }

    // Validate due date is not in the past
    $today = new DateTime(date('Y-m-d'));
    $dueDateTime = DateTime::createFromFormat('Y-m-d', $due_date);

    if (!$dueDateTime || $dueDateTime < $today) {
        die('Due date cannot be in the past. Please select today or a future date.');
    }

    // check ownership
    $stmt = $conn->prepare("SELECT classes_id FROM classes WHERE classes_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $class_id, $created_by);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {
        die("Access denied.");
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
        $unique_filename = uniqid('activity_' . $class_id . '_', true) . '.' . $file_extension;
        $target_path = $upload_dir . $unique_filename;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
            $attachment_path = 'uploads/activities/' . $unique_filename;
        } else {
            die('Failed to upload file.');
        }
    }

    // insert activity
    $stmt = $conn->prepare("
        INSERT INTO activities
        (class_id, name, description, due_date, total_points, created_by, attachment_path)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issssis",
        $class_id,
        $name,
        $description,
        $due_date,
        $total_points,
        $created_by,
        $attachment_path
    );

    $stmt->execute();

    header("Location: ../pages/class_activities.php?class_id=" . $class_id);
exit();
}
?>