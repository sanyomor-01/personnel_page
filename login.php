<?php
session_start();
require 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim(htmlspecialchars($_POST['username'])); 
    $password = trim($_POST['password']); 

    if (empty($username) || empty($password)) {
        $message = "Username and password are required.";
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT * FROM Personnel WHERE Username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['PersonnelID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['role'];

            if ($_SESSION['role'] == 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $message = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- <button onclick="window.history.back()">Go Back</button> -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
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
    echo "<p>" . htmlspecialchars($message) . "</p>"; 
}
?>