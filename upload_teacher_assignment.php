<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
date_default_timezone_set('Africa/Nairobi');
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher = $_SESSION['username'];
    $title = $_POST['title'];
    $type = $_POST['assignment_type'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $deadline_datetime = $_POST['deadline_datetime'];
    $deadline_day = date('l', strtotime($deadline_datetime));
    $uploaded_at = date("Y-m-d H:i:s");
    $file = $_FILES['assignment'];

    if ($file['error'] == 0) {
        $filename = time() . "_" . basename($file['name']);
        $targetPath = "uploads/" . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("INSERT INTO teacher_assignments 
                (teacher_username, title, file_path, deadline_date, deadline_day, uploaded_at, assignment_type, year, semester)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $teacher, $title, $targetPath, $deadline_datetime, $deadline_day, $uploaded_at, $type, $year, $semester);
            $stmt->execute();

            $success = "✅ Assignment uploaded successfully!";
        } else {
            $error = "❌ Failed to upload assignment file.";
        }
    } else {
        $error = "❌ File upload error.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Assignment</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 50px;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            margin: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
        }
        .msg {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>📤 Upload Assignment (With Deadline)</h2>

        <?php if (isset($success)): ?>
            <div class="msg" style="color: green;"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="msg" style="color: red;"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Assignment Title</label>
            <input type="text" name="title" required>

            <label>Assignment Type</label>
            <select name="assignment_type" required>
                <option value="">-- Select Type --</option>
                <option value="individual">Individual</option>
                <option value="group">Group</option>
            </select>

            <label>Year</label>
            <select name="year" required>
                <option value="">-- Select Year --</option>
                <option value="Year 1">Year 1</option>
                <option value="Year 2">Year 2</option>
                <option value="Year 3">Year 3</option>
            </select>

            <label>Semester</label>
            <select name="semester" required>
                <option value="">-- Select Semester --</option>
                <option value="Semester 1">Semester 1</option>
                <option value="Semester 2">Semester 2</option>
                <option value="Semester 3">Semester 3</option>
                <option value="Semester 4">Semester 4</option>
                <option value="Semester 5">Semester 5</option>
                <option value="Semester">Semester 6</option>
            </select>

            <label>Select Assignment File</label>
            <input type="file" name="assignment" accept=".pdf,.docx,.txt" required>

            <label>Deadline Date & Time</label>
            <input type="datetime-local" name="deadline_datetime" required>

            <button type="submit">Upload Assignment</button>
        </form>
    </div>
</body>
</html>
