<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Hakikisha password mpya zinafanana
    if ($new_password !== $confirm_password) {
        $msg = "❌ New password and confirm password do not match!";
    } else {
        // Angalia kama old password iko sahihi
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($current_password);
        $stmt->fetch();
        $stmt->close();

        if ($old_password === $current_password) {
            // Update password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_password, $username);
            if ($stmt->execute()) {
                $msg = "✅ Password changed successfully!";
            } else {
                $msg = "❌ Error updating password.";
            }
        } else {
            $msg = "❌ Incorrect old password!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 30px; text-align: center; }
        form { display: inline-block; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { padding: 10px; margin: 10px 0; width: 250px; }
        input[type="submit"] { width: 270px; background: #007bff; color: white; border: none; cursor: pointer; }
        .msg { margin-top: 15px; font-weight: bold; color: #cc0000; }
    </style>
</head>
<body>

<h2>Change Password 🔒</h2>

<form method="post">
    <input type="password" name="old_password" placeholder="Old Password" required><br>
    <input type="password" name="new_password" placeholder="New Password" required><br>
    <input type="password" name="confirm_password" placeholder="Confirm New Password" required><br>
    <input type="submit" value="Change Password">
</form>

<div class="msg"><?php echo $msg; ?></div>

</body>
</html>
