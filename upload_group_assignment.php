<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// Function to extract registration numbers like 24/PM/2024
function extractRegNumbers($filePath) {
    $text = '';
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

    if ($extension == 'txt') {
        $text = file_get_contents($filePath);
    } elseif ($extension == 'docx') {
        $zip = new ZipArchive;
        if ($zip->open($filePath) === TRUE) {
            $data = $zip->getFromName('word/document.xml');
            $text = strip_tags($data);
            $zip->close();
        }
    } elseif ($extension == 'pdf') {
        return []; // You can add a PDF parser later
    }

    // Match pattern like 24/PM/2024
    preg_match_all('/\d{2}\/[A-Z]{2}\/\d{3}/', $text, $matches);
    return array_unique($matches[0]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uploader = $_SESSION['username'];
    $title = $_POST['title'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $file = $_FILES['assignment'];

    if ($file['error'] == 0) {
        $filename = time() . "_" . basename($file['name']);
        $targetPath = "uploads/" . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $regNumbers = extractRegNumbers($targetPath);

            if (empty($regNumbers)) {
                $error = "❌ No registration numbers found in the document.";
            } else {
                $type = 'group';
                $groupMembers = implode(", ", $regNumbers);
                $alreadySubmitted = [];

                foreach ($regNumbers as $reg) {
                    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM assignments WHERE student_username = ?");
                    $checkStmt->bind_param("s", $reg);
                    $checkStmt->execute();
                    $checkStmt->store_result();
                    $checkStmt->bind_result($count);
                    $checkStmt->fetch();

                    if ($count > 0) {
                        $alreadySubmitted[] = $reg;
                    }
                }

                if (!empty($alreadySubmitted)) {
                    $error = "⚠️ Assignment already submitted by another member in your group: ";
                    unlink($targetPath);
                } else {
                    foreach ($regNumbers as $reg) {
                        $stmt = $conn->prepare("INSERT INTO assignments (student_username, assignment_title, file_path, type, year, semester, group_members) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssssss", $reg, $title, $targetPath, $type, $year, $semester, $groupMembers);
                        $stmt->execute();
                    }
                    $success = "✅ Group assignment uploaded successfully for: " . $groupMembers;
                }
            }
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
    <title>Upload Group Assignment</title>
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
        input, select, button {
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
        <h2>👥 Upload Group Assignment</h2>

        <?php if (isset($success)): ?>
            <div class="msg" style="color: green;"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="msg" style="color: red;"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Assignment Title</label>
            <input type="text" name="title" required>

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
                <option value="Semester 6">Semester 6</option>
            </select>

            <label>Select File (.txt or .docx)</label>
            <input type="file" name="assignment" accept=".txt,.docx,.pdf" required>

            <button type="submit">Upload</button>
        </form>
    </div>
</body>
</html>
