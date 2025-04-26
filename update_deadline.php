<?php
// Error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
date_default_timezone_set('Africa/Nairobi');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $assignment_id = $_POST['assignment_id'];
    $new_deadline = $_POST['new_deadline'];
    $new_day = date('l', strtotime($new_deadline));

    $stmt = $conn->prepare("UPDATE teacher_assignments SET deadline_date = ?, deadline_day = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_deadline, $new_day, $assignment_id);
    $stmt->execute();

    $success = "✅ Deadline updated successfully!";
}

// Fetch assignments uploaded by current teacher
$teacher = $_SESSION['username'];
$result = $conn->query("SELECT * FROM teacher_assignments WHERE teacher_username = '$teacher' ORDER BY uploaded_at DESC");

if (!$result) {
    die("Query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Assignment Deadlines</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        input, button, select { padding: 10px; margin-top: 8px; width: 100%; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        h2 { color: #333; }
        .msg { font-weight: bold; color: green; }
    </style>
</head>
<body>

<h2>🕒 Update Assignment Deadlines</h2>

<?php if (isset($success)): ?>
    <p class="msg"><?= $success ?></p>
<?php endif; ?>

<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="box">
            <form method="post">
                <strong>📌 Title:</strong> <?= htmlspecialchars($row['title']) ?><br>
                <strong>📁 Uploaded At:</strong> <?= $row['uploaded_at'] ?><br>
                <strong>👤 Type:</strong> <?= ucfirst($row['assignment_type']) ?><br>
                <strong>📅 Year:</strong> <?= $row['year'] ?> |
                <strong>📚 Semester:</strong> <?= $row['semester'] ?><br>
                <strong>⏰ Current Deadline:</strong> <?= $row['deadline_date'] ?> (<?= $row['deadline_day'] ?>)<br><br>

                <label>New Deadline</label>
                <input type="datetime-local" name="new_deadline" value="<?= date('Y-m-d\TH:i', strtotime($row['deadline_date'])) ?>" required>

                <input type="hidden" name="assignment_id" value="<?= $row['id'] ?>">
                <button type="submit" name="update">Update Deadline</button>
            </form>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="color: red;">⚠️ No assignments found for your account.</p>
<?php endif; ?>

</body>
</html>
