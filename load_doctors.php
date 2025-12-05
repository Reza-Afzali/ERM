<?php
require_once "config.php";

$department_id = $_POST['department_id'];

$stmt = $db_verbindung->prepare("
    SELECT arzt_id, vorname, nachname, titel 
    FROM aerzte 
    WHERE abteilung_id = ?
    ORDER BY nachname
");
$stmt->execute([$department_id]);

echo '<option value="">--- Select Doctor ---</option>';

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<option value="'.$row['arzt_id'].'">'.$row['titel'].' '.$row['vorname'].' '.$row['nachname'].'</option>';
}
