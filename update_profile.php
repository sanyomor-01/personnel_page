<?php
session_start();
require 'db_connect.php';

$institutions = [
    "Kwame Nkrumah University of Science and Technology",
    "University of Education, Winneba",
    "University of Health and Allied Sciences",
    "University of Cape Coast",
    "University of Ghana",
    "University of Professional Studies, Accra",
    "University of Development Studies"
];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['first_name'];
    $middleName = $_POST['middle_name'];
    $lastName = $_POST['last_name'];
    $bio = $_POST['bio'];
    $project = $_POST['project'];
    $department = $_POST['department'];
    $gender = $_POST['gender'];
    $institution = $_POST['institution'];
    $field_of_study = $_POST['field_of_study'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $project_title = $_POST['project_title'];
    $project_role = $_POST['project_role'];
    $phoneNumber = $_POST['phone'];
    $socialLinks = isset($_POST['social_links']) ? $_POST['social_links'] : [];

    $profilePicturePath = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadsDir = 'uploads/profile_pictures/';
        $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
        $targetFilePath = $uploadsDir . $fileName;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            $profilePicturePath = $targetFilePath;
        } else {
            $message = "Error uploading profile picture.";
        }
    }
ob_start();
    try {
        $sql = "UPDATE Personnel SET 
                    FirstName = :first_name, 
                    MiddleName = :middle_name, 
                    LastName = :last_name, 
                    Bio = :bio, 
                    Project = :project, 
                    Department = :department, 
                    Email = :email,
                    Gender = :gender";

        if ($profilePicturePath) {
            $sql .= ", ProfilePicture = :profile_picture";
        }

        $sql .= " WHERE PersonnelID = :user_id";

        $params = [
            ':first_name' => $firstName,
            ':middle_name' => $middleName,
            ':last_name' => $lastName,
            ':bio' => $bio,
            ':project' => $project,
            ':department' => $department,
            'email' =>$email,
            ':gender' => $gender,
            ':user_id' => $user_id
        ];

        if ($profilePicturePath) {
            $params[':profile_picture'] = $profilePicturePath;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $stmt = $pdo->prepare("UPDATE contact SET PhoneNumber = :phone_number WHERE PersonnelID = :user_id");
        $stmt->execute([
            ':phone_number' => $phoneNumber,
            ':user_id' => $user_id
        ]);

        $stmt = $pdo->prepare("SELECT InstitutionID FROM institution WHERE InstitutionName = :institution_name");
        $stmt->execute([':institution_name' => $institution]);
        $institutionData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($institutionData) {
            $institutionID = $institutionData['InstitutionID'];
        } else {
            $message = "Selected institution not found in the database.";
            echo "<p class='error'>$message</p>";
            exit();
        }

        $sql = "SELECT COUNT(*) FROM Education 
                WHERE PersonnelID = :user_id AND InstitutionID = :institution_id AND FieldOfStudyID = :field_of_study";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':institution_id' => $institutionID,
            ':field_of_study' => $field_of_study
        ]);

        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo "This institution and field of study already exist.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM Education WHERE PersonnelID = :user_id");
            $stmt->execute([':user_id' => $user_id]);

            $stmt = $pdo->prepare("INSERT INTO Education (PersonnelID, InstitutionID, FieldOfStudyID, StartDate, EndDate) 
                                   VALUES (:user_id, :institution_id, :field_of_study, :start_date, :end_date)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':institution_id' => $institutionID,
                ':field_of_study' => $field_of_study,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);
        }

        $sql = "SELECT COUNT(*) FROM projects WHERE PersonnelID = :user_id AND ProjectTitle = :project_title";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id, ':project_title' => $project_title]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $sql = "INSERT INTO projects (PersonnelID, ProjectTitle, Role, Date) VALUES (:user_id, :project_title, :project_role, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id' => $user_id, ':project_title' => $project_title, ':project_role' => $project_role]);
        } else {
            echo "This project already exists.";
        }

        foreach ($socialLinks as $platform => $link) {
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
        
                $stmt = $pdo->prepare("SELECT * FROM WebPresence WHERE PersonnelID = :personnel_id AND WebServiceID = :platform_id");
                $stmt->execute([':personnel_id' => $user_id, ':platform_id' => $webServiceID]);
        
                if ($stmt->rowCount() > 0) {
                    // If entry exists, update the link
                    $stmt = $pdo->prepare("UPDATE WebPresence SET SocialLink = :link WHERE PersonnelID = :personnel_id AND WebServiceID = :platform_id");
                    $stmt->execute([
                        ':link' => $link,
                        ':personnel_id' => $user_id,
                        ':platform_id' => $webServiceID
                    ]);
                } else {
                    // If entry does not exist, insert new record
                    $stmt = $pdo->prepare("INSERT INTO WebPresence (PersonnelID, WebServiceID, SocialLink) VALUES (:personnel_id, :platform_id, :link)");
                    $stmt->execute([
                        ':personnel_id' => $user_id,
                        ':platform_id' => $webServiceID,
                        ':link' => $link
                    ]);
                }
            }
        }

        $message = "Profile updated successfully!";
        header("Location: profile.php?message=" . urldecode("Profile updated succesfully"));
        exit();

    } catch (PDOException $e) {
        $message = "Error updating profile: " . htmlspecialchars($e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<button onclick="window.history.back()" class="back-button">Go Back</button>

<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="stylesheet" href="css/style.css">
  
</head>
<body>
<div class="container">
    <h2>Update Your Profile</h2>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
        <a href="profile.php" class="btn-view-profile">View Updated Profile</a>
    <?php endif; ?>

    <form method="POST" action="update_profile.php" enctype="multipart/form-data">
   
    <h3>Upload Profile Picture</h3>
    <input type="file" name="profile_picture" accept="image/*"><br>
    
        <h3>Personal Information</h3>
        <label>First Name:</label>
        <input type="text" name="first_name"  placeholder="Your Firstname" value="<?= htmlspecialchars($firstname ?? '') ?>" required><br>
        <label>Middle Name:</label>
        <input type="text" name="middle_name" value="<?= htmlspecialchars($personnel['Name'] ?? '') ?>" placeholder="Your Middlename"  value="<?= htmlspecialchars($middlename ?? '') ?>" ><br>
        
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($personnel['Name'] ?? '') ?>" placeholder="Your Lastname"  value="<?= htmlspecialchars($lastname ?? '') ?>" required><br>
        
        <label>Gender:</label>
        <select name="gender" required>
            <option value="Male" <?= isset($gender) && $gender == 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= isset($gender) && $gender == 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= isset($gender) && $gender == 'Other' ? 'selected' : '' ?>>Other</option>
        </select><br>


        <label>Bio:</label>
        <input type="text" name="bio" value="<?= htmlspecialchars($bio ?? '') ?>" placeholder="Your bio" required><br>
        
        <h3>NSS Information</h3>
        <label>Department/Section :</label>
        <input type="text" name="department" placeholder="Department" value="<?= htmlspecialchars($personnel['Department']?? ''); ?>"> <br> 
        
        
        <label>Start Date:</label>
        <input type="date" name="start_date"><br>
        
        <label>End Date:</label>
        <input type="date" name="end_date"><br>
       
        <h3>Project Information</h3>

        <label>Project Title:</label>
        <input type="text" name="project_title" placeholder="Project Title"><br>
       
        <label> Project Info:</label>
        <input type="text" name="project" value="<?= htmlspecialchars($project ?? '') ?>" placeholder="Current project" required><br>

         <label>Role in Project:</label>
        <input type="text" name="project_role" placeholder="Project Role"><br>
        
       
        <h3>Additional Information</h3>
        
        <label>Institution Completed:</label>
<select name="institution" required>
    <option value="">Select an institution</option>
    <?php foreach ($institutions as $school): ?>
        <option value="<?= htmlspecialchars($school) ?>" <?= isset($personnel['Institution']) && $personnel['Institution'] == $school ? 'selected' : '' ?>>
            <?= htmlspecialchars($school) ?>
        </option>
    <?php endforeach; ?>
</select><br>
        
<?php

$stmt = $pdo->prepare("SELECT * FROM FieldOfStudy");
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


    <h3>Field of Study</h3>
    <label for="field_of_study">Select a field of study:</label>
    <select name="field_of_study" id="field_of_study" required>
        <option value="">Select a field of study</option>
        <?php foreach ($courses as $course): ?>
            <option value="<?= htmlspecialchars($course['FieldOfStudyID']) ?>">
                <?= htmlspecialchars($course['FieldOfStudyName']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <h3>Or, enter your own field of study:</h3>
    <label for="custom_field_of_study">Your field of study (if not listed):</label>
    <input type="text" name="custom_field_of_study" id="custom_field_of_study" placeholder="Enter your own field of study">
    
    


        <label>Phone Number:</label>
        <input type="text" name="phone" placeholder="Phone Number"><br>
        
        
        
    <h3>Web Presence</h3>
        <p>Select social media platforms and add your profile links:</p>
        <label>
        <input type="checkbox" name="platforms[]" value="Facebook" onclick="updateSocialLinks()"> Facebook<br>
        </label>

        <label>
        <input type="checkbox" name="platforms[]" value="Twitter" onclick="updateSocialLinks()"> Twitter<br>
</label>

        <label>
        <input type="checkbox" name="platforms[]" value="LinkedIn" onclick="updateSocialLinks()"> LinkedIn<br>
        </label>

        <label>
        <input type="checkbox" name="platforms[]" value="Instagram" onclick="updateSocialLinks()"> Instagram<br>
</label>

        <label>
        <input type="checkbox" name="platforms[]" value="GitHub" onclick="updateSocialLinks()"> GitHub<br>
        </label>
        
        <div id="social_links">
        </div>

        <button type="submit">Save Profile</button>
    </form>
    <form action="logout.php" method="POST">
        <button type="submit" class="btn-logout">Logout</button>
    </form>
</div>
<script src="script.js"></script>
</body>
<?php ob_end_flush(); ?>

</html>