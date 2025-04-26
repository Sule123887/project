<?php
include 'db.php';

$username = $_GET['username'];
$year = $_GET['year'];
$semester = $_GET['semester'];

$stmt = $conn->prepare("SELECT id FROM teachers WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($teacher_id);
$stmt->fetch();
$stmt->close();

$subjects = [];

if ($teacher_id) {
    $query = $conn->prepare("SELECT subject FROM teacher_subjects WHERE teacher_id = ? AND year = ? AND semester = ?");
    $query->bind_param("iss", $teacher_id, $year, $semester);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }
}

echo json_encode($subjects);
