<?php
include 'db.php';
//session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Get student ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$student_id = $student['id'];

$subjects_by_term = [];

// Get all year+semester combinations for this student
$stmt = $conn->prepare("SELECT DISTINCT year, semester FROM student_subjects WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$terms = $stmt->get_result();

while ($term = $terms->fetch_assoc()) {
    $year = $term['year'];
    $semester = $term['semester'];

    $stmt2 = $conn->prepare("SELECT subject FROM student_subjects WHERE student_id = ? AND year = ? AND semester = ?");
    $stmt2->bind_param("iss", $student_id, $year, $semester);
    $stmt2->execute();
    $subRes = $stmt2->get_result();

    $subjects = [];
    while ($row = $subRes->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }

    $subjects_by_term["$year - $semester"] = $subjects;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="mb-4"><i class="bi bi-book"></i> My Registered Subjects</h3>

    <?php if (!empty($subjects_by_term)): ?>
        <?php foreach ($subjects_by_term as $term => $subjects): ?>
            <div class="mb-4">
                <h5 class="mb-3 text-primary">📅 <?= htmlspecialchars($term) ?></h5>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($subjects as $subject): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">📘 <?= htmlspecialchars($subject) ?></h5>
                                    <p class="card-text">Access resources for this subject:</p>
                                    <div class="d-flex gap-2">
                                        <a href="view_lessons.php?subject=<?= urlencode($subject) ?>" class="btn btn-info btn-sm">
                                            <i class="bi bi-journal-text"></i> Notes
                                        </a>
                                        <a href="student_assignments.php?subject=<?= urlencode($subject) ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i> Assignment
                                        </a>
                                        <a href="view_quiz.php?subject=<?= urlencode($subject) ?>" class="btn btn-success btn-sm">
                                            <i class="bi bi-question-circle"></i> Quiz
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning mt-4">⚠️ You have not registered for any subjects yet.</div>
    <?php endif; ?>
</div>
</body>
</html>
