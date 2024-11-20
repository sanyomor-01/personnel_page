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
    $project_title = $_POST['project_title'];
    $project_role = $_POST['project_role'];

    try {
        $sql = "INSERT INTO projects (PersonnelID, ProjectTitle, Role, Date) VALUES (:user_id, :project_title, :project_role, NOW())
                ON DUPLICATE KEY UPDATE Role = :project_role, Date = NOW()";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':project_title' => $project_title,
            ':project_role' => $project_role
        ]);

        header("Location: profile.php?message=" . urlencode("Project updated successfully"));
        exit();
    } catch (PDOException $e) {
        echo "Error updating project: " . $e->getMessage();
    }
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Project Information</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <button onclick="window.history.back()" class="back-button">Go Back</button> 


</head>
<body>

    <form method="POST" action="update_projects.php">
        <h3>Project Information</h3>

        <label for="project_title">Project Title: <span class="required-asterisk">*</span></label>
        <input type="text" name="project_title" id="project_title" placeholder="Enter project title" value="<?= htmlspecialchars($project_title ?? '') ?>" required><br>

        <label for="project_role">Role in Project:</label>
        <input type="text" name="project_role" id="project_role" placeholder="Enter your role in the project" value="<?= htmlspecialchars($project_role ?? '') ?>"><br>

        <label for="project">Project Description: <span class="required-asterisk">*</span></label>
        <textarea name="project" id="project" placeholder="Enter project details" required><?= htmlspecialchars($project ?? '') ?></textarea><br>

        <button type="submit">Save Project Information</button>
    </form>

</body>
</html>