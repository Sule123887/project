<?php

include 'db.php';

// Hakikisha teacher ame-login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Pata teacher ID
$stmt = $conn->prepare("SELECT id FROM teachers WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$teacher_id = $teacher['id'];

// Pata masomo kulingana na year na semester
$subjects_by_term = [];

$stmt = $conn->prepare("SELECT DISTINCT year, semester FROM teacher_subjects WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$terms = $stmt->get_result();

while ($term = $terms->fetch_assoc()) {
    $year = $term['year'];
    $semester = $term['semester'];

    $stmt2 = $conn->prepare("SELECT subject FROM teacher_subjects WHERE teacher_id = ? AND year = ? AND semester = ?");
    $stmt2->bind_param("iss", $teacher_id, $year, $semester);
    $stmt2->execute();
    $subjects = $stmt2->get_result();

    while ($subject = $subjects->fetch_assoc()) {
        $subjects_by_term["$year - $semester"][] = $subject['subject'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .container { margin-top: 60px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h3 class="mb-4 text-center">📚 My Subjects</h3>

    <?php if (empty($subjects_by_term)): ?>
        <div class="alert alert-info text-center">No subjects assigned yet.</div>
    <?php else: ?>
        <?php foreach ($subjects_by_term as $term => $subjects): ?>
    <h5 class="mt-4"><?= htmlspecialchars($term) ?></h5>
    <div class="row">
        <?php foreach ($subjects as $index => $subject): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($subject) ?></h5>
                        <p class="card-text">Term: <?= htmlspecialchars($term) ?></p>
                        <a href="upload.php?subject=<?= urlencode($subject) ?>&term=<?= urlencode($term) ?>" class="btn btn-primary btn-sm">📄 Notes</a>
                        <a href="upload_group_assignment.php?subject=<?= urlencode($subject) ?>&term=<?= urlencode($term) ?>" class="btn btn-warning btn-sm">📝 Assignment</a>
                        <a href="quiz.php?subject=<?= urlencode($subject) ?>&term=<?= urlencode($term) ?>" class="btn btn-success btn-sm">🧠 Quiz</a>
                    </div>
                </div>
            </div>
            <?php if (($index + 1) % 3 === 0): ?>
                </div><div class="row"> <!-- End current row and start new after 3 cards -->
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

    <?php endif; ?>
</div>

</body>
</html>
