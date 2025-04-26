<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
include 'db.php';

$username = $_SESSION['username'];
$result = $conn->query("SELECT * FROM assignments WHERE student_username = '$username' ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Assignment Marks</title>
    <style>
        body { font-family: Arial; padding: 40px; background-color: #f0f2f5; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #28a745; color: white; }
        tr:hover { background-color: #f1f1f1; }
        h2 { text-align: center; margin-bottom: 25px; color: #333; }
    </style>
</head>
<body>

<h2>📊 My Assignment Results</h2>

<table>
    <tr>
        <th>Assignment Title</th>
        <th>File</th>
        <th>Submitted At</th>
        <th>Marks</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['assignment_title']) ?></td>
        <td><a href="<?= $row['file_path'] ?>" target="_blank">📥 Download</a></td>
        <td><?= date('F j, Y H:i', strtotime($row['uploaded_at'])) ?></td>
        <td><?= is_null($row['marks']) ? "Pending" : $row['marks'] . "" ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
