<?php
require 'db_connect.php';

$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $email = $_POST['email'];

    // Check if the username already exists in accounts
    $stmt = $pdo->prepare("SELECT * FROM personnel WHERE username = :username");
    $stmt->execute([':username' => $username]);

    if ($stmt->rowCount() > 0) {
        $successMessage = "Username already taken. Please choose another one.";
    } else {
        try {
          

            // Get the newly generated PersonnelID
            $personnelID = $pdo->lastInsertId();

            // Step 2: Insert into accounts table using the generated PersonnelID
            $stmt = $pdo->prepare("INSERT INTO personnel (username, password, email, personnelID) VALUES (:username, :password, :email, :personnelID)");
            $stmt->execute([
                ':username' => $username,
                ':password' => $password,
                ':email' => $email,
                ':personnelID' => $personnelID
            ]);

            $successMessage = "Registration successful. You can now <a href='login.php'>log in</a>.";
        } catch (PDOException $e) {
            $successMessage = "Error during registration: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if ($successMessage): ?>
            <p class="success-message"><?= $successMessage ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>