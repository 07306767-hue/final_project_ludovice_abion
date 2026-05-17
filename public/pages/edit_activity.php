<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$activity_id = $_GET['activity_id'] ?? '';
$class_id = $_GET['class_id'] ?? '';

if (!$activity_id || !is_numeric($activity_id)) {
    die('Invalid activity ID.');
}

$user_id = $_SESSION['user']['id'];
$user_role = $_SESSION['user']['role'];

// Get activity details
$stmt = $conn->prepare('
    SELECT a.id, a.name, a.description, a.total_points, a.due_date, a.class_id, a.attachment_path, c.subject, c.section, c.user_id as class_owner_id
    FROM activities a
    JOIN classes c ON a.class_id = c.classes_id
    WHERE a.id = ?
');
$stmt->bind_param('i', $activity_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Activity not found.');
}
$activity = $result->fetch_assoc();
$stmt->close();

// Check authorization
$is_faculty = ($user_role === 'faculty' && $activity['class_owner_id'] == $user_id);
$is_student_enrolled = false;

if (!$is_faculty) {
    if ($user_role === 'student') {
        $stmt = $conn->prepare('SELECT enrollment_id FROM enrollments WHERE class_id = ? AND user_id = ?');
        $stmt->bind_param('ii', $activity['class_id'], $user_id);
        $stmt->execute();
        $is_student_enrolled = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    }
}

if (!$is_faculty && !$is_student_enrolled) {
    die('Access denied.');
}

// Check if deadline has passed (for students)
$deadline_passed = false;
if ($activity['due_date']) {
    $deadline_passed = strtotime($activity['due_date']) < time();
}

// Get student submission if student
$submission = null;
if (!$is_faculty) {
    $stmt = $conn->prepare('SELECT id, answer, attachment_path FROM submissions WHERE activity_id = ? AND student_id = ?');
    $stmt->bind_param('ii', $activity_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submission = $result->fetch_assoc();
    $stmt->close();
}

if ($is_student_enrolled && $deadline_passed && !$submission) {
    header('Location: activity.php?activity_id=' . $activity_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit - <?= htmlspecialchars($activity['name']); ?></title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/edit_activity.css">
</head>
<body>
    <div class="page-header">
        <div class="page-title">
            <h2>Edit Activity</h2>
        </div>
        <a href="activity.php?activity_id=<?= $activity_id; ?>" class="logout page-back">Back to Activity</a>
    </div>

    <div class="edit-container">
        <?php if ($is_faculty): ?>
            <!-- Faculty Edit Form -->
            <div class="edit-form-wrapper">
                <h3>Edit Activity</h3>
                <form method="POST" action="../api/update_activity.php" enctype="multipart/form-data" class="edit-form">
                    <input type="hidden" name="activity_id" value="<?= htmlspecialchars($activity_id); ?>">

                    <div class="form-group">
                        <label for="name">Activity Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($activity['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="6" required><?= htmlspecialchars($activity['description']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="total_points">Total Points</label>
                            <input type="number" id="total_points" name="total_points" value="<?= htmlspecialchars($activity['total_points']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" id="due_date" name="due_date" value="<?= $activity['due_date'] ? htmlspecialchars($activity['due_date']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Update Attachment (optional)</label>
                        <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip,.rar">
                        <small>Allowed formats: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, ZIP, RAR (Max: 10MB)</small>
                        <?php if ($activity['attachment_path']): ?>
                            <div class="current-attachment">
                                <small>Current attachment: <a href="../<?= htmlspecialchars($activity['attachment_path']); ?>" target="_blank"><?= htmlspecialchars(basename($activity['attachment_path'])); ?></a></small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-save">Save Changes</button>
                </form>
            </div>

        <?php elseif ($is_student_enrolled && $submission): ?>
            <!-- Student Edit Submission Form -->
            <div class="edit-form-wrapper">
                <h3>Edit Your Submission</h3>
                <?php if ($deadline_passed): ?>
                    <div class="warning-message">
                        ⚠️ The deadline for this activity has passed. You can still edit your submission, but no new submissions will be accepted after the deadline.
                    </div>
                <?php endif; ?>

                <form method="POST" action="../api/update_submission.php" enctype="multipart/form-data" class="edit-form">
                    <input type="hidden" name="activity_id" value="<?= htmlspecialchars($activity_id); ?>">

                    <div class="activity-info">
                        <h4><?= htmlspecialchars($activity['name']); ?></h4>
                        <p><strong>Class:</strong> <?= htmlspecialchars($activity['subject'] . ' - Section ' . $activity['section']); ?></p>
                        <p><strong>Description:</strong></p>
                        <p class="description-text"><?= nl2br(htmlspecialchars($activity['description'])); ?></p>
                    </div>

                    <div class="form-group">
                        <label for="answer">Your Answer</label>
                        <textarea id="answer" name="answer" rows="10" required><?= htmlspecialchars($submission['answer']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Update Attachment (optional)</label>
                        <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip,.rar">
                        <small>Allowed formats: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, ZIP, RAR (Max: 10MB)</small>
                        <?php if ($submission['attachment_path']): ?>
                            <div class="current-attachment">
                                <small>Current attachment: <a href="../<?= htmlspecialchars($submission['attachment_path']); ?>" target="_blank"><?= htmlspecialchars(basename($submission['attachment_path'])); ?></a></small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-save">Update Submission</button>
                </form>
            </div>

        <?php else: ?>
            <div class="error-message">
                You don't have a submission for this activity yet or are not authorized to edit.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
