<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $submission_id = trim($_POST['submission_id'] ?? '');
        $grade = trim($_POST['grade'] ?? '');
        $class_id = trim($_POST['class_id'] ?? '');
        $redirect = trim($_POST['redirect'] ?? '');
        $graded_by = $_SESSION['user']['id'];

        if (!$submission_id || !is_numeric($submission_id) || $grade === '' || !$class_id || !is_numeric($class_id)) {
            die('Invalid input.');
        }

        if (!is_numeric($grade)) {
            die('Grade must be a numeric value.');
        }

        $grade_value = floatval($grade);
        if ($grade_value < 0) {
            die('Grade cannot be negative.');
        }

        // Verify the submission belongs to the faculty's class and get activity total points
        $stmt = $conn->prepare('
            SELECT s.id, a.total_points
            FROM submissions s
            JOIN activities a ON s.activity_id = a.id
            JOIN classes c ON a.class_id = c.classes_id
            WHERE s.id = ? AND c.classes_id = ? AND c.user_id = ?
        ');
        $stmt->bind_param('iii', $submission_id, $class_id, $graded_by);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            die('Access denied.');
        }
        $submission = $result->fetch_assoc();
        $stmt->close();

        // Validate grade does not exceed total points
        if ($submission['total_points'] && $grade_value > floatval($submission['total_points'])) {
            echo '<script>
                alert("Error: Grade cannot exceed total points (' . htmlspecialchars($submission['total_points']) . ').");
                window.history.back();
            </script>';
            exit();
        }

        $grade = (string) $grade_value;

        // Update grade
        $stmt = $conn->prepare('UPDATE submissions SET grade = ?, graded_at = NOW(), graded_by = ? WHERE id = ?');
        $stmt->bind_param('sii', $grade, $graded_by, $submission_id);

        if ($stmt->execute()) {
            // Normalize redirect path so it works from the api folder
            if ($redirect) {
                if (!preg_match('#^(?:https?://|/|\.\./)#', $redirect)) {
                    $redirect = '../pages/' . ltrim($redirect, '/');
                }
                $redirectTo = $redirect;
            } else {
                $redirectTo = '../pages/class_activities.php?class_id=' . $class_id;
            }
            header('Location: ' . $redirectTo);
            exit();
        } else {
            die('Error grading submission.');
        }
    } catch (\mysqli_sql_exception $e) {
        error_log("Database error in grade_submission: " . $e->getMessage());
        die('Database error occurred while grading submission.');
    }
}
?>