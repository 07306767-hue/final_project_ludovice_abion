<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$user = $_SESSION['user'];
$myClasses = [];
$joinedClasses = [];

if ($user['role'] === 'faculty') {
    $stmt = $conn->prepare('SELECT classes_id, subject, section, class_code FROM classes WHERE user_id = ? AND is_archived = 0 ORDER BY subject DESC');
    if ($stmt) {
        $stmt->bind_param('i', $user['id']);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $myClasses = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $myClasses = []; // Query failed
        }
        $stmt->close();
    } else {
        $myClasses = []; // Prepare failed
    }
} elseif ($user['role'] === 'student') {
    $stmt = $conn->prepare('SELECT c.classes_id, c.subject, c.section, c.class_code FROM classes c JOIN enrollments e ON c.classes_id = e.class_id WHERE e.user_id = ? AND c.is_archived = 0 ORDER BY c.subject DESC');
    if ($stmt) {
        $stmt->bind_param('i', $user['id']);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $joinedClasses = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $joinedClasses = []; // Query failed
        }
        $stmt->close();
    } else {
        $joinedClasses = []; // Prepare failed
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<link rel="stylesheet" href="../assets/CSS/dashboard.css">
</head>

<body>

<div class="header">
    <div class="left">
        <img src="../assets/images/logo.png" class="logo">
        <h1 class="brand">A.I Academy</h1>
    </div>
    <div class="middle">
        <h2>Welcome, <?= $user['firstname'] . ' ' . $user['lastname']; ?></h2>
    </div>
    <div class="right">
        <a href="../api/logout.php" class="logout">Logout</a>
    </div>

    
</div>

<div class="container">
<?php if ($user['role'] === 'faculty'): ?>
    <div class="dashboard-actions">
        <a href="archive.php" class="trash-btn">Archive</a>
    </div>
<?php endif; ?>
    <?php $error = getFlashError(); ?>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php $success = getFlashSuccess(); ?>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <p><strong>Role:</strong> <?= $user['role']; ?></p>

    <?php if ($user['role'] == 'faculty'): ?>

        <h3>Faculty Panel</h3>
        <a href="create_class.php" style="text-decoration: none;"><button>Create Class</button></a>
        <a href="create_announcement.php" style="text-decoration: none;"><button>Create Announcement</button></a>



        <h3>My Classes</h3>
        <?php if (count($myClasses) > 0): ?>
            <div class="classes-container">
                <?php foreach ($myClasses as $class): ?>
                    <div class="class-card">
                        <h4><?= htmlspecialchars($class['subject']); ?></h4>
                        <p><strong>Section:</strong> <?= htmlspecialchars($class['section']); ?></p>
                        <p><strong>Class Code:</strong> <?= htmlspecialchars($class['class_code']); ?></p>
                        <div class="card-actions">
                            <a href="view_class.php?class_id=<?= $class['classes_id'] ?>" class="view-class-btn">
                            View Class
                            </a>
                            <a href="view_announcements.php?class_id=<?= $class['classes_id'] ?>" class="view-class-btn" style="background-color: #28a745;">
                            Announcements
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No classes created yet.</p>
        <?php endif; ?>

    <?php else: ?>

        <h3>Student Panel</h3>
        <a href="join_class.php" style="text-decoration: none;"><button>Join Class</button></a>
        <a href="calendar.php" style="text-decoration: none;"><button>View Activity Calendar</button></a>

        <h3>My Courses</h3>
        <?php if (count($joinedClasses) > 0): ?>
            <div class="classes-container">
                <?php foreach ($joinedClasses as $class): ?>
                    <div class="class-card">
                        <h4><?= htmlspecialchars($class['subject']); ?></h4>
                        <p><strong>Section:</strong> <?= htmlspecialchars($class['section']); ?></p>
                        <p><strong>Class Code:</strong> <?= htmlspecialchars($class['class_code']); ?></p>
                        <div class="card-actions">
                            <a href="class_view.php?class_id=<?= $class['classes_id']; ?>" class="view-class-btn" style="display: inline-block; background-color: #007bff; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-weight: bold;">View Class & Activities</a>
                            <a href="view_announcements.php?class_id=<?= $class['classes_id']; ?>" class="view-class-btn" style="display: inline-block; background-color: #28a745; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; margin-left: 5px;">Announcements</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No courses joined yet.</p>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>