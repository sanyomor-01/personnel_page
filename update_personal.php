<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$message = "";

$first_name = $middle_name = $last_name = $gender = $bio = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $middle_name = htmlspecialchars(trim($_POST['middle_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars(trim($_POST['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $gender = htmlspecialchars(trim($_POST['gender'] ?? ''), ENT_QUOTES, 'UTF-8');
    $bio = htmlspecialchars(trim($_POST['bio'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (!preg_match("/^[a-zA-Z]+$/", $first_name)) {
        $error = "Invalid first name. Only letters are allowed.";
    } elseif (!empty($middle_name) && !preg_match("/^[a-zA-Z]+$/", $middle_name)) {
        $error = "Invalid middle name. Only letters are allowed.";
    } elseif (!preg_match("/^[a-zA-Z]+$/", $last_name)) {
        $error = "Invalid last name. Only letters are allowed.";
    } elseif (empty($bio)) {
        $error = "Bio cannot be empty.";
    }

    if (empty($error)) {
        try {
            $sql = "UPDATE Personnel 
                    SET FirstName = :first_name, 
                        MiddleName = :middle_name, 
                        LastName = :last_name, 
                        Gender = :gender, 
                        Bio = :bio 
                    WHERE PersonnelID = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':last_name' => $last_name,
                ':gender' => $gender,
                ':bio' => $bio,
                ':user_id' => $user_id
            ]);

            if ($_FILES['profile_picture']['error'] == 0) {
                $target_dir = "uploads/";  
                $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
                if ($check !== false && in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                        $sql = "UPDATE Personnel SET ProfilePicture = :profile_picture WHERE PersonnelID = :user_id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            ':profile_picture' => $target_file,
                            ':user_id' => $user_id
                        ]);
                        $message = "Profile updated successfully!";
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $error = "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
                }
            }

            if (empty($error)) {
                header("Location: profile.php?message=" . urlencode("Personal information updated successfully"));
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error updating profile: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Personal Information</title>
    <link rel="stylesheet" href="css/update_profile.css">
</head>
<body>
    <button onclick="window.history.back()" class="back-button">Go Back</button>

    <form method="POST" action="update_personal.php" enctype="multipart/form-data">
        <h3>Personal Information</h3>

        <?php if (!empty($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <p class="success-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <label for="first_name">First Name: <span class="required-asterisk">*</span></label>
        <input type="text" name="first_name" id="first_name" placeholder="Enter your first name" value="<?= htmlspecialchars($first_name ?? '') ?>" required><br>

        <label for="middle_name">Middle Name:</label>
        <input type="text" name="middle_name" id="middle_name" placeholder="Enter your middle name" value="<?= htmlspecialchars($middle_name ?? '') ?>"><br>

        <label for="last_name">Last Name: <span class="required-asterisk">*</span></label>
        <input type="text" name="last_name" id="last_name" placeholder="Enter your last name" value="<?= htmlspecialchars($last_name ?? '') ?>" required><br>

        <label for="gender">Gender: <span class="required-asterisk">*</span></label>
        <select name="gender" id="gender" required>
            <option value="Male" <?= isset($gender) && $gender == 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= isset($gender) && $gender == 'Female' ? 'selected' : '' ?>>Female</option>
        </select><br>

        <label for="bio">Bio: <span class="required-asterisk">*</span></label>
        <textarea name="bio" id="bio" placeholder="Enter a short bio" required><?= htmlspecialchars($bio ?? '') ?></textarea><br>

        <label for="profile_picture">Profile Picture:</label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/*"><br>

        <button type="submit">Save Personal Information</button>
    </form>
</body>
</html>
