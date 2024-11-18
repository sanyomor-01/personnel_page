<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM Personnel WHERE PersonnelID = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_personal'])) {
    } elseif (isset($_POST['update_contact'])) {
    } elseif (isset($_POST['update_education'])) {
    } elseif (isset($_POST['update_projects'])) {
    } elseif (isset($_POST['update_web_presence'])) {
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="script.js"></script>
</head>
<body>
    <div class="container-sidebar">
        <div class="sidebar"><br>
            
        <h2>Profile Dashboard</h2>
        <hr class="divider">
            <ul>
                <li><a href="?section=personal">Personal Information</a></li>
                <li><a href="?section=contact">Contact Information</a></li>
                <li><a href="?section=education">Education</a></li>
                <li><a href="?section=projects">Projects</a></li>
                <li><a href="?section=web">Web Presence</a></li>
            </ul>
        </div>

        <div class="content-sidebar">
            <h1>Update Profile</h1>

            <?php
            $section = isset($_GET['section']) ? $_GET['section'] : 'personal'; // Default to personal section

            switch ($section) {
                case 'personal':
                    include 'update_personal.php';  
                    break;
                case 'contact':
                    include 'update_contact.php';   
                    break;
                case 'education':
                    include 'update_education.php';
                    break;
                case 'projects':
                    include 'update_projects.php';  
                    break;
                case 'web':
                    include 'update_web_presence.php'; 
                    break;
                default:
                    include 'update_personal.php';  
            }
            ?>
        </div>
    </div>
</body>
</html>
