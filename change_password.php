<?php
session_start();
include 'db_connect.php'; // Database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_id = $_SESSION['user_id'];

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match. Please try again.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $new_password)) {
        // Validate password strength
        $error_message = "Password must be at least 8 characters long, include an uppercase letter, and a number.";
    } else {
        // Hash the password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password and set password_changed to TRUE
        $stmt = $pdo->prepare("UPDATE personnel SET password = ?, password_changed = 1 WHERE PersonnelID = ?");
        if ($stmt->execute([$hashed_password,$user_id])) {
            // Redirect to dashboard after success
            header("Location: dashboard.php");
            exit;
        } else {
            $error_message = "An error occurred. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
<main>
    <section class="login-container">
        <h1 class="title">National Service Personnel</h1>
        <?php if (isset($error_message)) { echo "<p class='error'>" . htmlspecialchars($error_message) . "</p>"; } ?>

        <form action="change_password.php" method="POST" id="passwordForm">
            <legend>Update Your Password</legend>
            <div class="fieldset">

                <div class="input-field">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="input-field">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>

            <p id="errorMessage" style="color: red;"></p>
            <button type="submit">Change Password</button>
        </form>
    </section>
</main>

<script>
    document.getElementById('passwordForm').addEventListener('submit', function (e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const errorMessage = document.getElementById('errorMessage');

        // Check if passwords match
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            errorMessage.textContent = "Passwords do not match.";
            return;
        }

        // Check password strength
        const isValid = /^(?=.*[A-Z])(?=.*\d).{8,}$/.test(newPassword);
        if (!isValid) {
            e.preventDefault();
            errorMessage.textContent = "Password must be at least 8 characters long, include an uppercase letter and a number.";
        } else {
            errorMessage.textContent = "";
        }
    });
</script>
</body>
</html>
