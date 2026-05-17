<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();


$class_id = $_GET['class_id'] ?? '';
if (!$class_id || !is_numeric($class_id)) {
    die('Invalid class ID.');
}

$user_id = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'] ?? null;

// Get class details and verify access
// Faculty can access if they created the class, students if they're enrolled
$stmt = $conn->prepare('
    SELECT c.classes_id, c.subject, c.section, c.class_code
    FROM classes c
    LEFT JOIN enrollments e ON c.classes_id = e.class_id AND e.user_id = ?
    WHERE c.classes_id = ? AND c.is_archived = 0 AND (c.user_id = ? OR e.user_id = ?)
');
$stmt->bind_param('iiii', $user_id, $class_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Class not found or access denied.');
}
$class = $result->fetch_assoc();
$stmt->close();

// Fetch all activities for this class
$stmt = $conn->prepare('SELECT id, name, description, total_points, due_date FROM activities WHERE class_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $class_id);
$stmt->execute();
$activitiesResult = $stmt->get_result();
$activities = $activitiesResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check submission status for each activity
$submissionStatus = [];
foreach ($activities as $activity) {
    $stmtCheck = $conn->prepare('SELECT id FROM submissions WHERE activity_id = ? AND student_id = ?');
    $stmtCheck->bind_param('ii', $activity['id'], $user_id);
    $stmtCheck->execute();
    $submissionStatus[$activity['id']] = $stmtCheck->get_result()->num_rows > 0;
    $stmtCheck->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($class['subject']); ?> - Activities</title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/class_view.css">
    <style>
        
    </style>
</head>
<body>
    <div class="header">
        <h2><?= htmlspecialchars($class['subject']); ?> - <?= htmlspecialchars($class['section']); ?></h2>
        <a href="dashboard.php" class="logout">Back to Dashboard</a>
    </div>

    <div class="class-detail-container">
        <div class="class-header">
            <h2><?= htmlspecialchars($class['subject']); ?></h2>
            <div class="class-details">
                <div class="class-info">
                    <div><strong>Section:</strong> <?= htmlspecialchars($class['section']); ?></div>
                    <div><strong>Class Code:</strong> <?= htmlspecialchars($class['class_code']); ?></div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <form method="POST" action="leave_class.php" onsubmit="return confirm('Are you sure you want to leave this class?');">
                        <input type="hidden" name="class_id" value="<?= $class_id; ?>">
                        <button type="submit" class="leave-btn">Leave Class</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="activities-list">
            <h3>Activities</h3>
            
            <?php if (count($activities) > 0): ?>
                <div class="activities-grid">
                    <?php foreach ($activities as $activity): ?>
                        <div class="activity-card">
                            <div class="card-header">
                                <h4><?= htmlspecialchars($activity['name']); ?></h4>
                                <?php if ($submissionStatus[$activity['id']]): ?>
                                    <span class="submitted-badge">✓ Submitted</span>
                                <?php else: ?>
                                    <span class="pending-badge">⏳ Pending</span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="description"><?= htmlspecialchars(substr($activity['description'], 0, 80)) . (strlen($activity['description']) > 80 ? '...' : ''); ?></p>
                            
                            <?php if ($activity['total_points']): ?>
                                <p><strong>Points:</strong> <?= htmlspecialchars($activity['total_points']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($activity['due_date']): ?>
                                <p class="due-date"><strong>Due:</strong> <?= date('M d, Y g:i A', strtotime($activity['due_date'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="card-actions">
                                <a href="activity.php?activity_id=<?= $activity['id']; ?>" class="view-btn">
                                    <?php if ($submissionStatus[$activity['id']]): ?>
                                        View & Edit
                                    <?php else: ?>
                                        View & Submit
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-activities">
                    <p>No activities available for this class yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
