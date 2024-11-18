<?php
session_start();
require 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT PersonnelID FROM Personnel WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate a unique token for the password reset link
        $token = bin2hex(random_bytes(50));
        $expiry = time() + 3600; // Token expires in 1 hour

        // Store the token and expiry in the database
        $stmt = $pdo->prepare("UPDATE Personnel SET reset_token = :token, token_expiry = :expiry WHERE email = :email");
        $stmt->execute([
            ':token' => $token,
            ':expiry' => $expiry,
            ':email' => $email
        ]);

        $resetLink = "http://yourdomain.com/reset_password.php?token=" . $token;
        $subject = "Password Reset Request";
        $body = "To reset your password, click the following link: $resetLink";

        if (mail($email, $subject, $body)) {
            $message = "A password reset link has been sent to your email.";
        } else {
            $message = "Error sending email. Please try again later.";
        }
    } else {
        $message = "Email not found in our records.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
    <div class="forgot-password-container">
        <h1>Forgot Password</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <label>Email:</label>
            <input type="email" name="email" required><br>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>

</html>
