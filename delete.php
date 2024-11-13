<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require 'db_connect.php'; 

if (isset($_GET['id'])) {
    $personnelID = $_GET['id'];

    $stmt = $pdo->prepare("DELETE FROM Personnel WHERE PersonnelID = :id");
    $stmt->execute([':id' => $personnelID]);

    header("Location: admin_dashboard.php?message=delete_success");
    exit();
} else {
    echo "No ID provided!";
    exit();
}
?>