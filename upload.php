<?php
include 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $type = $_POST['type']; // 'lesson' or 'tutorial'
    $name = $_FILES['file']['name'];
    $tmp = $_FILES['file']['tmp_name'];
    $folder = "uploads/" . basename($name);

    // Move the uploaded file
    if (move_uploaded_file($tmp, $folder)) {
        $sql = "INSERT INTO lessons (filename, type) VALUES ('$name', '$type')";
        if ($conn->query($sql)) {
            $message = "✅ " . ucfirst($type) . " uploaded successfully!";
        } else {
            $message = "❌ Database error: " . $conn->error;
        }
    } else {
        $message = "❌ Failed to upload file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Lesson or Tutorial</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f0f2f5;
        }

        h2 {
            color: #007bff;
        }

        .box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            margin: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        input[type="file"], select, button {
            width: 100%;
            padding: 10px;
            margin-top: 12px;
            margin-bottom: 18px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>📤 Upload Lesson or Tutorial</h2>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="type">Select Type:</label>
        <select name="type" required>
            <option value="lesson">Lesson (PDF)</option>
            <option value="tutorial">Tutorial (Video)</option>
        </select>

        <label for="file">Choose File:</label>
        <input type="file" name="file" required>

        <button type="submit">Upload</button>
    </form>
</div>

</body>
</html>
