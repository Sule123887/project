<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $subjects = $_POST['subjects'] ?? [];

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($existingUser = $result->fetch_assoc()) {
        $student_id = $existingUser['id'];

        // Check if this enrollment already exists
        $checkEnroll = $conn->prepare("SELECT * FROM student_enrollments WHERE student_id = ? AND year = ? AND semester = ?");
        $checkEnroll->bind_param("iss", $student_id, $year, $semester);
        $checkEnroll->execute();
        $enrollResult = $checkEnroll->get_result();

        if ($enrollResult->num_rows == 0) {
            $enrollInsert = $conn->prepare("INSERT INTO student_enrollments (student_id, year, semester) VALUES (?, ?, ?)");
            $enrollInsert->bind_param("iss", $student_id, $year, $semester);
            $enrollInsert->execute();

            $subjectStmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject, year, semester) VALUES (?, ?, ?, ?)");
            foreach ($subjects as $subject) {
                $subjectStmt->bind_param("isss", $student_id, $subject, $year, $semester);
                $subjectStmt->execute();
            }
            
            echo json_encode(["status" => "success", "message" => "Registration successful"]);
        } else {
            echo json_encode(["status" => "warning", "message" => "Already enrolled"]);
        }
    } else {
        // Create new user
        $stmt = $conn->prepare("INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, 'student')");
        $stmt->bind_param("sss", $fullname, $username, $password);

        if ($stmt->execute()) {
            $student_id = $stmt->insert_id;

            $enrollInsert = $conn->prepare("INSERT INTO student_enrollments (student_id, year, semester) VALUES (?, ?, ?)");
            $enrollInsert->bind_param("iss", $student_id, $year, $semester);
            $enrollInsert->execute();

            $subjectStmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject) VALUES (?, ?)");
            foreach ($subjects as $subject) {
                $subjectStmt->bind_param("is", $student_id, $subject);
                $subjectStmt->execute();
            }

            echo json_encode(["status" => "success", "message" => "Registration successful"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
    }
}
?>
