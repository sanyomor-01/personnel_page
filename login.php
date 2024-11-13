<?php
session_start();
require 'db_connect.php'; // Include database connection

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $username = trim(htmlspecialchars($_POST['username'])); // Sanitize username
    $password = trim($_POST['password']); // Password sanitization is not needed, but trimming spaces

    // Validate inputs to make sure they're not empty
    if (empty($username) || empty($password)) {
        $message = "Username and password are required.";
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT * FROM Personnel WHERE Username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            // Store user info in session if credentials are correct
            $_SESSION['user_id'] = $user['PersonnelID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($_SESSION['role'] == 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: dashboard.php");
                exit();
            }
        } else {
            // Invalid credentials
            $message = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <!-- This will display any login error messages if you set them in your PHP code -->
    <?php if (isset($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>

    <form action="login.php" method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>

<?php
if (isset($message)) {
    echo "<p>" . htmlspecialchars($message) . "</p>"; // Output the sanitized message
}
?>