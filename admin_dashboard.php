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
    $middleName = htmlspecialchars(trim($_POST['middle_name']));
    $lastName = htmlspecialchars(trim($_POST['last_name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT); // Password hashing
    $role = htmlspecialchars(trim($_POST['role']));

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO Personnel (FirstName, MiddleName, LastName, Phone, Username, email, Password, role) 
             VALUES (:first_name, :middle_name, :last_name, :phone, :username, :email, :password, :role)"
        );
        $stmt->execute([
            ':first_name' => $firstName,
            ':middle_name' => $middleName,
            ':last_name' => $lastName,
            ':phone' => $phone,
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':role' => $role
        ]);

        $_SESSION['success_message'] = "Personnel added successfully!";
        header("Location: admin_dashboard.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<button onclick="window.history.back()">Go Back</button>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Personnel Management</title>
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <h1>Admin Dashboard</h1>

    <?php if (!empty($successMessage)): ?>
        <div class="success-message">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <h1>Personnel List</h1>

    <h3>Add New Personnel</h3>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="middle_name" placeholder="Middle Name">
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="text" name="username" placeholder="NSS Number" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
        <button type="submit" name="add_personnel">Add Personnel</button>
    </form>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Phone</th>
            <th>NSS Number</th>
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
                <td><?= htmlspecialchars($person['FirstName']) ?></td>
                <td><?= htmlspecialchars($person['MiddleName'] ?? '-') ?></td>
                <td><?= htmlspecialchars($person['LastName']) ?></td>
                <td><?= htmlspecialchars($person['Phone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($person['Username']) ?></td>
                <td><?= htmlspecialchars($person['email']) ?></td>
                <td><?= htmlspecialchars($person['role']) ?></td>
                <td>
                    <a href="edit_profile.php?id=<?= $person['PersonnelID'] ?>" class="btn btn-edit">Edit Profile</a> |
                    <a href="?delete=<?= $person['PersonnelID'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this personnel?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <form action="logout.php" method="POST">
        <button type="submit" class="btn-logout">Logout</button>
    </form>
    <form action="dashboard.php" method="POST">
        <button type="submit" class="btn-dashboard">User Dashboard</button>
    </form>
</body>
</html>
