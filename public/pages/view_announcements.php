<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$user = $_SESSION['user'];
$class_id = $_GET['class_id'] ?? '';

if (!$class_id) {
    header('Location: dashboard.php');
    exit();
}

// Get class information
$stmt = $conn->prepare('SELECT subject, section, user_id FROM classes WHERE classes_id = ?');
$stmt->bind_param('i', $class_id);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();
$stmt->close();

if (!$class) {
    header('Location: dashboard.php');
    exit();
}

// Verify authorization
if ($user['role'] === 'faculty') {
    if ($class['user_id'] != $user['id']) {
        header('Location: dashboard.php');
        exit();
    }
} elseif ($user['role'] === 'student') {
    $stmt = $conn->prepare(
        'SELECT enrollment_id FROM enrollments WHERE class_id = ? AND user_id = ?'
    );
    $stmt->bind_param('ii', $class_id, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        header('Location: dashboard.php');
        exit();
    }
    $stmt->close();
}

// Fetch announcements
$stmt = $conn->prepare(
    'SELECT a.id, a.title, a.content, a.created_at, u.firstname, u.lastname 
     FROM announcements a 
     JOIN users u ON a.faculty_id = u.id 
     WHERE a.class_id = ? 
     ORDER BY a.created_at DESC'
);

$stmt->bind_param('i', $class_id);
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Announcements - <?php echo htmlspecialchars($class['subject']); ?></title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/announcement.css">
</head>
<body>
    <div class="header">
        <h2>Announcements - <?php echo htmlspecialchars($class['subject']) . ' (Section ' . htmlspecialchars($class['section']) . ')'; ?></h2>
        <a href="dashboard.php?class_id=<?php echo $class_id; ?>" class="logout">Back to Dashboard</a>
    </div>

    <div class="announcements-container">
        <?php if ($user['role'] === 'faculty'): ?>
            <div class="faculty-actions">
                <a href="create_announcement.php" class="btn-create-announcement">+ Create New Announcement</a>
            </div>
        <?php endif; ?>

        <div class="announcements-list">
            <?php if (count($announcements) > 0): ?>
                <?php foreach ($announcements as $announcement): ?>
                    <div class="announcement-card">
                        <div class="announcement-header">
                            <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                            <span class="announcement-author">
                                by <?php echo htmlspecialchars($announcement['firstname'] . ' ' . $announcement['lastname']); ?>
                            </span>
                        </div>
                        <div class="announcement-date">
                            <?php echo date('M d, Y g:i A', strtotime($announcement['created_at'])); ?>
                        </div>
                        <div class="announcement-content">
                            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-announcements">
                    <p>No announcements yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
