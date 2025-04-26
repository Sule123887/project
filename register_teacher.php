<?php
include 'db.php';

$years = ['Year 1', 'Year 2', 'Year 3'];
$semesters = ['Semester 1', 'Semester 2'];

$subjectsByYearSemester = [
    'Year 1_Semester 1' => ['Intro to Accounting', 'Business Math', 'ICT Basics'],
    'Year 1_Semester 2' => ['Microeconomics', 'Communication Skills'],
    'Year 2_Semester 1' => ['Financial Accounting', 'Business Law'],
    'Year 2_Semester 2' => ['Cost Accounting', 'Statistics'],
    'Year 3_Semester 1' => ['Auditing', 'Taxation'],
    'Year 3_Semester 2' => ['Advanced Accounting', 'Research Project']
];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $subjects = $_POST['subjects'] ?? [];

    $stmt = $conn->prepare("SELECT id FROM teachers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($teacher_id);
        $stmt->fetch();

        // Check if subjects for the same year & semester already exist
        $checkSubjects = $conn->prepare("SELECT subject FROM teacher_subjects WHERE teacher_id = ? AND year = ? AND semester = ?");
        $checkSubjects->bind_param("iss", $teacher_id, $year, $semester);
        $checkSubjects->execute();
        $existingSubjectsResult = $checkSubjects->get_result();
        $existingSubjects = [];
        while ($row = $existingSubjectsResult->fetch_assoc()) {
            $existingSubjects[] = $row['subject'];
        }

      // Delete old subjects for this year and semester
$deleteOld = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ? AND year = ? AND semester = ?");
$deleteOld->bind_param("iss", $teacher_id, $year, $semester);
$deleteOld->execute();

// Insert updated subjects
$insertSub = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject, year, semester) VALUES (?, ?, ?, ?)");
foreach ($subjects as $subject) {
    $insertSub->bind_param("isss", $teacher_id, $subject, $year, $semester);
    $insertSub->execute();
}

        $message = "<div class='alert alert-info'>📚 Subjects updated for existing teacher.</div>";
    } else {
        // Insert new teacher
        $stmt = $conn->prepare("INSERT INTO teachers (fullname, username, password, year, semester) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $username, $password, $year, $semester);
        if ($stmt->execute()) {
            $teacher_id = $stmt->insert_id;

            $insertSub = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject, year, semester) VALUES (?, ?, ?, ?)");
            foreach ($subjects as $subject) {
                $insertSub->bind_param("isss", $teacher_id, $subject, $year, $semester);
                $insertSub->execute();
            }

            $message = "<div class='alert alert-success'>✅ Teacher registered successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Error: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #eef1f5; }
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
    <h3 class="mb-4 text-center"><i class="bi bi-person-plus-fill"></i> Teacher Registration</h3>

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

        <button type="submit" class="btn btn-success w-100 mt-3"><i class="bi bi-check-circle"></i> Register</button>
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
    const username = document.querySelector('input[name="username"]').value;
    const key = year + '_' + semester;
    const subjectsContainer = document.getElementById('subjectsContainer');
    subjectsContainer.innerHTML = '';

    if (subjectsMap[key]) {
        // Fetch subjects already selected by this teacher
        if (username && year && semester) {
            fetch(`get_teacher_subjects.php?username=${username}&year=${encodeURIComponent(year)}&semester=${encodeURIComponent(semester)}`)
                .then(response => response.json())
                .then(selectedSubjects => {
                    subjectsMap[key].forEach(subject => {
                        const isChecked = selectedSubjects.includes(subject) ? 'checked' : '';
                        const checkbox = document.createElement('div');
                        checkbox.className = 'form-check';
                        checkbox.innerHTML = `
                            <input class="form-check-input" type="checkbox" name="subjects[]" value="${subject}" id="${subject}" ${isChecked}>
                            <label class="form-check-label" for="${subject}">${subject}</label>
                        `;
                        subjectsContainer.appendChild(checkbox);
                    });
                });
        }
    }
}

</script>

</body>
</html>
