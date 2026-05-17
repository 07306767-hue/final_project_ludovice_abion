<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

$class_id = $_GET['class_id'] ?? '';
if (!$class_id || !is_numeric($class_id)) {
    die('Invalid class ID.');
}

// Ensure tables exist before querying them
$conn->query("CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    score VARCHAR(50) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    answer TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade VARCHAR(50) DEFAULT NULL,
    graded_at TIMESTAMP NULL,
    graded_by INT NULL
)");

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

// Get activities for the class
$stmt = $conn->prepare('SELECT id, name, description, total_points FROM activities WHERE class_id = ? ORDER BY created_at DESC');
if (!$stmt) {
    die('Database error: ' . $conn->error);
}
$stmt->bind_param('i', $class_id);
$stmt->execute();
$activities_result = $stmt->get_result();
$activities = $activities_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activities for <?= htmlspecialchars($class['subject']); ?></title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/class_activities.css">
</head>
<body>
    <div class="header">
        <h2>Activities for <?= htmlspecialchars($class['subject']); ?> - <?= htmlspecialchars($class['section']); ?></h2>
        <a href="view_class.php?class_id=<?= $class_id; ?>" class="back-btn">Back</a>
    </div>

    <div class="container_act">
        <?php if (count($activities) > 0): ?>
            <?php foreach ($activities as $activity): ?>
                <?php
                // Count submissions for this activity
                $stmt = $conn->prepare('
                    SELECT COUNT(*) as count, SUM(CASE WHEN grade IS NOT NULL THEN 1 ELSE 0 END) as graded_count
                    FROM submissions
                    WHERE activity_id = ?
                ');
                $stmt->bind_param('i', $activity['id']);
                $stmt->execute();
                $counts = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $total_submissions = $counts['count'] ?? 0;
                $graded_submissions = $counts['graded_count'] ?? 0;
                ?>
                <div class="activity-card">
                    <div class="card-header">
                        <h4><?= htmlspecialchars($activity['name']); ?></h4>
                        <span class="submission-badge"><?= $total_submissions; ?> submissions</span>
                    </div>
                    
                    <p class="description"><strong>Description:</strong> <?= htmlspecialchars(substr($activity['description'], 0, 100)) . (strlen($activity['description']) > 100 ? '...' : ''); ?></p>
                    
                    <?php if ($activity['total_points']): ?>
                        <p><strong>Total Points:</strong> <?= htmlspecialchars($activity['total_points']); ?></p>
                    <?php endif; ?>
                    
                    <div class="submission-status">
                        <p><strong>Graded:</strong> <?= $graded_submissions; ?> / <?= $total_submissions; ?></p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $total_submissions > 0 ? ($graded_submissions / $total_submissions * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <a href="view_submissions.php?activity_id=<?= $activity['id']; ?>&class_id=<?= $class_id; ?>" class="view-submissions-btn">
                            View Submissions
                        </a>
                        <a href="edit_activity.php?activity_id=<?= $activity['id']; ?>" class="edit-btn">
                            ✏️ Edit
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; text-align: center;">No activities created yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
