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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Activity</title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/create_activity.css">
</head>
<body>
    <div class="header">
        <h2>Create Activity for <?= htmlspecialchars($class['subject']); ?> - <?= htmlspecialchars($class['section']); ?></h2>
        <a href="dashboard.php" class="logout">Back to Dashboard</a>
    </div>

    <div class="create-act">
    <form method="POST" action="../api/store_activity.php" enctype="multipart/form-data">

    <input type="hidden" name="class_id" value="<?= $class_id; ?>">

    <div class="form-group">
        <label>Activity Name</label>
        <input type="text" name="name" required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description" required></textarea>
    </div>

    <div class="form-group">
        <label>Due Date</label>
        <input type="date" name="due_date" min="<?= date('Y-m-d'); ?>" required>
    </div>

    <div class="form-group">
        <label>Total Points</label>
        <input type="number" name="total_points" required>
    </div>

    <div class="form-group">
        <label>Attachment (optional)</label>
        <input type="file" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip,.rar">
        <small>Allowed formats: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, ZIP, RAR (Max: 10MB)</small>
    </div>

    <button type="submit">Create Activity</button>

</form>
</div>
</body>
</html>
