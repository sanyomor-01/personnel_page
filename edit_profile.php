<?php
session_start();

require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];  

$personnelID = $_GET['id'] ?? $user_id; 
if ($role !== 'admin' && $user_id != $personnelID) {
    echo "You do not have permission to edit this profile.";
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM Personnel WHERE PersonnelID = :personnelID");
$stmt->execute([':personnelID' => $personnelID]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $lastName = htmlspecialchars(trim($_POST['last_name']));
   $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email']));
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

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

    $_SESSION['success_message'] = "Profile updated successfully!";
header("Location: admin_dashboard.php");
exit();

}
?>

<!DOCTYPE html>
<html lang="en">
<button onclick="window.history.back()" class="back-button">Go Back</button>

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/edit_profile.css">
</head>

<body>
<div class="container">
    <h1>Edit Profile</h1>
    <?php if (isset($successMessage)) echo $successMessage; ?>

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
