<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css"> 
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
</head>
<body>
    <main class="dashboard-container">
        <header class="dashboard-header">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h1>
            <button onclick="window.history.back()" class="back-button">Go Back</button>
        </header>

        <section class="profile-overview">
            <h2>Your Profile</h2>
            <img src="<?= htmlspecialchars($personnel['ProfilePicture'] ?? 'defaultprofile.jpg'); ?>" alt="Profile Picture" style="width:150px;height:150px;border-radius:50%;">
            <p><strong>Full Name:</strong> <?= htmlspecialchars($personnel['FirstName'] . ' ' . $personnel['MiddleName'] . ' ' . $personnel['LastName']); ?></p>
            <p><strong>NSS Number:</strong> <?= htmlspecialchars($personnel['Username']); ?></p>
            <p><strong>Bio:</strong> <?= htmlspecialchars($personnel['Bio'] ?? 'Not provided'); ?></p>
            <!-- <p><strong>Current Project:</strong> <?= htmlspecialchars($personnel['Project'] ?? 'Not provided'); ?></p> -->
        </section>

        <section class="profile-update">
            <h2>Update Basic Profile Info</h2>
            <form action="update_profile.php" method="POST">
                <label for="bio">Bio:</label>
                <textarea name="bio" id="bio" rows="4" placeholder="Enter your bio..."><?= htmlspecialchars($personnel['Bio'] ?? '') ?></textarea>

                <label for="firstname">First Name</label>
                <input type="text" name="firstname" id="firstname" value="<?=htmlspecialchars($personnel['FirstName'] ?? '') ?>" placeholder="First Name">
                
                <label for="middlename">Middle Name</label>
                <input type="text" name="middlename" id="middlename" value="<?=htmlspecialchars($personnel['MiddleName'] ?? '') ?>" placeholder="Middle Name">
               
                <label for="lastname">First Name</label>
                <input type="text" name="lastname" id="lastname" value="<?=htmlspecialchars($personnel['LastName'] ?? '') ?>" placeholder="Last Name">
               
               
                <!-- <label for="project">Current Project:</label> -->
                <!-- <input type="text" name="project" id="project" value="<?= htmlspecialchars($personnel['Project'] ?? '') ?>" placeholder="Current Project"> -->

                <button type="submit">Update Profile</button>
            </form>
        </section>

        <section class="links">
            <h2>Additional Options</h2>
            <a href="update_profile.php" class="btn-link">Update Complete Profile</a>
            <a href="profile.php" class="btn-link">View Profile</a>
        </section>

        <form action="logout.php" method="POST">
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </main>
</body>
</html>
