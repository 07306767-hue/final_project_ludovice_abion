<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$class_id = $_GET['class_id'] ?? null;

if ($class_id) {
    $stmt = $conn->prepare("UPDATE classes SET is_archived = 1 WHERE classes_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: dashboard.php");
exit;
?>