<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
include 'db.php';

$current_user = $_SESSION['username'];

// Save marks if submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['marks'])) {
    $assignment_id = $_POST['assignment_id'];
    $marks = $_POST['marks'];

    $stmt = $conn->prepare("SELECT type, student_username, group_members, assignment_title FROM assignments WHERE id = ?");
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $assignment = $stmt->get_result()->fetch_assoc();

    if ($assignment['type'] === 'group') {
        $group_members = explode(",", $assignment['group_members']);
        foreach ($group_members as $member) {
            $member = trim($member);
            $update_stmt = $conn->prepare("UPDATE assignments SET marks = ? WHERE student_username = ? AND assignment_title = ?");
            $update_stmt->bind_param("iss", $marks, $member, $assignment['assignment_title']);
            $update_stmt->execute();
        }
    } else {
        $stmt = $conn->prepare("UPDATE assignments SET marks = ? WHERE id = ?");
        $stmt->bind_param("ii", $marks, $assignment_id);
        $stmt->execute();
    }
}

// Filters
$type = $_GET['type'] ?? 'individual';
$year = $_GET['year'] ?? '';
$semester = $_GET['semester'] ?? '';

$query = "SELECT * FROM assignments WHERE type = ?";
$params = [$type];
$types = "s";

if (!empty($year)) {
    $query .= " AND year = ?";
    $params[] = $year;
    $types .= "s";
}

if (!empty($semester)) {
    $query .= " AND semester = ?";
    $params[] = $semester;
    $types .= "s";
}

$query .= " ORDER BY uploaded_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Keep track of shown group submissions to avoid duplicates
$shownGroups = [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Assignment Submissions</title>
    <style>
        body {
            font-family: Arial;
            padding: 40px;
            background-color: #f0f2f5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        input[type="number"] {
            width: 70px;
        }
        form {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        button {
            padding: 5px 10px;
        }
        .filter {
            margin-bottom: 20px;
            text-align: center;
        }
        select {
            padding: 8px;
            margin: 0 5px;
        }
    </style>
</head>
<body>

<h2>📄 Student Assignment Submissions</h2>

<div class="filter">
    <form method="get">
        <label>Type:</label>
        <select name="type">
            <option value="individual" <?= $type == 'individual' ? 'selected' : '' ?>>Individual</option>
            <option value="group" <?= $type == 'group' ? 'selected' : '' ?>>Group</option>
        </select>

        <label>Year:</label>
        <select name="year">
            <option value="">All</option>
            <option value="Year 1" <?= $year == 'Year 1' ? 'selected' : '' ?>>Year 1</option>
            <option value="Year 2" <?= $year == 'Year 2' ? 'selected' : '' ?>>Year 2</option>
            <option value="Year 3" <?= $year == 'Year 3' ? 'selected' : '' ?>>Year 3</option>
        </select>

        <label>Semester:</label>
        <select name="semester">
            <option value="">All</option>
            <option value="Semester 1" <?= $semester == 'Semester 1' ? 'selected' : '' ?>>Semester 1</option>
            <option value="Semester 2" <?= $semester == 'Semester 2' ? 'selected' : '' ?>>Semester 2</option>
            <option value="Semester 3" <?= $semester == 'Semester 3' ? 'selected' : '' ?>>Semester 3</option>
            <option value="Semester 4" <?= $semester == 'Semester 4' ? 'selected' : '' ?>>Semester 4</option>
            <option value="Semester 5" <?= $semester == 'Semester 5' ? 'selected' : '' ?>>Semester 5</option>
            <option value="Semester 6" <?= $semester == 'Semester 6' ? 'selected' : '' ?>>Semester 6</option>
        </select>

        <button type="submit">🔍 Search</button>
    </form>
</div>

<table>
    <tr>
        <th>Student RegNo</th>
        <th>Assignment Title</th>
        <th>Year</th>
        <th>Semester</th>
        <th>File</th>
        <th>Submitted At</th>
        <th>Marks</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
        $showRow = true;

        if ($row['type'] === 'group') {
            $key = $row['assignment_title'] . '_' . $row['student_username'];
            if (in_array($key, $shownGroups)) {
                $showRow = false;
            } else {
                $shownGroups[] = $key;
            }
        }

        if ($showRow):
        ?>
        <tr>
            <td><?= htmlspecialchars($row['student_username']) ?></td>
            <td><?= htmlspecialchars($row['assignment_title']) ?></td>
            <td><?= htmlspecialchars($row['year']) ?></td>
            <td><?= htmlspecialchars($row['semester']) ?></td>
            <td><a href="<?= $row['file_path'] ?>" target="_blank">📥 Download</a></td>
            <td><?= date('F j, Y H:i', strtotime($row['uploaded_at'])) ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="assignment_id" value="<?= $row['id'] ?>">
                    <input type="number" name="marks" value="<?= htmlspecialchars($row['marks']) ?>" min="0" max="100" required>
                    <button type="submit">Save</button>
                </form>
            </td>
        </tr>
        <?php endif; ?>
    <?php endwhile; ?>
</table>

</body>
</html>
