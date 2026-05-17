<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';
requireLogin();

$class_id = $_GET['class_id'] ?? '';

if (!$class_id) {
    die(json_encode(['error' => 'Class ID is required']));
}

$user = $_SESSION['user'];

if ($user['role'] === 'faculty') {
    // Faculty can see announcements for their own classes
    $stmt = $conn->prepare('SELECT user_id FROM classes WHERE classes_id = ?');
    $stmt->bind_param('i', $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $class = $result->fetch_assoc();
    $stmt->close();

    if (!$class || $class['user_id'] != $user['id']) {
        die(json_encode(['error' => 'Unauthorized']));
    }
} elseif ($user['role'] === 'student') {
    // Students can only see announcements for classes they're enrolled in
    $stmt = $conn->prepare(
        'SELECT e.class_id FROM enrollments e WHERE e.class_id = ? AND e.user_id = ?'
    );
    $stmt->bind_param('ii', $class_id, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die(json_encode(['error' => 'Unauthorized']));
    }
    $stmt->close();
}

// Fetch announcements
$stmt = $conn->prepare(
    'SELECT a.id, a.title, a.content, a.created_at, u.firstname, u.lastname 
     FROM announcements a 
     JOIN users u ON a.faculty_id = u.id 
     WHERE a.class_id = ? 
     ORDER BY a.created_at DESC'
);

if (!$stmt) {
    die(json_encode(['error' => 'Database error: ' . $conn->error]));
}

$stmt->bind_param('i', $class_id);
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

header('Content-Type: application/json');
echo json_encode($announcements);
