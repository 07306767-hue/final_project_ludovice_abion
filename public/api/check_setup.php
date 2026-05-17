<?php
require_once __DIR__ . '/src/Helpers/_init.php';

echo "<h1>Database Schema Check</h1>";

// Check activities table
echo "<h2>Activities Table:</h2>";
$result = $conn->query("DESCRIBE activities");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} else {
    echo "Error checking activities table: " . $conn->error;
}

echo "<h2>Submissions Table:</h2>";
$result = $conn->query("DESCRIBE submissions");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} else {
    echo "Error checking submissions table: " . $conn->error;
}

// Check if attachment_path columns exist
echo "<h2>Attachment Columns Check:</h2>";
$activities_has_attachment = false;
$submissions_has_attachment = false;

$result = $conn->query("SHOW COLUMNS FROM activities LIKE 'attachment_path'");
$activities_has_attachment = $result->num_rows > 0;

$result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'attachment_path'");
$submissions_has_attachment = $result->num_rows > 0;

echo "Activities table has attachment_path: " . ($activities_has_attachment ? "✅ YES" : "❌ NO") . "<br>";
echo "Submissions table has attachment_path: " . ($submissions_has_attachment ? "✅ YES" : "❌ NO") . "<br>";

if (!$activities_has_attachment || !$submissions_has_attachment) {
    echo "<h3>⚠️ Database Migration Needed</h3>";
    echo "<p>Please run the following SQL commands:</p>";
    echo "<pre>";
    if (!$activities_has_attachment) {
        echo "ALTER TABLE `activities` ADD COLUMN `attachment_path` VARCHAR(500) NULL AFTER `due_date`;\n";
    }
    if (!$submissions_has_attachment) {
        echo "ALTER TABLE `submissions` ADD COLUMN `attachment_path` VARCHAR(500) NULL AFTER `answer`;\n";
    }
    echo "</pre>";
} else {
    echo "<h3>✅ Database is ready for file attachments!</h3>";
}

// Check upload directories
echo "<h2>Upload Directories Check:</h2>";
$dirs = [
    'uploads/',
    'uploads/activities/',
    'uploads/submissions/'
];

foreach ($dirs as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    $exists = file_exists($full_path);
    $writable = is_writable($full_path);
    echo "$dir: " . ($exists ? "Exists" : "Missing") . " - " . ($writable ? "Writable" : "Not Writable") . "<br>";
}

echo "<h2>PHP Upload Settings:</h2>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
?>