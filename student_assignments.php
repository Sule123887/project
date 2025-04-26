<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Africa/Nairobi');
include 'db.php';

// Get all assignments
$result = $conn->query("SELECT * FROM teacher_assignments ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Assignments</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f9fafc;
            margin: 0;
            padding: 40px;
        }
        h2 {
            color: #333;
            margin-bottom: 30px;
        }
        .assignment {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            transition: transform 0.2s ease;
        }
        .assignment:hover {
            transform: translateY(-3px);
        }
        .assignment h3 {
            margin: 0 0 10px;
            color: #222;
            font-weight: 600;
        }
        .assignment p {
            margin: 5px 0;
            color: #555;
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            margin-top: 15px;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        .btn-view {
            background-color: #007bff;
            color: white;
        }
        .btn-view:hover {
            background-color: #0056b3;
        }
        .btn-upload {
            background-color: #28a745;
            color: white;
        }
        .btn-upload:hover {
            background-color: #1e7e34;
        }
        .btn-disabled {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
        }
        .date-tag {
            display: inline-block;
            background: #eef2f6;
            padding: 4px 10px;
            font-size: 12px;
            color: #666;
            border-radius: 6px;
            margin-right: 5px;
        }
    </style>
</head>
<body>

<h2>📚 Available Assignments</h2>

<?php while ($row = $result->fetch_assoc()): ?>
    <?php
        $deadline = $row['deadline_date'];
        $now = date("Y-m-d H:i:s");
        $isDeadlinePassed = ($now >= $deadline);
    ?>
    <div class="assignment" data-deadline="<?= $deadline ?>" data-id="<?= $row['id'] ?>">
    <h3><?= htmlspecialchars($row['title']) ?></h3>
<div class="date-tag"><strong>Type:</strong> <?= htmlspecialchars($row['assignment_type']) ?></div>
<div class="date-tag"><strong>Year:</strong> <?= htmlspecialchars($row['year']) ?></div>
<div class="date-tag"><strong>Semester:</strong> <?= htmlspecialchars($row['semester']) ?></div>
<div class="date-tag"><strong>Deadline:</strong> <?= $row['deadline_day'] ?>, <?= date("d M Y H:i", strtotime($row['deadline_date'])) ?></div>
<div class="date-tag"><strong>Uploaded:</strong> <?= date("d M Y H:i", strtotime($row['uploaded_at'])) ?></div>


        <div style="margin-top: 15px;">
            <a href="<?= htmlspecialchars($row['file_path']) ?>" class="btn btn-view" target="_blank">📄 View Assignment</a>

            <?php if (!$isDeadlinePassed): ?>
                <?php
    $uploadLink = ($row['assignment_type'] === 'group') ? 'upload_group_assignment.php' : 'upload_assignment.php';
?>
<a href="<?= $uploadLink ?>?assignment_id=<?= $row['id'] ?>" class="btn btn-upload upload-btn" data-id="<?= $row['id'] ?>">📤 Upload Assignment</a>

            <?php else: ?>
                <button class="btn btn-disabled" disabled>❌ Deadline Passed</button>
            <?php endif; ?>
        </div>
    </div>
<?php endwhile; ?>

<script>
    function disableExpiredButtons() {
        const assignments = document.querySelectorAll('.assignment');
        const now = new Date();

        assignments.forEach(assignment => {
            const deadlineStr = assignment.getAttribute('data-deadline');
            if (!deadlineStr) return;

            const deadline = new Date(deadlineStr.replace(' ', 'T'));
            const uploadBtn = assignment.querySelector('.upload-btn');

            if (uploadBtn && now >= deadline) {
                const disabledBtn = document.createElement('button');
                disabledBtn.className = 'btn btn-disabled';
                disabledBtn.textContent = '❌ Deadline Passed';
                disabledBtn.disabled = true;

                uploadBtn.parentNode.replaceChild(disabledBtn, uploadBtn);
            }
        });
    }

    // Run every 10 seconds
    setInterval(disableExpiredButtons, 10000);
    // Also run immediately
    disableExpiredButtons();
</script>

</body>
</html>
