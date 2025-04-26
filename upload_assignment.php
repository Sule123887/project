<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student = $_SESSION['username'];
    $title = $_POST['title'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $file = $_FILES['assignment'];
    $type = 'individual';

    if ($file['error'] == 0) {
        $filename = time() . "_" . basename($file['name']);
        $targetPath = "uploads/" . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("INSERT INTO assignments (student_username, assignment_title, file_path, type, year, semester) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $student, $title, $targetPath, $type, $year, $semester);
            $stmt->execute();
            $success = "✅ Assignment uploaded successfully!";
        } else {
            $error = "❌ Failed to upload file.";
        }
    } else {
        $error = "❌ Error uploading file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Assignment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #007bff;
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
        <h2>📤 Upload Individual Assignment</h2>

        <?php if (isset($success)): ?>
            <div class="msg" style="color: green;"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="msg" style="color: red;"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Assignment Title</label>
            <input type="text" name="title" required>

            <label>Year of Student</label>
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
                <option value="Semester 6">Semester 6</option>
            </select>

            <label>Select File</label>
            <input type="file" name="assignment" accept=".pdf,.doc,.docx,.zip,.rar" required>

            <button type="submit">Upload</button>
        </form>
    </div>
</body>
</html>
