<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$user = $_SESSION['user'];
$class_id = $_GET['class_id'] ?? null;

if ($class_id) {

    // delete enrollments first
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE class_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();

    // delete class
    $stmt = $conn->prepare("
        DELETE FROM classes 
        WHERE classes_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $class_id, $user['id']);
    $stmt->execute();
}

header("Location: archive.php");
exit;
?>