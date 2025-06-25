<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$role = $user['role'];

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search
$search = $_GET['search'] ?? '';
$searchTerm = "%" . $search . "%";

if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT a.*, u.name FROM assignments a JOIN users u ON a.uploaded_by = u.id WHERE a.title LIKE ? ORDER BY a.id DESC LIMIT ?, ?");
    $stmt->bindParam(1, $searchTerm);
    $stmt->bindParam(2, $start, PDO::PARAM_INT);
    $stmt->bindParam(3, $limit, PDO::PARAM_INT);
} else {
    $stmt = $conn->prepare("SELECT a.*, u.name FROM assignments a JOIN users u ON a.uploaded_by = u.id WHERE a.uploaded_by = ? AND a.title LIKE ? ORDER BY a.id DESC LIMIT ?, ?");
    $stmt->bindParam(1, $user['id'], PDO::PARAM_INT);
    $stmt->bindParam(2, $searchTerm);
    $stmt->bindParam(3, $start, PDO::PARAM_INT);
    $stmt->bindParam(4, $limit, PDO::PARAM_INT);
}

$stmt->execute();
$assignments = $stmt->fetchAll();

// Count total
if ($role === 'admin') {
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM assignments WHERE title LIKE ?");
    $countStmt->execute([$searchTerm]);
} else {
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM assignments WHERE uploaded_by = ? AND title LIKE ?");
    $countStmt->execute([$user['id'], $searchTerm]);
}
$total = $countStmt->fetchColumn();
$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body>
<h2>Welcome <?= htmlspecialchars($user['name']) ?> (<?= $role ?>)</h2>
<a href="logout.php">Logout</a> |
<?php if ($role === 'student'): ?>
<a href="upload.php">Upload Assignment</a>
<?php endif; ?>

<form method="GET">
    <input type="text" name="search" placeholder="Search by title" value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<h3>Assignments</h3>
<table border="1" cellpadding="10">
<tr>
    <th>Title</th>
    <th>Uploaded By</th>
    <th>File</th>
    <?php if ($role === 'student'): ?>
        <th>Actions</th>
    <?php endif; ?>
</tr>

<?php foreach ($assignments as $a): ?>
<tr>
    <td><?= htmlspecialchars($a['title']) ?></td>
    <td><?= htmlspecialchars($a['name']) ?></td>
    <td><a href="uploads/<?= htmlspecialchars($a['file']) ?>" target="_blank">View</a></td>
    <?php if ($role === 'student'): ?>
        <td>
            <a href="edit.php?id=<?= $a['id'] ?>">Edit</a> |
            <a href="delete.php?id=<?= $a['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
        </td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>
</table>

<p>Pages:
<?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
<?php endfor; ?>
</p>
</body>
</html>
