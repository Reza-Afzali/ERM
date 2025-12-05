<?php
require_once "config.php";

// Collect form data
$vorname = $_POST['first_name'];
$nachname = $_POST['last_name'];
$email = $_POST['email'];
$telefon = $_POST['phone'];
$datum = $_POST['date'];
$zeit = $_POST['time'];
$abteilung = $_POST['department'];
$arzt = $_POST['doctor'];
$grund = $_POST['reason'];

// Check if patient exists
$stmt = $db_verbindung->prepare("SELECT patienten_id FROM patienten WHERE email = ?");
$stmt->execute([$email]);
$patient = $stmt->fetch();

if (!$patient) {
    $pass_hash = password_hash("temp12345", PASSWORD_BCRYPT);

    $stmt = $db_verbindung->prepare("
        INSERT INTO patienten (vorname, nachname, telefon, email, passwort_hash)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$vorname, $nachname, $telefon, $email, $pass_hash]);
    $patient_id = $db_verbindung->lastInsertId();
} else {
    $patient_id = $patient["patienten_id"];
}

// Save appointment
$stmt = $db_verbindung->prepare("
    INSERT INTO termine 
    (patienten_id, arzt_id, abteilung_id, termin_datum, termin_zeit, preferred_time, besuchsgrund)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $patient_id,
    $arzt,
    $abteilung,
    $datum,
    $zeit,
    $zeit,
    $grund
]);

echo "SUCCESS";
