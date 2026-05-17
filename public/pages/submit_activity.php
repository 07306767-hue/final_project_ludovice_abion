<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('student'); 

$activity_id = $_GET['activity_id'] ?? '';
if (!$activity_id || !is_numeric($activity_id)) {
    die(ERR_INVALID_ACTIVITY_ID); 
}

$user_id = $_SESSION['user']['id'];

// Check if activity exists and student is enrolled
// Try-catch: Database operations wrapped in try-catch blocks in the called functions
$stmt = $conn->prepare('
    SELECT a.name, a.description, a.score
    FROM activities a
    JOIN classes c ON a.class_id = c.classes_id
    JOIN enrollments e ON c.classes_id = e.class_id
    WHERE a.id = ? AND e.user_id = ?
');
$stmt->bind_param('ii', $activity_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die(ERR_ACTIVITY_NOT_FOUND); 
}
$activity = $result->fetch_assoc();
$stmt->close();

// Check if already submitted
$stmt = $conn->prepare('SELECT id FROM submissions WHERE activity_id = ? AND student_id = ?');
$stmt->bind_param('ii', $activity_id, $user_id);
$stmt->execute();
$already_submitted = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($already_submitted) {
    die(ERR_ALREADY_SUBMITTED); 
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Activity</title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/submit_activity.css">
</head>
<body>
    <div class="header">
        <h2>Submit Activity: <?= htmlspecialchars($activity['name']); ?></h2>
        <a href="dashboard.php" class="logout">Back to Dashboard</a>
    </div>

    <div class="container">
        <p><strong>Description:</strong> <?= htmlspecialchars($activity['description']); ?></p>
        <p><strong>Score:</strong> <?= htmlspecialchars($activity['score']); ?></p>
        <form method="POST" action="../api/store_submission.php" enctype="multipart/form-data">
            <input type="hidden" name="activity_id" value="<?= htmlspecialchars($activity_id); ?>">
            <div class="form-group">
                <label for="answer">Your Answer</label>
                <textarea id="answer" name="answer" required></textarea>
            </div>
            <div class="form-group">
                <label for="attachment">Attachment (optional)</label>
                <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip,.rar">
                <small>Allowed formats: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, ZIP, RAR (Max: 10MB)</small>
            </div>
            <button type=\"submit\" name=\"submit\">Submit</button>
        </form>
    </div>
</body>
</html>
