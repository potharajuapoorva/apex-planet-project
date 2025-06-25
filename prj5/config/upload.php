<?php
session_start();
require_once 'db.php';

// Redirect if not logged in or not a student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $userId = $_SESSION['user']['id'];

    if (empty($title) || !isset($_FILES['pdf'])) {
        $message = "Title and PDF file are required.";
    } else {
        $file = $_FILES['pdf'];
        $fileName = uniqid() . "_" . basename($file['name']);
        $filePath = "uploads/" . $fileName;
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($fileType !== 'pdf') {
            $message = "Only PDF files are allowed.";
        } elseif ($file['size'] > 5 * 1024 * 1024) { // Limit to 5MB
            $message = "File is too large. Maximum 5MB allowed.";
        } elseif (!file_exists("uploads")) {
            mkdir("uploads", 0777, true); // Auto create uploads folder if missing
        }

        if (empty($message)) {
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $stmt = $conn->prepare("INSERT INTO assignments (title, file, uploaded_by) VALUES (?, ?, ?)");
                $stmt->execute([$title, $fileName, $userId]);
                $message = "File uploaded successfully.";
            } else {
                $message = "Failed to upload file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Assignment</title>
</head>
<body>
    <h2>Upload Assignment</h2>

    <?php if (!empty($message)): ?>
        <p style="color: <?= strpos($message, 'success') !== false ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>File (PDF only):</label><br>
        <input type="file" name="pdf" accept="application/pdf" required><br><br>

        <button type="submit">Upload</button>
    </form>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
