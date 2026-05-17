<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$user = $_SESSION['user'];
$class_id = $_GET['class_id'] ?? null;

if ($class_id) {
    $stmt = $conn->prepare("
        UPDATE classes 
        SET is_archived = 0 
        WHERE classes_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $class_id, $user['id']);
    $stmt->execute();
    $stmt->close();
}

header("Location: archive.php");
exit;
?>