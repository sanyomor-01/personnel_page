<?php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit("Unauthorized access");
}

require 'db_connect.php'; 

if (isset($_GET['delete'])) {
    $personnelID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM Personnel WHERE PersonnelID = :id");
    $stmt->execute([':id' => $personnelID]);

    $_SESSION['success_message'] = "Personnel record deleted successfully!";
    header("Location: admin_dashboard.php"); 
    exit();
}

$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);
    


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_personnel'])) {
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $lastName = htmlspecialchars(trim($_POST['last_name']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT); // Password hashing
    $role = htmlspecialchars(trim($_POST['role']));

    $stmt = $pdo->prepare("INSERT INTO Personnel (FirstName, LastName, Username, Email, Password, role) 
                           VALUES (:first_name, :last_name, :username, :email, :password, :role)");
    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':username' => $username,
        ':email' => $email,
        ':password' => $password,
        ':role' => $role
    ]);
    $message = "Personnel added successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<button onclick="window.history.back()">Go Back</button>

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Personnel Management</title>
   <link href="css/admin.css" rel="stylesheet"></a> 
</head>
<body>
    <h1>Admin Dashboard</h1>
    <?php if (!empty($successMessage)): ?>
        <div class="success-message">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <h2>Personnel List</h2>
    <h3>Add New Personnel</h3>
    <?php if (isset($message)) echo "<p>" . htmlspecialchars($message) . "</p>"; ?>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role">
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
        <button type="submit" name="add_personnel">Add Personnel</button>
    </form>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM Personnel");
        $stmt->execute();
        $personnelList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($personnelList as $person): ?>
            <tr>
                <td><?= htmlspecialchars($person['PersonnelID']) ?></td>
                <td><?= htmlspecialchars($person['FirstName'] . ' ' . $person['LastName']) ?></td>
                <td><?= htmlspecialchars($person['Username']) ?></td>
                <td><?= htmlspecialchars($person['email']) ?></td>
                <td><?= htmlspecialchars($person['role']) ?></td>
                <td>
                 
                    <a href="edit_profile.php?id=<?= $person['PersonnelID'] ?>"class="btn btn-edit">Edit Profile</a> |
                    <!-- <a href="?delete=<?= $person['PersonnelID'] ?>" onclick="return confirm('Are you sure you want to delete this personnel?')">Delete</a> -->
                   <a href="delete.php?id=<?= $person['PersonnelID'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this personnel?');">Delete Profile</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table> <br>
    <form action="logout.php" method="POST">
            <button type="submit" class="btn-logout">Logout</button>
        </form> 
        <form action="dashboard.php" method="POST">
            <button type="submit" class="btn-dasboard">User Dashboard</button>
        </form>
</body>
</html>