<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireRole('faculty');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Class</title>
    <link rel="stylesheet" href="../assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../assets/CSS/create_class.css">
</head>
<body>
    <div class="header">
        <h2>Create Class</h2>
        <a href="dashboard.php" class="logout">Back to Dashboard</a>
    </div>

    <div class="create-class">
        <form method="POST" action="../api/store_class.php">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="section">Section</label>
                <input type="text" id="section" name="section" 
                       pattern="[A-Za-z]" maxlength="1" 
                       placeholder="Enter a single letter (A-Z)" 
                       title="Section must be a single letter (A-Z)"
                       required>
            </div>
            <button type="submit">Create Class</button>
        </form>
    </div>
</body>
</html>
