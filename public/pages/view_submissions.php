<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');

$activity_id = $_GET['activity_id'] ?? '';
$class_id = $_GET['class_id'] ?? '';

if (!$activity_id || !is_numeric($activity_id) || !$class_id || !is_numeric($class_id)) {
    die('Invalid activity or class ID.');
}

$user_id = $_SESSION['user']['id'];

// Get activity details and verify faculty owns the class
$stmt = $conn->prepare('
    SELECT a.id, a.name, a.description, a.total_points, c.subject, c.section, c.user_id
    FROM activities a
    JOIN classes c ON a.class_id = c.classes_id
    WHERE a.id = ? AND c.classes_id = ?
');
$stmt->bind_param('ii', $activity_id, $class_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Activity not found or access denied.');
}
$activity = $result->fetch_assoc();

// Check if faculty owns this class
if ($activity['user_id'] != $user_id) {
    die('Access denied.');
}
$stmt->close();

// Get all submissions for this activity
$stmt = $conn->prepare('
    SELECT s.id, s.answer, s.grade, s.submitted_at, s.attachment_path, u.id as student_id, u.firstname, u.lastname, u.email
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.activity_id = ?
    ORDER BY s.submitted_at DESC
');
$stmt->bind_param('i', $activity_id);
$stmt->execute();
$submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submissions - <?= htmlspecialchars($activity['name']); ?></title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/view_submissions.css">
</head>
<body>
    <div class="header">
        <h2>Submissions - <?= htmlspecialchars($activity['name']); ?></h2>
        <a href="class_activities.php?class_id=<?= $class_id; ?>" class="back-btn">Back to Activities</a>
    </div>

    <div class="submissions-container">
        <div class="activity-info">
            <h3><?= htmlspecialchars($activity['name']); ?></h3>
            <p><strong>Subject:</strong> <?= htmlspecialchars($activity['subject'] . ' - ' . $activity['section']); ?></p>
            <p><strong>Description:</strong></p>
            <p class="description-text"><?= nl2br(htmlspecialchars($activity['description'])); ?></p>
            <?php if ($activity['total_points']): ?>
                <p><strong>Total Points:</strong> <?= htmlspecialchars($activity['total_points']); ?></p>
            <?php endif; ?>
            <hr>
        </div>

        <?php if (count($submissions) > 0): ?>
            <div class="submissions-list">
                <h4>Total Submissions: <?= count($submissions); ?></h4>
                
                <?php foreach ($submissions as $index => $sub): ?>
                    <div class="submission-card">
                        <div class="submission-header">
                            <div class="student-info">
                                <h5><?= htmlspecialchars($sub['firstname'] . ' ' . $sub['lastname']); ?></h5>
                                <p class="email"><?= htmlspecialchars($sub['email']); ?></p>
                                <p class="submitted-date">Submitted: <?= date('M d, Y g:i A', strtotime($sub['submitted_at'])); ?></p>
                            </div>
                            <div class="grade-badge">
                                <?php if ($sub['grade']): ?>
                                    <span class="graded">Graded: <?= htmlspecialchars($sub['grade']); ?></span>
                                <?php else: ?>
                                    <span class="ungraded">Not Graded</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="submission-body">
                            <h6>Answer:</h6>
                            <div class="answer-text"><?= nl2br(htmlspecialchars($sub['answer'])); ?></div>

                            <?php if ($sub['attachment_path']): ?>
                                <h6>Attachment:</h6>
                                <div class="attachment-section">
                                    <a href="../<?= htmlspecialchars($sub['attachment_path']); ?>" target="_blank" class="attachment-link">
                                        📎 Download Attachment
                                    </a>
                                    <small style="display: block; margin-top: 5px; color: #666;">
                                        <?= htmlspecialchars(basename($sub['attachment_path'])); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="submission-actions">
                            <form method="POST" action="../api/grade_submission.php" class="grade-form">
                                <input type="hidden" name="submission_id" value="<?= $sub['id']; ?>">
                                <input type="hidden" name="class_id" value="<?= $class_id; ?>">
                                <input type="number" step="0.01" min="0" max="<?= htmlspecialchars($activity['total_points']); ?>" name="grade" value="<?= htmlspecialchars($sub['grade'] ?? ''); ?>" placeholder="Enter grade (max: <?= htmlspecialchars($activity['total_points']); ?>)" required>
                                <button type="submit"><?php echo $sub['grade'] ? 'Update Grade' : 'Submit Grade'; ?></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-submissions">
                <p>No submissions yet.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
