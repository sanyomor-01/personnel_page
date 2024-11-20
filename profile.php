<?php
session_start();
require 'db_connect.php';

$message = '';

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT * FROM personnel WHERE PersonnelID = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $personnel = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "
        SELECT e.*, i.InstitutionName, f.FieldOfStudyName
        FROM education e
        LEFT JOIN institution i ON e.InstitutionID = i.InstitutionID
        LEFT JOIN fieldofstudy f ON e.FieldOfStudyID = f.FieldOfStudyID
        WHERE e.PersonnelID = :user_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM projects WHERE PersonnelID = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "
    SELECT StartDate, EndDate 
    FROM education 
    WHERE PersonnelID = :user_id 
    ORDER BY EndDate DESC 
    LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $educationDates = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch web presence with platform name
    $sql = "SELECT wp.SocialLink, ws.Name
    FROM webpresence wp
    JOIN webservice ws ON wp.WebServiceID = ws.WebServiceID
    WHERE wp.PersonnelID = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$webPresences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($personnel) {
        $profilePicture = $personnel['ProfilePicture'] ?? 'defaultprofile.jpg';
    }

} catch (PDOException $e) {
    die("Error retrieving profile information: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <div class="container">
        <button onclick="window.history.back()" class="back-button">Go Back</button>

        <?php if (!empty($message)): ?>
            <p class="message success"><?= $message ?></p>
        <?php endif; ?>

        <h1>Your Complete Profile</h1>
        
        <h3>Personal Information</h3>
        <img src="<?= htmlspecialchars($personnel['ProfilePicture'] ?? 'defaultprofile.jpg'); ?>" alt="Profile Picture" style="width:150px;height:150px;border-radius:50%;">
        <p><strong>Username:</strong> <?= htmlspecialchars($personnel['Username'] ?? 'N/A'); ?></p>
        <p><strong>First Name:</strong> <?= htmlspecialchars($personnel['FirstName'] ?? 'N/A'); ?></p>
        <p><strong>Middle Name:</strong> <?= htmlspecialchars($personnel['MiddleName'] ?? 'N/A'); ?></p>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($personnel['LastName'] ?? 'N/A'); ?></p>
        <p><strong>Gender:</strong> <?= htmlspecialchars($personnel['Gender'] ?? 'N/A'); ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($personnel['Department'] ?? 'N/A'); ?></p>
        <p><strong>Start Date:</strong> <?= htmlspecialchars($educationDates['StartDate'] ?? 'N/A'); ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($educationDates['EndDate'] ?? 'N/A'); ?></p>
        <p><strong>Bio:</strong> <?= htmlspecialchars($personnel['Bio'] ?? 'N/A'); ?></p>

        <h3>Contact Information</h3>
        <p><strong>Phone Number:</strong> <?= htmlspecialchars($personnel['Phone'] ?? 'N/A'); ?></p>
        <p><strong>Email Address:</strong> <?= htmlspecialchars($personnel['email'] ?? 'N/A'); ?></p>

        <h3>Education</h3>
        <?php if ($education): ?>
            <?php foreach ($education as $edu): ?>
                <p><strong>Institution:</strong> <?= htmlspecialchars($edu['InstitutionName'] ?? 'N/A'); ?></p>
                <p><strong>Field of Study:</strong> <?= htmlspecialchars($edu['FieldOfStudyName'] ?? 'N/A'); ?></p>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No education information provided.</p>
        <?php endif; ?>

        <h3>Project Information</h3>
        <?php if ($projects): ?>
            <?php foreach ($projects as $project): ?>
                <p><strong>Project Title:</strong> <?= htmlspecialchars($project['ProjectTitle'] ?? 'N/A'); ?></p>
                <p><strong>Role:</strong> <?= htmlspecialchars($project['Role'] ?? 'N/A'); ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($project['Date'] ?? 'N/A'); ?></p>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No project information provided.</p>
        <?php endif; ?>

        <h3>Web Presence</h3>
        <?php if ($webPresences): ?>
    <?php foreach ($webPresences as $web): ?>
        <p><strong>Web Service:</strong> <?= htmlspecialchars($web['Name'] ?? 'N/A'); ?></p>
        <p><strong>Social Link:</strong> <a href="<?= htmlspecialchars($web['SocialLink'] ?? '#'); ?>" target="_blank"><?= htmlspecialchars($web['SocialLink'] ?? 'N/A'); ?></a></p>
        <hr>
    <?php endforeach; ?>
<?php else: ?>
    <p>No web presence information provided.</p>
<?php endif; ?>

        <p><a href="update_profile.php">Edit Profile</a></p> 

        <div style="margin-top: 20px;">
            <form action="logout.php" method="POST">
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>
    </div>
</body>
</html>