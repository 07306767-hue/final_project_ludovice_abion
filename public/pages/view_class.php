<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$user = $_SESSION['user'];

if ($user['role'] !== 'faculty') {
    header("Location: dashboard.php");
    exit();
}

$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    die("Invalid class ID");
}

/* GET CLASS INFO */
$stmt = $conn->prepare("SELECT subject, section, class_code FROM classes WHERE classes_id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

/* GET STUDENTS */
$stmt = $conn->prepare("
    SELECT u.id, u.firstname, u.lastname, u.email
    FROM users u
    JOIN enrollments e ON u.id = e.user_id
    WHERE e.class_id = ?
");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Class</title>
    <link rel="stylesheet" href="../assets/CSS/view_class.css">
</head>

<body>

<div class="header">
    <div class="left">
        <img src="../assets/images/logo.png" class="logo">
        <h1 class="brand">A.I Academy</h1>
    </div>

    <div class="middle">
        <h2><?= htmlspecialchars($class['subject']); ?> - Students</h2>
    </div>

    <div class="right">
        <a href="dashboard.php" class="logout">Back</a>
    </div>
</div>

<div class="container">

    <h3>Section: <?= htmlspecialchars($class['section']); ?></h3>
    <h4>Class Code: <?= htmlspecialchars($class['class_code']); ?></h4>

    <div>
        <a href="create_activity.php?class_id=<?= htmlspecialchars($class_id); ?>" class="create-activity-btn">
            Create Activity
        </a>
        <a href="class_activities.php?class_id=<?= htmlspecialchars($class_id); ?>" class="view-activities-btn">
            View Activities
        </a>
        
    </div>

    <hr>

    <h3>Enrolled Students</h3>

    <?php if (count($students) > 0): ?>
        <div class="classes-container">

            <?php foreach ($students as $s): ?>
                <div class="class-card">

                    <h4>
                        <a href="view_student.php?class_id=<?= $class_id ?>&user_id=<?= $s['id'] ?>" style="color:white; text-decoration:none;">
                            <?= htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?>
                        </a>
                    </h4>

                    <p><?= htmlspecialchars($s['email']); ?></p>

                    <div class="card-actions">

                        <a href="view_student.php?class_id=<?= $class_id ?>&user_id=<?= $s['id'] ?>"
                           class="view-activity-btn">
                            View Activity
                        </a>

                        <a href="kick_student.php?class_id=<?= $class_id ?>&user_id=<?= $s['id'] ?>"
                           class="delete-class-btn"
                           onclick="return confirm('Remove this student from class?')">
                            Remove
                        </a>

                    </div>

                </div>
            <?php endforeach; ?>

        </div>

    <?php else: ?>
        <p>No students enrolled yet.</p>
    <?php endif; ?>
<?php if ($user['role'] === 'faculty'): ?>

    <div style="margin-bottom: 20px; text-align:right;">
        <a href="delete_class.php?class_id=<?= $class_id; ?>"
           class="delete-class-btn"
           onclick="return confirm('Move this class to trash?')">
            Archive
        </a>
    </div>

<?php endif; ?>
</div>

</body>
</html>