<?php
session_start();
require 'db_connect.php';

$message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Retrieve the token and check if it's valid
    $stmt = $pdo->prepare("SELECT PersonnelID, token_expiry FROM Personnel WHERE reset_token = :token");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (time() > $user['token_expiry']) {
            $message = "The reset link has expired.";
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];

                if ($newPassword !== $confirmPassword) {
                    $message = "Passwords do not match.";
                } elseif (strlen($newPassword) < 8) {
                    $message = "Password must be at least 8 characters long.";
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                    $stmt = $pdo->prepare("UPDATE Personnel SET Password = :password, reset_token = NULL, token_expiry = NULL WHERE PersonnelID = :user_id");
                    $stmt->execute([
                        ':password' => $hashedPassword,
                        ':user_id' => $user['PersonnelID']
                    ]);

                    $message = "Your password has been successfully updated. You can now log in.";
                    header('Location: login.php');
                    exit();
                }
            }
        }
    } else {
        $message = "Invalid reset token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Your Password</h2>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
        <label>New Password:</label>
        <input type="password" name="new_password" required><br>

        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" required><br>

        <button type="submit">Reset Password</button>
    </form>
</body>
</html>