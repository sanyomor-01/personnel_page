<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $errors = [];

    if (empty($new_password) || empty($confirm_password)) {
        $errors[] = "Both fields are required.";
    }

    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($new_password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $new_password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/\d/', $new_password)) {
        $errors[] = "Password must contain at least one number.";
    }
    if (!preg_match('/[^\w]/', $new_password)) {
        $errors[] = "Password must contain at least one special character (e.g., @, #, $, etc.).";
    }

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            $sql = "UPDATE Personnel SET Password = :password, password_changed = 1 WHERE PersonnelID = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':password' => $hashed_password, ':user_id' => $user_id]);

            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error updating password: " . $e->getMessage();
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
    <link rel="stylesheet" href="css/forgot_password.css">
</head>
<body>

<div class="container">
    <h1>Change Your Password</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p class="error"><?= htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="change_password.php" method="POST">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Update Password</button>
    </form>

   
</div>

</body>
</html>
