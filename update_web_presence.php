<?php

// session_start();
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
    $socialLinks = $_POST['social_links'] ?? [];

    try {
        foreach ($socialLinks as $platform => $link) {
            $link = trim($link);

            if (!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
                throw new Exception("Invalid URL format for $platform.");
            }

            if (!empty($link)) {
                $stmt = $pdo->prepare("SELECT WebServiceID FROM WebService WHERE Name = :platform");
                $stmt->execute([':platform' => $platform]);
                $platformData = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($platformData) {
                    $webServiceID = $platformData['WebServiceID'];
                } else {
                    $stmt = $pdo->prepare("INSERT INTO WebService (Name) VALUES (:platform)");
                    $stmt->execute([':platform' => $platform]);
                    $webServiceID = $pdo->lastInsertId();
                }
        
                $stmt = $pdo->prepare("INSERT INTO WebPresence (PersonnelID, WebServiceID, SocialLink) 
                                       VALUES (:personnel_id, :platform_id, :link)
                                       ON DUPLICATE KEY UPDATE SocialLink = :link");
                $stmt->execute([
                    ':personnel_id' => $user_id,
                    ':platform_id' => $webServiceID,
                    ':link' => $link
                ]);
            }
        }

        header("Location: profile.php?message=" . urlencode("Web presence updated successfully"));
        exit();
    } catch (Exception $e) {
        echo "Error updating web presence: " . htmlspecialchars($e->getMessage());
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Web Presence</title>
    <link rel="stylesheet" href="css/update_profile.css">
   <script src="script.js"></script>
</head>
<body>

    <button onclick="window.history.back()" class="back-button">Go Back</button>

    <form method="POST" action="update_web_presence.php">
        <h3>Web Presence</h3>
        <p>Select social media platforms and add your profile links:</p>

        <label>
            <input type="checkbox" name="platforms[]" value="Facebook" onclick="updateSocialLinks()"> Facebook
        </label><br>

        <label>
            <input type="checkbox" name="platforms[]" value="Twitter" onclick="updateSocialLinks()"> Twitter
        </label><br>

        <label>
            <input type="checkbox" name="platforms[]" value="LinkedIn" onclick="updateSocialLinks()"> LinkedIn
        </label><br>

        <label>
            <input type="checkbox" name="platforms[]" value="Instagram" onclick="updateSocialLinks()"> Instagram
        </label><br>

        <label>
            <input type="checkbox" name="platforms[]" value="GitHub" onclick="updateSocialLinks()"> GitHub
        </label><br>

        <div id="social_links"></div>

        <button type="submit">Save Web Presence</button>
    </form>

</body>
</html>