<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch fullname and role from database
$stmt = $conn->prepare("SELECT fullname, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['role'] = $user['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
        }

        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            background-color: #333;
            color: white;
            overflow-x: hidden;
            transition: 0.3s;
            padding-top: 60px;
            z-index: 999;
        }

        .sidebar a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .menu-btn {
            font-size: 30px;
            cursor: pointer;
            color: white;
            background: #007bff;
            padding: 10px 20px;
            border: none;
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1001;
            border-radius: 5px;
        }

        .content {
            margin-left: 0;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .show-sidebar .sidebar {
            left: 0;
        }

        .show-sidebar .content {
            margin-left: 250px;
        }

        .header {
            background-color: #007bff;
            color: white;
            padding: 60px 20px 20px 20px;
            font-size: 22px;
            text-align: center;
            border-radius: 0 0 10px 10px;
        }

        .info-box {
            background-color: #f0f0f0;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="update_deadline.php">📘 Update Assigment</a>
    <a href="register.php">📚 Registration</a>
    <a href="upload_assignment.php">📤 Upload Assignment</a>
    <a href="upload_teacher_assignment.php">📤 Teacher Assignment</a>
    <a href="upload_group_assignment.php">👥 Upload Group Assignment</a>
    <a href="student_assignments.php">📂 Student Assignments</a>
    <a href="view_assignments.php">📂 View Assignments</a>
    <a href="view_assignment_marks.php">✅ View Assignment Marks</a>
    <a href="change_password.php">🔒 Change Password</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- Menu Button -->
<button class="menu-btn" onclick="toggleSidebar()">☰</button>

<!-- Content -->
<div class="content" id="main">
    <div class="header">
        <?= "Welcome, " . htmlspecialchars($_SESSION['fullname']) . " (" . htmlspecialchars($_SESSION['username']) . " - " . htmlspecialchars($_SESSION['role']) . ")" ?>
    </div>

   

    <!-- My Subjects Section -->
    <div class="mt-4">
        <?php include 'my_subjects.php'; ?>
    </div>
</div>

<!-- JS to toggle sidebar -->
<script>
function toggleSidebar() {
    document.body.classList.toggle("show-sidebar");
}
</script>

</body>
</html>
