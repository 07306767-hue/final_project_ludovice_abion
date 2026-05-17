<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$activity_id = $_GET['activity_id'] ?? '';
if (!$activity_id || !is_numeric($activity_id)) {
    die('Invalid activity ID.');
}

$user_id = $_SESSION['user']['id'];
$user_role = $_SESSION['user']['role'] ?? 'student';

// Get activity details
$stmt = $conn->prepare('
    SELECT a.id, a.name, a.description, a.total_points, a.due_date, a.created_at, a.attachment_path, c.subject, c.section, c.user_id as class_owner_id
    FROM activities a
    JOIN classes c ON a.class_id = c.classes_id
    LEFT JOIN enrollments e ON c.classes_id = e.class_id AND e.user_id = ?
    WHERE a.id = ? AND (c.user_id = ? OR e.user_id = ?)
');
$stmt->bind_param('iiii', $user_id, $activity_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Activity not found or access denied.');
}
$activity = $result->fetch_assoc();
$stmt->close();

// Check if deadline has passed
$deadline_passed = false;
if ($activity['due_date']) {
    $deadline_passed = strtotime($activity['due_date']) < time();
}

// Check if user is the faculty who created this activity
$is_faculty_creator = ($user_role === 'faculty' && $activity['class_owner_id'] == $user_id);

// Check if already submitted (only relevant for students)
$submission = null;
$already_submitted = false;
if (!$is_faculty_creator) {
    $stmt = $conn->prepare('SELECT id, grade FROM submissions WHERE activity_id = ? AND student_id = ?');
    $stmt->bind_param('ii', $activity_id, $user_id);
    $stmt->execute();
    $submissionResult = $stmt->get_result();
    $submission = $submissionResult->fetch_assoc();
    $stmt->close();
    $already_submitted = $submission !== null;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activity: <?= htmlspecialchars($activity['name']); ?></title>
    <link rel="stylesheet" href="../assets/CSS/activity.css">
    <style>
        
    </style>
</head>
<body>
    <div class="header">
        <h2><?= htmlspecialchars($activity['subject']); ?> - <?= htmlspecialchars($activity['name']); ?></h2>
        <a href="dashboard.php" class="back">Back to Dashboard</a>
    </div>

    <?php $flash_success = getFlashSuccess(); ?>
    <?php if ($flash_success): ?>
        <div class="flash-success">
            ✓ <?= htmlspecialchars($flash_success); ?>
        </div>
    <?php endif; ?>

    <div class="activity-detail-container">
        <div class="activity-header">
            <h2><?= htmlspecialchars($activity['name']); ?></h2>
            <div class="activity-meta">
                <div><strong>Subject:</strong> <?= htmlspecialchars($activity['subject']); ?> - <?= htmlspecialchars($activity['section']); ?></div>
                <?php if ($activity['due_date']): ?>
                    <div><strong>Due Date:</strong> <?= date('M d, Y g:i A', strtotime($activity['due_date'])); ?></div>
                <?php endif; ?>
                <?php if ($activity['total_points']): ?>
                    <div><strong>Total Points:</strong> <?= htmlspecialchars($activity['total_points']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="activity-body">
            <h4>Description</h4>
            <p><?= nl2br(htmlspecialchars($activity['description'])); ?></p>

            <?php if ($activity['attachment_path']): ?>
                <h4>Attachment</h4>
                <div class="attachment-section">
                    <a href="../<?= htmlspecialchars($activity['attachment_path']); ?>" target="_blank" class="attachment-link">
                        📎 Download Attachment
                    </a>
                    <small style="display: block; margin-top: 5px; color: #666;">
                        <?= htmlspecialchars(basename($activity['attachment_path'])); ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($is_faculty_creator): ?>
            <div class="already-submitted" style="background-color: #1a1a1a; border-color: #8b5cf6; border-left-color: #8b5cf6; color: #a7f3d0;">
                <strong style="color: #8b5cf6;">ℹ️ You are the instructor for this activity</strong>
                <p>You cannot submit answers to your own activity. <a href="class_activities.php?class_id=<?= $_GET['class_id'] ?? ''; ?>" style="color: #8b5cf6;">View submissions</a></p>
                <div style="margin-top: 15px;">
                    <a href="edit_activity.php?activity_id=<?= $activity_id; ?>" class="edit-btn" style="background-color: #10b981;">✏️ Edit Activity</a>
                </div>
            </div>
        <?php elseif ($already_submitted): ?>
            <div class="already-submitted">
                <strong>✓ You have already submitted this activity</strong>
                <?php if ($submission['grade']): ?>
                    <div class="grade-section">
                        <h5>Your Grade</h5>
                        <p><?= htmlspecialchars($submission['grade']); ?></p>
                    </div>
                <?php endif; ?>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <a href="edit_activity.php?activity_id=<?= $activity_id; ?>" class="edit-btn">✏️ Edit Submission</a>
                    <form action="../api/delete_submission.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to unsubmit? You will be able to submit again.');">
                        <input type="hidden" name="activity_id" value="<?= htmlspecialchars($activity_id); ?>">
                        <button type="submit" class="delete-btn" style="background-color: #ef4444; color: white; padding: 10px 15px; border-radius: 6px; border: none; cursor: pointer; font-weight: bold; transition: all 0.3s ease;">🗑️ Unsubmit</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <?php if ($deadline_passed): ?>
                <div class="already-submitted" style="background-color: #1a1a1a; border-color: #ef4444; border-left-color: #ef4444; color: #fecaca;">
                    <strong style="color: #ef4444;">⏰ Deadline has passed</strong>
                    <p>The deadline for this activity was on <?= date('M d, Y g:i A', strtotime($activity['due_date'])); ?>. You can no longer submit new answers.</p>
                </div>
            <?php else: ?>
                <div class="submission-section">
                    <h4>Submit Your Answer</h4>
                    <form method="POST" action="../api/store_submission.php" class="submission-form">
                        <input type="hidden" name="activity_id" value="<?= htmlspecialchars($activity_id); ?>">
                        <div class="form-group">
                            <label for="answer">Your Answer</label>
                            <textarea id="answer" name="answer" required placeholder="Enter your answer here..."></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Submit Answer</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
