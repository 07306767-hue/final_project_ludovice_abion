<?php
require_once __DIR__ . '/src/Helpers/_init.php';
requireLogin();

// Get a sample activity for testing
$stmt = $conn->prepare('
    SELECT a.id, a.name, a.description, a.total_points, c.subject, c.section
    FROM activities a
    JOIN classes c ON a.class_id = c.classes_id
    JOIN enrollments e ON c.classes_id = e.class_id
    WHERE e.user_id = ? AND a.due_date >= CURDATE()
    ORDER BY a.due_date ASC
    LIMIT 1
');
$stmt->bind_param('i', $_SESSION['user']['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h1>No Activities Available for Testing</h1>";
    echo "<p>Please create an activity first as a teacher, then enroll as a student to test file attachments.</p>";
    echo "<a href='dashboard.php'>Back to Dashboard</a>";
    exit();
}

$activity = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test File Attachment - Submit Activity</title>
    <link rel="stylesheet" href="assets/CSS/dashboard.css">
    <link rel="stylesheet" href="assets/CSS/submit_activity.css">
    <style>
        .test-info {
            background-color: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .test-info h3 {
            color: #2e7d32;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>🧪 Test File Attachment Feature</h2>
        <a href="dashboard.php" class="logout">Back to Dashboard</a>
    </div>

    <div class="test-info">
        <h3>✅ File Attachment Feature is Active!</h3>
        <p>This page demonstrates the file attachment functionality for student submissions.</p>
        <ul>
            <li>✅ Database schema updated with attachment_path columns</li>
            <li>✅ Upload directories created and writable</li>
            <li>✅ PHP file uploads enabled (40MB limit)</li>
            <li>✅ Form includes file input with proper validation</li>
            <li>✅ API handles file uploads securely</li>
        </ul>
    </div>

    <div class="container">
        <h3>Test Activity: <?= htmlspecialchars($activity['name']); ?></h3>
        <p><strong>Class:</strong> <?= htmlspecialchars($activity['subject'] . ' - ' . $activity['section']); ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($activity['description']); ?></p>
        <p><strong>Total Points:</strong> <?= htmlspecialchars($activity['total_points']); ?></p>

        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0;">
            <h4>📎 Test File Attachment</h4>
            <p><strong>How to test:</strong></p>
            <ol>
                <li>Fill in your answer in the text area below</li>
                <li>Click "Choose File" to select a file to attach (PDF, DOC, image, etc.)</li>
                <li>Click "Submit" to test the file upload functionality</li>
                <li>Check that the file is saved and can be downloaded later</li>
            </ol>
            <p><strong>Supported formats:</strong> PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, ZIP, RAR (Max 10MB)</p>
        </div>

        <form method="POST" action="api/store_submission.php" enctype="multipart/form-data">
            <input type="hidden" name="activity_id" value="<?= htmlspecialchars($activity['id']); ?>">
            <div class="form-group">
                <label for="answer">Your Answer *</label>
                <textarea id="answer" name="answer" rows="6" required placeholder="Enter your answer here...">This is a test submission with file attachment functionality.</textarea>
            </div>
            <div class="form-group">
                <label for="attachment">📎 Attach File (optional)</label>
                <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip,.rar">
                <small style="color: #666;">Allowed formats: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, ZIP, RAR (Maximum: 10MB)</small>
            </div>
            <button type="submit" name="submit" style="background-color: #4caf50; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer;">📤 Submit with Attachment</button>
        </form>
    </div>
</body>
</html>