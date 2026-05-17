<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $activity_id = $_POST['activity_id'] ?? '';
        $student_id = $_SESSION['user']['id'];

        if (!$activity_id || !is_numeric($activity_id)) {
            flashError('Invalid activity ID.');
            redirect('../pages/activity.php?activity_id=' . urlencode($activity_id));
        }

        // Verify student is enrolled in the class containing this activity
        $stmt = $conn->prepare('
            SELECT 1 FROM submissions s
            JOIN activities a ON s.activity_id = a.id
            JOIN classes c ON a.class_id = c.classes_id
            JOIN enrollments e ON c.classes_id = e.class_id
            WHERE s.activity_id = ? AND s.student_id = ? AND e.user_id = ?
        ');
        $stmt->bind_param('iii', $activity_id, $student_id, $student_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            flashError('Submission not found or access denied.');
            redirect('../pages/activity.php?activity_id=' . urlencode($activity_id));
        }
        $stmt->close();

        // Delete the submission
        $stmt = $conn->prepare('DELETE FROM submissions WHERE activity_id = ? AND student_id = ?');
        $stmt->bind_param('ii', $activity_id, $student_id);

        if ($stmt->execute()) {
            flashSuccess('Submission has been removed. You can now submit again.');
        } else {
            flashError('Error removing submission: ' . $stmt->error);
        }
        $stmt->close();

        redirect('../pages/activity.php?activity_id=' . urlencode($activity_id));
    } catch (\mysqli_sql_exception $e) {
        error_log("Database error in delete_submission: " . $e->getMessage());
        flashError('Database error occurred.');
        $activity_id = $_POST['activity_id'] ?? '';
        redirect('../pages/activity.php?activity_id=' . urlencode($activity_id));
    }
}
?>
