<?php
$newPassword = 'santorini'; // Replace with your desired password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
echo $hashedPassword;
?>