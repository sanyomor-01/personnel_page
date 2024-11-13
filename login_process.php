<?php

session_start();
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    
    $stmt = $pdo->prepare("SELECT * FROM personnel WHERE Username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // var_dump($user, password_verify($password, $user['Password']), $password, $user['Password']);
    // die();

    if ($user && password_verify($password, $user['Password'])) {
        
        $_SESSION['user_id'] = $user['PersonnelID'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        
        $_SESSION['loginMessage'] = "Invalid username or password.";
        $_SESSION['messageType'] = "error";
        header("Location: login.php");
        exit();
    }
}
?>