<?php
// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=klinik_db;charset=utf8mb4", "root", "");

// Step 1: Get form data safely
$vorname        = $_POST['first_name'];
$nachname       = $_POST['last_name'];
$email          = $_POST['email'];
$telefon        = $_POST['phone'];
$termin_datum   = $_POST['date'];
$termin_zeit    = $_POST['time'];
$abteilung_id   = $_POST['department'];
$arzt_id        = $_POST['doctor'];
$besuchsgrund   = $_POST['reason'];
$patient_message = $_POST['patient_message'] ?? null;

// Step 2: Check if patient exists
$stmt = $pdo->prepare("SELECT patienten_id FROM patienten WHERE email = ?");
$stmt->execute([$email]);
$patient = $stmt->fetch();

if (!$patient) {
    // Step 3: Create new patient
    $pass_hash = password_hash("temp12345", PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO patienten (vorname, nachname, telefon, email, passwort_hash)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$vorname, $nachname, $telefon, $email, $pass_hash]);

    $patient_id = $pdo->lastInsertId();
} else {
    $patient_id = $patient['patienten_id'];
}

// Step 4: Insert appointment
$stmt = $pdo->prepare("
    INSERT INTO termine 
    (patienten_id, arzt_id, abteilung_id, termin_datum, termin_zeit, preferred_time, besuchsgrund, patient_message)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $patient_id,
    $arzt_id,
    $abteilung_id,
    $termin_datum,
    $termin_zeit,
    $termin_zeit,
    $besuchsgrund,
    $patient_message
]);

echo "Termin erfolgreich gespeichert!";
?>
