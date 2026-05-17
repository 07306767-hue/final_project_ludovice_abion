<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $class_id = $_POST['class_id'] ?? '';

        if (!$class_id || !is_numeric($class_id)) {
            flashError('Invalid class ID.');
            redirect('dashboard.php');
        }

        $user_id = $_SESSION['user']['id'];

        // Check if enrolled
        $stmt = $conn->prepare('SELECT 1 FROM enrollments WHERE user_id = ? AND class_id = ?');
        $stmt->bind_param('ii', $user_id, $class_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            flashError('You are not enrolled in this class.');
            redirect('dashboard.php');
        }
        $stmt->close();

        // Leave the class
        $stmt = $conn->prepare('DELETE FROM enrollments WHERE user_id = ? AND class_id = ?');
        $stmt->bind_param('ii', $user_id, $class_id);
        if ($stmt->execute()) {
            flashSuccess(MSG_LEFT_CLASS); // Magic string → constant
            redirect('dashboard.php');
        } else {
            flashError(ERR_ERROR_LEAVING . $stmt->error); // Magic string → constant
            redirect('dashboard.php');
        }
    } catch (\mysqli_sql_exception $e) {
        error_log("Database error in leave_class: " . $e->getMessage());
        flashError('Database error occurred while leaving class.');
        redirect('dashboard.php');
    }
} else {
    redirect('dashboard.php');
}
?>