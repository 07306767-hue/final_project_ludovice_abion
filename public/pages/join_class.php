<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

$error = '';
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
} elseif (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Join Class</title>
    <link rel="stylesheet" href="../assets/CSS/join_class.css">
</head>

<body>

<div class="header">
    <h2>Join Class</h2>
    <a href="dashboard.php" class="back">Back to Dashboard</a>
</div>

<div class="container">

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="../api/store_join.php">
        <div class="form-group">
            <label for="class_code">Class Code</label>
            <input type="text" id="class_code" name="class_code" required maxlength="10">
        </div>

        <button type="submit">Join Class</button>
    </form>

</div>

</body>
</html>