<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $link = $_POST['link'];
    $today = date("Y-m-d");

    // Count existing quizzes with same title
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM quizzes WHERE title = ? AND date = ?");
    $stmt->bind_param("ss", $title, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $count = $data['total'] + 1;

    // Insert new quiz (title stays the same, but count used for display only)
    $stmt = $conn->prepare("INSERT INTO quizzes (title, link, date) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $link, $today);
    $stmt->execute();

    echo "<p style='color:green'>✅ Quiz $count for <strong>$title</strong> added on $today.</p>";
}
?>

<h2>Add Quiz (Google Form Link)</h2>
<form method="post">
    Quiz Title: <input type="text" name="title" required><br><br>
    Google Form Link: <input type="url" name="link" required><br><br>
    <input type="submit" value="Submit">
</form>
