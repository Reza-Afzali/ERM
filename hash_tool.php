<?php
$passwort = 'admin123'; 
$hashed_passwort = password_hash($passwort, PASSWORD_DEFAULT);
echo $hashed_passwort; // سيُخرج لك سلسلة التشفير الطويلة
?>