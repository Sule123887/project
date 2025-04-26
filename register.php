<?php
include 'db.php';

$years = ['Year 1', 'Year 2', 'Year 3'];
$semesters = ['Semester 1', 'Semester 2'];

$subjectsByYearSemester = [
    'Year 1_Semester 1' => ['Intro to Accounting', 'Business Math', 'ICT Basics'],
    'Year 1_Semester 2' => ['Microeconomics', 'Communication Skills'],
    'Year 1_Semester 3' => ['Intro to Accounting', 'Business Math', 'ICT Basics'],
    'Year 1_Semester 4' => ['Microeconomics', 'Communication Skills'],
    'Year 1_Semester 5' => ['Intro to Accounting', 'Business Math', 'ICT Basics'],
    'Year 1_Semester 6' => ['Microeconomics', 'Communication Skills'],
    'Year 2_Semester 1' => ['Financial Accounting', 'Business Law'],
    'Year 2_Semester 2' => ['Financial Accounting', 'Business Law'],
    'Year 2_Semester 3' => ['Financial Accounting', 'Business Law'],
    'Year 2_Semester 4' => ['Financial Accounting', 'Business Law'],
    'Year 2_Semester 5' => ['Financial Accounting', 'Business Law'],
    'Year 2_Semester 6' => ['Financial Accounting', 'Business Law'],
    'Year 3_Semester 1' => ['Cost Accounting', 'Statistics'],
    'Year 3_Semester 2' => ['Auditing', 'Taxation'],
    'Year 3_Semester 3' => ['Advanced Accounting', 'Research Project'],
    'Year 3_Semester 4' => ['Cost Accounting', 'Statistics'],
    'Year 3_Semester 5' => ['Auditing', 'Taxation'],
    'Year 3_Semester 6' => ['Advanced Accounting', 'Research Project']
];

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
            

            $message = "<div class='alert alert-success'>🔁 Enrollment added for new Year & Semester.</div>";
        } else {
            $message = "<div class='alert alert-warning'>⚠️ Already enrolled for this Year & Semester.</div>";
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

            $message = "<div class='alert alert-success'>✅ Registered successfully. <a href='login.php'>Login here</a></div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Error: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .register-container {
            max-width: 600px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .subject-box { margin-top: 15px; }
    </style>
</head>
<body>

<div class="register-container">
    <h3 class="mb-4 text-center"><i class="bi bi-person-plus"></i> Student Registration</h3>

    <?php if (!empty($message)) echo $message; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input name="fullname" type="text" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" type="text" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Year</label>
            <select name="year" class="form-select" id="yearSelect" required>
                <option value="">-- Choose Year --</option>
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>"><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Semester</label>
            <select name="semester" class="form-select" id="semesterSelect" required>
                <option value="">-- Choose Semester --</option>
                <?php foreach ($semesters as $s): ?>
                    <option value="<?= $s ?>"><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="subject-box" id="subjectBox">
            <label class="form-label">Subjects</label>
            <div id="subjectsContainer"></div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3"><i class="bi bi-check-circle"></i> Register</button>
        <p class="mt-3 text-center">Already registered? <a href="login.php">Login here</a></p>
    </form>
</div>

<script>
    const subjectsMap = <?= json_encode($subjectsByYearSemester) ?>;

    document.getElementById('yearSelect').addEventListener('change', updateSubjects);
    document.getElementById('semesterSelect').addEventListener('change', updateSubjects);

    function updateSubjects() {
        const year = document.getElementById('yearSelect').value;
        const semester = document.getElementById('semesterSelect').value;
        const key = year + '_' + semester;
        const subjectsContainer = document.getElementById('subjectsContainer');
        subjectsContainer.innerHTML = '';

        if (subjectsMap[key]) {
            subjectsMap[key].forEach(subject => {
                const checkbox = document.createElement('div');
                checkbox.className = 'form-check';
                checkbox.innerHTML = `
                    <input class="form-check-input" type="checkbox" name="subjects[]" value="${subject}" id="${subject}">
                    <label class="form-check-label" for="${subject}">${subject}</label>
                `;
                subjectsContainer.appendChild(checkbox);
            });
        }
    }
</script>

</body>
</html>
