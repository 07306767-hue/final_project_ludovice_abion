<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? '';
    $student_id = $_POST['student_id'] ?? '';

    if (!$class_id || !is_numeric($class_id) || !$student_id || !is_numeric($student_id)) {
        flashError('Invalid request.');
        redirect('../pages/class_students.php?class_id=' . urlencode($class_id));
    }

    $user_id = $_SESSION['user']['id'];

    try {
        // Check if class belongs to faculty
        $stmt = $conn->prepare('SELECT classes_id FROM classes WHERE classes_id = ? AND user_id = ?');
        $stmt->bind_param('ii', $class_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            flashError('Class not found or access denied.');
            redirect('../pages/dashboard.php');
        }
        $stmt->close();

        // Check if student is enrolled in the class
        $stmt = $conn->prepare('SELECT 1 FROM enrollments WHERE user_id = ? AND class_id = ?');
        $stmt->bind_param('ii', $student_id, $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            flashError('Student not enrolled in this class.');
            redirect('../pages/class_students.php?class_id=' . urlencode($class_id));
        }
        $stmt->close();

        // Kick the student (delete enrollment)
        $stmt = $conn->prepare('DELETE FROM enrollments WHERE user_id = ? AND class_id = ?');
        $stmt->bind_param('ii', $student_id, $class_id);
        if ($stmt->execute()) {
            flashSuccess('Student has been removed from the class.');
        } else {
            flashError('Error removing student: ' . $stmt->error);
        }
        $stmt->close();

        redirect('../pages/class_students.php?class_id=' . urlencode($class_id));
    } catch (\mysqli_sql_exception $e) {
        error_log("Database error in kick_student: " . $e->getMessage());
        flashError('Database error occurred.');
        redirect('../pages/class_students.php?class_id=' . urlencode($class_id));
    }
}
?>