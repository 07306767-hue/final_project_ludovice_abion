<?php
// File Upload Test Script
echo "<h1>File Upload Test</h1>";

// Check PHP upload settings
echo "<h2>PHP Upload Settings:</h2>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";

// Check directory permissions
echo "<h2>Directory Permissions:</h2>";
$dirs = ['uploads/', 'uploads/activities/', 'uploads/submissions/'];
foreach ($dirs as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    echo "$dir: " . (is_writable($full_path) ? 'Writable' : 'Not Writable') . "<br>";
}

// Test form
echo "<h2>Test File Upload:</h2>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='test_file'>";
echo "<input type='submit' value='Upload Test File'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo "<h3>Upload Result:</h3>";
    if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        $temp_path = $_FILES['test_file']['tmp_name'];
        $target_path = __DIR__ . '/uploads/test_' . time() . '_' . basename($_FILES['test_file']['name']);

        if (move_uploaded_file($temp_path, $target_path)) {
            echo "✅ File uploaded successfully: " . basename($target_path) . "<br>";
            echo "<a href='uploads/" . basename($target_path) . "' target='_blank'>View uploaded file</a>";
        } else {
            echo "❌ Failed to move uploaded file";
        }
    } else {
        echo "❌ Upload error: " . $_FILES['test_file']['error'];
    }
}
?>