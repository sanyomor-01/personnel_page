<?php
$newPassword = ''; 
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
echo $hashedPassword;
?>