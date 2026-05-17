<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$user = $_SESSION['user'];

$stmt = $conn->prepare("
    SELECT classes_id, subject, section, class_code
    FROM classes
    WHERE user_id = ? AND is_archived = 1
    ORDER BY subject ASC
");

$stmt->bind_param("i", $user['id']);
$stmt->execute();
$archiveClasses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Archive</title>
<link rel="stylesheet" href="../assets/CSS/dashboard.css">
<link rel="stylesheet" href="../assets/CSS/archive.css">
</head>

<body>

<div class="header">
    <div class="left">
        <img src="../assets/images/logo.png" class="logo">
        <h1 class="brand">A.I Academy</h1>
    </div>

    <div class="middle">
        <h2>Archive</h2>
    </div>

    <div class="right">
        <a href="dashboard.php" class="logout">Back</a>
    </div>
</div>

<div class="container">

<h3>Archived Classes</h3>

<?php if (count($archiveClasses) > 0): ?>
    <div class="classes-container">

        <?php foreach ($archiveClasses as $class): ?>
            <div class="class-card">

                <h4><?= htmlspecialchars($class['subject']); ?></h4>
                <p><?= htmlspecialchars($class['section']); ?></p>
                <p><?= htmlspecialchars($class['class_code']); ?></p>

                <div class="card-actions">

                    <a href="restore_class.php?class_id=<?= $class['classes_id']; ?>"
                       class="view-students-btn">
                        Restore
                    </a>

                    <a href="permanent_delete_class.php?class_id=<?= $class['classes_id']; ?>"
                       class="delete-class-btn"
                       onclick="return confirm('Delete permanently?')">
                        Delete Forever
                    </a>

                </div>

            </div>
        <?php endforeach; ?>

    </div>
<?php else: ?>
    <p>No archived classes.</p>
<?php endif; ?>

</div>

</body>
</html>