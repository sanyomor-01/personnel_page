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
    $institution = $_POST['InstitutionName'];
    $field_of_study = $_POST['FieldOfStudy'];
    $start_date = $_POST['StartDate'];
    $end_date = $_POST['EndDate'];
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

    try {
        $sql = "UPDATE Personnel SET 
                    FirstName = :first_name, 
                    MiddleName = :middle_name, 
                    LastName = :last_name, 
                    Bio = :bio, 
                    Project = :project, 
                    Department = :department, 
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

       // Retrieve InstitutionID from the institution name selected in the form
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

// Now use $institutionID when updating Education table
if (!empty($institutionID) && !empty($field_of_study)) {
    $stmt = $pdo->prepare("REPLACE INTO Education (PersonnelID, InstitutionID, FieldOfStudyID) VALUES (:user_id, :institution_id, :field_of_study)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':institution_id' => $institutionID,
        ':field_of_study' => $field_of_study
    ]);
}


    
    // Check if a predefined course was selected
    if (!empty($_POST['field_of_study'])) {
        $field_of_study = $_POST['field_of_study'];
    } else {
        // Use the custom input field if a predefined one is not selected
        $field_of_study = $_POST['custom_field_of_study'];
        
        // If a custom course is entered, you may want to insert it into the database for future use
        if (!empty($field_of_study)) {
            // Insert the new custom course into the FieldOfStudy table
            $stmt = $pdo->prepare("INSERT INTO FieldOfStudy (Name) VALUES (:field_of_study)");
            $stmt->execute([':field_of_study' => $field_of_study]);

            // Fetch the ID of the newly inserted course
            $field_of_study_id = $pdo->lastInsertId();
        }
    }

    // Save the selected or entered course in the Education table
    $stmt = $pdo->prepare("UPDATE Education SET FieldOfStudyID = :field_of_study_id WHERE PersonnelID = :user_id");
    $stmt->execute([
        ':field_of_study_id' => !empty($field_of_study) ? $field_of_study : $field_of_study_id,
        ':user_id' => $user_id
    ]);






















    // Example of checking for duplicates before inserting
$sql = "SELECT COUNT(*) FROM projects WHERE PersonnelID = :user_id AND ProjectTitle = :project_title";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id, ':project_title' => $project_title]);
$count = $stmt->fetchColumn();

if ($count == 0) {
    // Proceed with inserting the new project if no duplicate is found
   $sql = "INSERT INTO projects (PersonnelID, ProjectTitle, Role, Date) VALUES (:user_id, :project_title, :project_role, :date)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id, ':project_title' => $project_title, ':project_role' => $project_role, ':date' => $date]);
} else {
    echo "This project already exists.";
}

        // Insert or update Projects
       // if (!empty($project_title) && !empty($project_role)) {
         //   $stmt = $pdo->prepare("REPLACE INTO Projects (PersonnelID, ProjectTitle, Role, Date) VALUES (:user_id, :project_title, :project_role, NOW())");
           // $stmt->execute([
             //   ':user_id' => $user_id,
               // ':project_title' => $project_title,
               // ':project_role' => $project_role
          //  ]);
        //}

        // Insert or update WebPresence using REPLACE INTO
        foreach ($socialLinks as $platform => $link) {
            if (!empty($link)) {
                $stmt = $pdo->prepare("SELECT WebServiceID FROM WebService WHERE Name = :platform");
                $stmt->execute([':platform' => $platform]);

                if ($stmt->rowCount() == 0) {
                    // Insert new platform into WebService if not existing
                    $stmt = $pdo->prepare("INSERT INTO WebService (Name) VALUES (:platform)");
                    $stmt->execute([':platform' => $platform]);
                    $webServiceID = $pdo->lastInsertId();
                } else {
                    $platformData = $stmt->fetch(PDO::FETCH_ASSOC);
                    $webServiceID = $platformData['WebServiceID'];
                }

                // Use REPLACE INTO to add or update the web presence
                $stmt = $pdo->prepare("REPLACE INTO WebPresence (PersonnelID, WebServiceID, SocialLink) VALUES (:personnel_id, :platform_id, :link)");
                $stmt->execute([
                    ':personnel_id' => $user_id,
                    ':platform_id' => $webServiceID,
                    ':link' => $link
                ]);
            }
        }

        $message = "Profile updated successfully!";
        header("Location: profile.php");
        exit();

    } catch (PDOException $e) {
        $message = "Error updating profile: " . htmlspecialchars($e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">
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
</html>