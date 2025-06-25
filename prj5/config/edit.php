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

if (!$assignment) {
    die("Assignment not found or not yours.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newTitle = htmlspecialchars(trim($_POST["title"]));
    $updateStmt = $conn->prepare("UPDATE assignments SET title = ? WHERE id = ?");
    $updateStmt->execute([$newTitle, $id]);
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Edit Assignment</title></head>
<body>
<h2>Edit Assignment</h2>
<form method="post">
    Title: <input type="text" name="title" value="<?= htmlspecialchars($assignment['title']) ?>" required><br><br>
    <input type="submit" value="Update">
</form>
<a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
