<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

$user = $_SESSION['user'];
$myClasses = [];

$stmt = $conn->prepare('SELECT classes_id, subject, section FROM classes WHERE user_id = ? AND is_archived = 0 ORDER BY subject DESC');
if ($stmt) {
    $stmt->bind_param('i', $user['id']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $myClasses = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    $stmt->close();
}

$successMessage = isset($_GET['success']) ? 'Announcement created successfully!' : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Announcement</title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/announcement.css">
</head>
<body>
    <div class="header">
        <h2>Create Announcement</h2>
        <a href="dashboard.php" class="logout">Back to Dashboard</a>
    </div>

    <?php if ($successMessage): ?>
        <div class="success-message"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <div class="create-announcement">
        <form method="POST" action="../api/store_announcement.php">
            <div class="form-group">
                <label for="class_id">Select Class</label>
                <select id="class_id" name="class_id" required>
                    <option value="">-- Choose a class --</option>
                    <?php foreach ($myClasses as $class): ?>
                        <option value="<?php echo $class['classes_id']; ?>">
                            <?php echo htmlspecialchars($class['subject']) . ' - Section ' . htmlspecialchars($class['section']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="title">Announcement Title</label>
                <input type="text" id="title" name="title" placeholder="Enter announcement title" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="8" placeholder="Enter announcement content" required></textarea>
            </div>

            <button type="submit" class="btn-submit">Post Announcement</button>
        </form>
    </div>
</body>
</html>
