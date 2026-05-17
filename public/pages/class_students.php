<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

$class_id = $_GET['class_id'] ?? '';
if (!$class_id || !is_numeric($class_id)) {
    die('Invalid class ID.');
}

$user_id = $_SESSION['user']['id'];

// Check if class belongs to faculty
$stmt = $conn->prepare('SELECT subject, section FROM classes WHERE classes_id = ? AND user_id = ?');
$stmt->bind_param('ii', $class_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Class not found or access denied.');
}
$class = $result->fetch_assoc();
$stmt->close();

// Get enrolled students
$stmt = $conn->prepare('SELECT u.id, u.firstname, u.lastname, u.email FROM users u JOIN enrollments e ON u.id = e.user_id WHERE e.class_id = ? ORDER BY u.firstname, u.lastname');
$stmt->bind_param('i', $class_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Students in <?= htmlspecialchars($class['subject']); ?></title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/class_students.css">
</head>
<body>
    <div class="header">
        <h2>Students in <?= htmlspecialchars($class['subject']); ?> - <?= htmlspecialchars($class['section']); ?></h2>
        <a href="dashboard.php" class="logout">Back to Dashboard</a>
    </div>

    <div class="container_act">
        <?php $error = getFlashError(); ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php $success = getFlashSuccess(); ?>
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="dashboard-actions">
            <a href="class_activities.php?class_id=<?= htmlspecialchars($class_id); ?>" class="back-btn">View Activities</a>
        </div>

        <?php if (count($students) > 0): ?>
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></td>
                            <td><?= htmlspecialchars($student['email']); ?></td>
                            <td>
                                <div class="student-actions">
                                    <form action="../api/kick_student.php" method="post" style="display:inline;">
                                        <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id); ?>">
                                        <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['id']); ?>">
                                        <button type="submit" class="kick-btn" onclick="return confirm('Are you sure you want to remove this student from the class?')">Kick</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-students">No students enrolled yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
