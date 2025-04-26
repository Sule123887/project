<?php
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lessons & Tutorials</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e0eafc, #cfdef3);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: #0a58ca;
            margin-top: 30px;
            font-size: 20px;
            border-left: 4px solid #0a58ca;
            padding-left: 10px;
        }

        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .file-card {
            background: #f5f9ff;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .file-card:hover {
            background: #e4efff;
            transform: translateY(-3px);
        }

        .file-card i {
            font-size: 26px;
            margin-bottom: 8px;
            display: block;
            color: #0a58ca;
        }

        .file-card a {
            font-size: 14px;
            color: #0a58ca;
            text-decoration: none;
            font-weight: 500;
            word-break: break-word;
            display: block;
            margin-top: 5px;
        }

        .video-preview {
            width: 100%;
            height: 120px;
            border-radius: 6px;
            margin-top: 8px;
            background: #000;
        }

        @media (max-width: 600px) {
            .file-card {
                padding: 10px;
            }

            .file-card i {
                font-size: 20px;
            }

            .file-card a {
                font-size: 13px;
            }

            .video-preview {
                height: 100px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📘 Available Lessons (PDF)</h2>
    <div class="file-list">
        <?php
        $lessons = $conn->query("SELECT * FROM lessons WHERE type='lesson'");
        if ($lessons->num_rows > 0) {
            while ($row = $lessons->fetch_assoc()) {
                echo "<div class='file-card'>
                        <i>📄</i>
                        <a href='uploads/{$row['filename']}' target='_blank'>{$row['filename']}</a>
                      </div>";
            }
        } else {
            echo "<p>No lessons uploaded yet.</p>";
        }
        ?>
    </div>

    <h2>🎥 Available Tutorials (Video)</h2>
    <div class="file-list">
        <?php
        $tutorials = $conn->query("SELECT * FROM lessons WHERE type='tutorial'");
        if ($tutorials->num_rows > 0) {
            while ($row = $tutorials->fetch_assoc()) {
                echo "<div class='file-card'>
                        <i>🎬</i>
                        <video class='video-preview' controls>
                            <source src='uploads/{$row['filename']}' type='video/mp4'>
                            Your browser does not support the video tag.
                        </video>
                        <a href='uploads/{$row['filename']}' target='_blank'>{$row['filename']}</a>
                      </div>";
            }
        } else {
            echo "<p>No tutorials uploaded yet.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>
