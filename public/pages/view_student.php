<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$user = $_SESSION['user'];

$class_id = $_GET['class_id'] ?? null;
$student_id = $_GET['user_id'] ?? null;

if (!$class_id) {
    header("Location: dashboard.php");
    exit();
}

/* Get class info - verify faculty owns this class */
$stmt = $conn->prepare("
    SELECT subject, section, class_code
    FROM classes
    WHERE classes_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $class_id, $user['id']);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

if (!$class) {
    die('Class not found or access denied.');
}
$stmt->close();

/* Get specific student info if user_id provided */
$student = null;
if ($student_id) {
    $stmt = $conn->prepare("
        SELECT u.id, u.firstname, u.lastname, u.email
        FROM users u
        JOIN enrollments e ON u.id = e.user_id
        WHERE e.class_id = ? AND u.id = ?
    ");
    $stmt->bind_param("ii", $class_id, $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$student) {
        die('Student not found in this class.');
    }
    
    /* Get activities for this class */
    $stmt = $conn->prepare("
        SELECT id, name, description, created_at, total_points
        FROM activities
        WHERE class_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    /* Get submissions for this student */
    $submissions = [];
    foreach ($activities as $activity) {
        $stmt = $conn->prepare("
            SELECT id, answer, grade, submitted_at, graded_at, attachment_path
            FROM submissions
            WHERE activity_id = ? AND student_id = ?
        ");
        $stmt->bind_param("ii", $activity['id'], $student_id);
        $stmt->execute();
        $submission = $stmt->get_result()->fetch_assoc();
        $submissions[$activity['id']] = $submission;
        $stmt->close();
    }
} else {
    /* Get all students in this class */
    $stmt = $conn->prepare("
        SELECT u.id, u.firstname, u.lastname, u.email
        FROM enrollments e
        JOIN users u ON e.user_id = u.id
        WHERE e.class_id = ?
        ORDER BY u.firstname ASC
    ");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $activities = [];
    $submissions = [];
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?php echo $student_id ? 'Student Activity' : 'Class Students'; ?></title>
<link rel="stylesheet" href="../assets/CSS/view_student.css">
</head>

<body>

<div class="header">
    <div class="left">
        <img src="../assets/images/logo.png" class="logo">
        <h1 class="brand">A.I Academy</h1>
    </div>

    <div class="middle">
        <?php if ($student): ?>
            <h2><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?> - Activity</h2>
        <?php else: ?>
            <h2><?= htmlspecialchars($class['subject']); ?> - Students</h2>
        <?php endif; ?>
    </div>

    <div class="right">
        <a href="view_class.php?class_id=<?= $class_id; ?>" class="logout">Back</a>
    </div>
</div>

<div class="container">
    <div class="page-card">
        <div class="page-meta">
            <span><strong>Subject:</strong> <?= htmlspecialchars($class['subject']); ?></span>
            <span><strong>Section:</strong> <?= htmlspecialchars($class['section']); ?></span>
            <span><strong>Code:</strong> <?= htmlspecialchars($class['class_code']); ?></span>
        </div>

    <hr>

    <?php if ($student): ?>
        <!-- Show specific student's activities and submissions -->
        <h3>Activities Assigned to <?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></h3>
        <div class="student-overview">
            <p><strong>Email:</strong> <?= htmlspecialchars($student['email']); ?></p>
        </div>

        <?php if (count($activities) > 0): ?>
            <?php foreach ($activities as $activity): ?>
                <div class="activity-card">
                    <div class="activity-summary">
                        <h5><?= htmlspecialchars($activity['name']); ?></h5>
                        <div class="activity-meta">
                            <p><strong>Description:</strong> <?= htmlspecialchars($activity['description']); ?></p>
                            <p><strong>Created:</strong> <?= date('M d, Y H:i', strtotime($activity['created_at'])); ?></p>
                            <p><strong>Total points:</strong> <?= htmlspecialchars($activity['total_points']); ?></p>
                        </div>
                    </div>

                    <div class="submission-card">
                        <?php if (isset($submissions[$activity['id']]) && $submissions[$activity['id']]): ?>
                            <?php $sub = $submissions[$activity['id']]; ?>
                            <p class="status-message"><strong>Status:</strong> <span class="status-tag status-submitted">Submitted</span></p>
                            <p class="status-message"><strong>Submitted:</strong> <?= date('M d, Y H:i', strtotime($sub['submitted_at'])); ?></p>
                            <p class="status-message"><strong>Answer:</strong></p>
                            <div class="response-box">
                                <?= nl2br(htmlspecialchars($sub['answer'])); ?>
                            </div>

                            <?php if (!empty($sub['attachment_path'])): ?>
                                <p class="status-message"><strong>Attachment:</strong></p>
                                <p>
                                    <a href="../<?= htmlspecialchars($sub['attachment_path']); ?>" target="_blank" class="attachment-link">
                                        📎 <?= htmlspecialchars(basename($sub['attachment_path'])); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($sub['grade']): ?>
                                <p class="status-message">
                                    <strong>Grade:</strong> <span class="grade-badge"><?= htmlspecialchars($sub['grade']); ?></span>
                                    <br>
                                    <strong>Graded on:</strong> <?= date('M d, Y H:i', strtotime($sub['graded_at'])); ?>
                                </p>
                                <form method="POST" action="../api/grade_submission.php" class="grade-form">
                                    <input type="hidden" name="submission_id" value="<?= htmlspecialchars($sub['id']); ?>">
                                    <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id); ?>">
                                    <input type="hidden" name="redirect" value="../pages/view_student.php?class_id=<?= $class_id; ?>&user_id=<?= $student_id; ?>">
                                    <input type="number" step="0.01" min="0" max="<?= htmlspecialchars($activity['total_points'] ?? ''); ?>" name="grade" value="<?= htmlspecialchars($sub['grade']); ?>" required>
                                    <button type="submit" class="submit-btn">Update Grade</button>
                                </form>
                            <?php else: ?>
                                <p class="status-message">
                                    <strong>Grade:</strong> <span class="no-submission">Not yet graded</span>
                                </p>
                                <form method="POST" action="../api/grade_submission.php" class="grade-form">
                                    <input type="hidden" name="submission_id" value="<?= htmlspecialchars($sub['id']); ?>">
                                    <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id); ?>">
                                    <input type="hidden" name="redirect" value="../pages/view_student.php?class_id=<?= $class_id; ?>&user_id=<?= $student_id; ?>">
                                    <input type="number" step="0.01" min="0" max="<?= htmlspecialchars($activity['total_points'] ?? ''); ?>" name="grade" placeholder="Enter grade" required>
                                    <button type="submit" class="submit-btn">Grade</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="status-message">
                                <strong>Status:</strong> <span class="status-tag status-pending">Not submitted</span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No activities assigned to this class yet.</p>
        <?php endif; ?>

    <?php else: ?>
        <!-- Show list of students in the class -->
        <h3>Enrolled Students</h3>

        <?php if (isset($students) && count($students) > 0): ?>
            <div class="classes-container">

                <?php foreach ($students as $s): ?>
                    <div class="class-card">

                        <h4>
                            <?= htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                        </h4>

                        <p><?= htmlspecialchars($s['email']); ?></p>

                        <div class="card-actions">
                            <a href="view_student.php?class_id=<?= $class_id ?>&user_id=<?= $s['id'] ?>"
                               class="view-students-btn">
                                View Activity
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>

            </div>
        <?php else: ?>
            <p>No students enrolled yet.</p>
        <?php endif; ?>
    <?php endif; ?>

</div>

</body>
</html>