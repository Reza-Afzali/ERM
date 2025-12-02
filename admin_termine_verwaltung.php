<?php
// DE: 1. Session- und Datenbankkonfiguration einbinden
require_once 'session_config.php';
require_once 'config.php';

// DE: Prüfen, ob der Benutzer eingeloggt und ein Admin ist (Wichtig!)
if (!isset($_SESSION["eingeloggt"]) || $_SESSION["eingeloggt"] !== true || $_SESSION["benutzer_typ"] !== "admin") {
    header("location: anmeldung.php"); 
    exit;
}

$fehlermeldung = "";
$erfolgsmeldung = "";
$termine = [];

// --- 2. DELETE- und UPDATE-Logik ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // DE: Lösch-Funktion (DELETE)
    if (isset($_POST['loesche_termin'])) {
        $termin_id_zu_loeschen = filter_input(INPUT_POST, 'termin_id', FILTER_VALIDATE_INT);
        if ($termin_id_zu_loeschen) {
            try {
                $sql_delete = "DELETE FROM termine WHERE termin_id = :tid";
                $stmt = $db_verbindung->prepare($sql_delete);
                $stmt->bindParam(':tid', $termin_id_zu_loeschen, PDO::PARAM_INT);
                $stmt->execute();
                $erfolgsmeldung = "Termin (ID: $termin_id_zu_loeschen) wurde erfolgreich gelöscht.";
            } catch (PDOException $e) {
                $fehlermeldung = "FEHLER beim Löschen: " . $e->getMessage();
            }
        }
    }

    // DE: Status-Update-Funktion (UPDATE)
    if (isset($_POST['status_aendern'])) {
        $termin_id_zu_aendern = filter_input(INPUT_POST, 'termin_id', FILTER_VALIDATE_INT);
        $neuer_status = filter_input(INPUT_POST, 'neuer_status', FILTER_SANITIZE_STRING); // z.B. 'bestätigt'
        
        if ($termin_id_zu_aendern && $neuer_status) {
            try {
                $sql_update = "UPDATE termine SET status = :status WHERE termin_id = :tid";
                $stmt = $db_verbindung->prepare($sql_update);
                $stmt->bindParam(':status', $neuer_status, PDO::PARAM_STR);
                $stmt->bindParam(':tid', $termin_id_zu_aendern, PDO::PARAM_INT);
                $stmt->execute();
                $erfolgsmeldung = "Status für Termin ID $termin_id_zu_aendern wurde auf '$neuer_status' geändert.";
            } catch (PDOException $e) {
                $fehlermeldung = "FEHLER beim Aktualisieren: " . $e->getMessage();
            }
        }
    }
}

// --- 3. SELECT: Alle zukünftigen Termine holen (mit JOINs) ---
try {
    // DE: SQL-Befehl mit JOINs, um Patienten- und Arztnamen zu erhalten
    $sql_select = "
        SELECT 
            t.termin_id, 
            t.termin_datum, 
            t.termin_zeit, 
            t.grund, 
            t.status, 
            p.vorname AS patient_vorname, 
            p.nachname AS patient_nachname,
            a.vorname AS arzt_vorname,
            a.nachname AS arzt_nachname
        FROM termine t
        JOIN patienten p ON t.patienten_id = p.patienten_id
        JOIN aerzte a ON t.arzt_id = a.arzt_id
        WHERE t.termin_datum >= CURDATE() -- Nur zukünftige Termine anzeigen
        ORDER BY t.termin_datum, t.termin_zeit
    ";
    
    $statement = $db_verbindung->prepare($sql_select);
    $statement->execute();
    $termine = $statement->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $fehlermeldung = "FEHLER: Termine konnten nicht geladen werden: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Admin: Terminverwaltung</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .bestaetigt { background-color: #d4edda; }
        .geplant { background-color: #fff3cd; }
    </style>
</head>
<body>
    <h1>Admin-Bereich: Terminverwaltung</h1>
    <p>Willkommen, <?php echo htmlspecialchars($_SESSION["vorname"] . " " . $_SESSION["nachname"]); ?>. Hier verwalten Sie alle zukünftigen Termine.</p>

    <?php if ($erfolgsmeldung): ?>
        <div style="color: green; font-weight: bold; padding: 10px; border: 1px solid green;"><?php echo $erfolgsmeldung; ?></div>
    <?php elseif ($fehlermeldung): ?>
        <div style="color: red; font-weight: bold; padding: 10px; border: 1px solid red;"><?php echo $fehlermeldung; ?></div>
    <?php endif; ?>

    <hr>
    
    <?php if (empty($termine)): ?>
        <p>Keine zukünftigen Termine gefunden.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Datum & Zeit</th>
                    <th>Arzt</th>
                    <th>Patient</th>
                    <th>Grund</th>
                    <th>Status</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($termine as $t): ?>
                <tr class="<?php echo htmlspecialchars($t['status'] === 'bestätigt' ? 'bestaetigt' : 'geplant'); ?>">
                    <td><?php echo htmlspecialchars($t['termin_id']); ?></td>
                    <td><?php echo htmlspecialchars($t['termin_datum'] . " " . $t['termin_zeit']); ?></td>
                    <td><?php echo htmlspecialchars($t['arzt_vorname'] . " " . $t['arzt_nachname']); ?></td>
                    <td><?php echo htmlspecialchars($t['patient_vorname'] . " " . $t['patient_nachname']); ?></td>
                    <td><?php echo htmlspecialchars(substr($t['grund'], 0, 50)) . '...'; ?></td>
                    <td><?php echo htmlspecialchars($t['status']); ?></td>
                    <td>
                        <?php if ($t['status'] !== 'bestätigt'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="termin_id" value="<?php echo htmlspecialchars($t['termin_id']); ?>">
                            <input type="hidden" name="neuer_status" value="bestätigt">
                            <button type="submit" name="status_aendern">Bestätigen</button>
                        </form>
                        <?php endif; ?>

                        <form method="post" style="display:inline;" onsubmit="return confirm('Sind Sie sicher, dass Sie diesen Termin löschen möchten?');">
                            <input type="hidden" name="termin_id" value="<?php echo htmlspecialchars($t['termin_id']); ?>">
                            <button type="submit" name="loesche_termin" style="background-color: red; color: white;">Löschen</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="admin_dashboard.php">Zurück zum Admin Dashboard</a> | <a href="abmelden.php">Abmelden</a></p>
</body>
</html>