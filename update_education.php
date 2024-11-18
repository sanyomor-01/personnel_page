<?php
// session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db_connect.php';

$stmt = $pdo->prepare("SELECT InstitutionID, InstitutionName FROM Institution");
$stmt->execute();
$institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $institution = $_POST['institution'];
    $field_of_study = $_POST['field_of_study'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    if (empty($institution) || !is_numeric($institution)) {
        $error = "Please select a valid institution.";
    }

    if (!isset($error)) {
        try {
            $sql = "INSERT INTO Education (PersonnelID, InstitutionID, FieldOfStudyID, StartDate, EndDate) 
                    VALUES (:user_id, :institution_id, :field_of_study, :start_date, :end_date)
                    ON DUPLICATE KEY UPDATE 
                        StartDate = :start_date, EndDate = :end_date";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':institution_id' => $institution,
                ':field_of_study' => $field_of_study,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);

            header("Location: profile.php?message=" . urlencode("Education updated successfully"));
            exit();
        } catch (PDOException $e) {
            echo "Error updating education: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Education Information</title>
    <link rel="stylesheet" href="css/style.css">
    <button onclick="window.history.back()" class="back-button">Go Back</button>
</head>
<body>

    <form method="POST" action="update_education.php">
        <h3>Education Information</h3>

        <label for="institution">Institution: <span class="required-asterisk">*</span></label>
        <select name="institution" id="institution" required>
            <option value="">Select an institution</option>
            <?php foreach ($institutions as $institution): ?>
                <option value="<?= htmlspecialchars($institution['InstitutionID']) ?>" <?= isset($institution) && $institution == $institution['InstitutionID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($institution['InstitutionName']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <?php
        $stmt = $pdo->prepare("SELECT * FROM FieldOfStudy");
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <label for="field_of_study">Select a field of study: <span class="required-asterisk">*</span></label>
        <select name="field_of_study" id="field_of_study" required>
            <option value="">Select a field of study</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= htmlspecialchars($course['FieldOfStudyID']) ?>">
                    <?= htmlspecialchars($course['FieldOfStudyName']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <h3>Or, enter your own field of study:</h3>
        <label for="custom_field_of_study">Your field of study (if not listed):</label>
        <input type="text" name="custom_field_of_study" id="custom_field_of_study" placeholder="Enter your own field of study"><br>

        <label for="start_date">Start Date: <span class="required-asterisk">*</span></label>
        <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date ?? '') ?>" required><br>

        <label for="end_date">End Date: <span class="required-asterisk">*</span></label>
        <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date ?? '') ?>" required><br>

        <button type="submit">Save Education Information</button>
    </form>

</body>
</html>
