<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }

    if (!preg_match("/^\d{10,15}$/", $phone)) {
        $error = "Invalid phone number. Only 10-15 digits are allowed.";
    }

    if (!isset($error)) {
        try {
            $sql = "UPDATE Personnel SET email = :email, Phone = :phone WHERE PersonnelID = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':phone' => $phone,
                ':user_id' => $user_id
            ]);

            $message = "Contact information updated successfully!";
            header("Location: profile.php?message=" . urlencode($message));
            exit();
        } catch (PDOException $e) {
            $error = "Error updating contact information: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Contact Information</title>
    <link rel="stylesheet" href="css/update_profile.css">
    <button onclick="window.history.back()" class="back-button">Go Back</button> 
</head>

<body>
    <form method="POST" action="update_contact.php">
        <h3>Contact Information</h3>

        <label for="email">Email: <span class="required-asterisk">*</span></label>
        <input type="email" name="email" id="email" placeholder="Enter your email" value="<?= htmlspecialchars($email ?? '') ?>" required><br>

        <label for="phone">Phone Number: <span class="required-asterisk">*</span></label>
        <input type="text" name="phone" id="phone" placeholder="Enter your phone number" value="<?= htmlspecialchars($phone ?? '') ?>" required><br>

        <button type="submit">Save Contact Information</button>
    </form>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</body>
</html>