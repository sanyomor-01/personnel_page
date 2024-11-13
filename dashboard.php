<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=nssdirectory_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM Personnel WHERE PersonnelID = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$personnel = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<button onclick="window.history.back()">Go Back</button>

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
         <h2>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h2> 
        <p>You are logged in as <?= htmlspecialchars($_SESSION['username']); ?>.</p>

        <div class="profile-overview">
            <h3>Your Profile</h3>
          <!--  <p><strong>Full Name:</strong> <?= htmlspecialchars($personnel['FirstName'] . ' ' . $personnel['LastName']); ?></p>
           <p><strong>NSS Number:</strong> <?= htmlspecialchars($personnel['NSSNumber']); ?></p> -->
            <p><strong>Bio:</strong> <?= htmlspecialchars($personnel['Bio'] ?? 'Not provided'); ?></p>
            <p><strong>Current Project:</strong> <?= htmlspecialchars($personnel['Project'] ?? 'Not provided'); ?></p>
        </div>

        <div class="profile-update">
            <h3>Update Basic Profile Info</h3>
            <form action="update_profile.php" method="POST">
           
                <label for="bio">Bio:</label>
                <textarea name="bio" id="bio" rows="4" cols="50" placeholder="Enter your bio..."><?= htmlspecialchars($personnel['Bio'] ?? '') ?></textarea><br>

                <label for="project">Current Project:</label>
                <input type="text" name="project" id="project" value="<?= htmlspecialchars($personnel['Project'] ?? '') ?>" placeholder="Current Project"><br>

                <button type="submit">Update Profile</button>
            </form>
        </div>

        <div class="full-profile-update">
            <h3>Update Complete Profile</h3>
            <p>To update complete profile, Visit the <a href="update_profile.php">Complete Profile Update Page</a>.</p>
        </div>
        <div class="view-profile-">
            <h3>View Profile</h3>
            <p>To view complete profile, Visit the <a href="profile.php">Complete Profile Page</a>.</p>
        </div>

        <form action="logout.php" method="POST">
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </div>
</body>
</html>