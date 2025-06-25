<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ? AND uploaded_by = ?");
$stmt->execute([$id, $_SESSION['user']['id']]);
$assignment = $stmt->fetch();

if ($assignment) {
    // Delete file
    if (file_exists("uploads/" . $assignment['file'])) {
        unlink("uploads/" . $assignment['file']);
    }

    // Delete from DB
    $delStmt = $conn->prepare("DELETE FROM assignments WHERE id = ?");
    $delStmt->execute([$id]);
}

header("Location: dashboard.php");
exit;
