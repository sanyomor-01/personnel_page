<?php
session_start();

// Database connection
require 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate if the user has permission to edit
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];  // Assuming role is set in session on login (e.g., 'admin' or 'user')

// Ensure user can only edit their own profile or that an admin is accessing the profile
$personnelID = $_GET['id'] ?? $user_id;  // Fetch the ID from URL if admin or use logged-in user ID
if ($role !== 'admin' && $user_id != $personnelID) {
    echo "You do not have permission to edit this profile.";
    exit();
}

// Fetch user data for editing
$stmt = $pdo->prepare("SELECT * FROM Personnel WHERE PersonnelID = :personnelID");
$stmt->execute([':personnelID' => $personnelID]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $lastName = htmlspecialchars(trim($_POST['last_name']));
   $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email']));
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Update the profile information securely using prepared statements
    $stmt = $pdo->prepare("UPDATE Personnel SET FirstName = :firstName, LastName = :lastName, Phone = :phone, Email = :email, StartDate = :startDate, EndDate = :endDate WHERE PersonnelID = :personnelID");
    $stmt->execute([
        ':firstName' => $firstName,
        ':lastName' => $lastName,
        ':phone' => $phone,
        ':email' => $email,
        ':startDate' => $startDate,
        ':endDate' => $endDate,
        ':personnelID' => $personnelID
    ]);

    echo "<p class='success'>Profile updated successfully!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Edit Profile</h2>
    <form method="POST" action="edit_profile.php?id=<?= htmlspecialchars($personnelID) ?>">
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($userData['FirstName'] ?? '') ?>" required><br>

        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($userData['LastName'] ?? '') ?>" required><br>

        <label>Phone:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($userData['Phone'] ?? '') ?>" required><br>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($userData['Email'] ?? '') ?>" required><br>

        <label>Start Date:</label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($userData['StartDate'] ?? '') ?>"><br>

        <label>End Date:</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($userData['EndDate'] ?? '') ?>"><br>

        <button type="submit">Update Profile</button>
    </form>
</div>
</body>
</html>