<?php
// DE: Session- und Datenbankkonfiguration einbinden
require_once 'session_config.php';
require_once 'config.php';

// Prüfen, ob der Benutzer eingeloggt und ein Admin ist
if (!isset($_SESSION["eingeloggt"]) || $_SESSION["eingeloggt"] !== true || $_SESSION["benutzer_typ"] !== "admin") {
    header("location: anmeldung.php"); 
    exit;
}

$erfolgsmeldung = $fehlermeldung = "";
$alle_termine = [];

// --- 1. UPDATE-Logik: Status ändern ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status_update'])) {
    $termin_id = filter_input(INPUT_POST, 'termin_id', FILTER_VALIDATE_INT);
    $neuer_status = trim($_POST['neuer_status']);

    // Validierung des Status (nur erlaubte Werte)
    if ($termin_id && in_array($neuer_status, ['geplant', 'bestätigt', 'abgeschlossen', 'abgesagt'])) {
        try {
            // SQL-Befehl UPDATE: Ändere den Status basierend auf der Termin ID
            $sql_update = "UPDATE termine SET status = :status WHERE termin_id = :tid";
            $stmt = $db_verbindung->prepare($sql_update);
            $stmt->bindParam(':status', $neuer_status, PDO::PARAM_STR);
            $stmt->bindParam(':tid', $termin_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $erfolgsmeldung = "Status für Termin ID " . $termin_id . " erfolgreich auf '" . $neuer_status . "' geändert.";
        } catch (PDOException $e) {
            $fehlermeldung = "FEHLER beim Aktualisieren des Status: " . $e->getMessage();
        }
    }
}

// --- 2. DELETE-Logik: Termin löschen (Admin kann jeden Termin löschen) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['termin_loeschen'])) {
    $termin_id_zu_loeschen = filter_input(INPUT_POST, 'termin_id', FILTER_VALIDATE_INT);

    if ($termin_id_zu_loeschen) {
        try {
            // SQL-Befehl DELETE: Lösche den Termin
            $sql_delete = "DELETE FROM termine WHERE termin_id = :tid";
            $stmt = $db_verbindung->prepare($sql_delete);
            $stmt->bindParam(':tid', $termin_id_zu_loeschen, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $erfolgsmeldung = "Termin ID " . $termin_id_zu_loeschen . " wurde erfolgreich gelöscht.";
            } else {
                $fehlermeldung = "FEHLER: Termin ID " . $termin_id_zu_loeschen . " konnte nicht gefunden werden.";
            }
        } catch (PDOException $e) {
            $fehlermeldung = "FEHLER beim Löschen des Termins: " . $e->getMessage();
        }
    }
}

// --- 3. SELECT-Logik: ALLE Termine abrufen (Muss NACH den POST-Logiken kommen) ---
try {
    $sql_select_all = "
        SELECT 
            t.termin_id, 
            t.termin_datum_zeit, 
            t.besuchsgrund, 
            t.status, 
            p.vorname AS patient_vorname, 
            p.nachname AS patient_nachname,
            a.vorname AS arzt_vorname, 
            a.nachname AS arzt_nachname, 
            abt.name AS abteilung_name
        FROM termine t
        JOIN patienten p ON t.patienten_id = p.patienten_id
        JOIN aerzte a ON t.arzt_id = a.arzt_id
        JOIN abteilungen abt ON a.abteilung_id = abt.abteilung_id
        ORDER BY t.termin_datum_zeit DESC
    ";
    $stmt = $db_verbindung->prepare($sql_select_all);
    $stmt->execute();
    $alle_termine = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $fehlermeldung = "FEHLER beim Abrufen aller Termine: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Willkommen, Admin <?php echo htmlspecialchars($_SESSION["vorname"] . " " . $_SESSION["nachname"]); ?>!</h1>
    
    <?php if ($erfolgsmeldung): ?>
        <div style="color: green; font-weight: bold;"><?php echo $erfolgsmeldung; ?></div>
    <?php elseif ($fehlermeldung): ?>
        <div style="color: red; font-weight: bold;"><?php echo $fehlermeldung; ?></div>
    <?php endif; ?>

    <h2>Übersicht aller Termine</h2>

    <?php if (empty($alle_termine)): ?>
        <p>Es sind noch keine Termine im System vorhanden.</p>
    <?php else: ?>
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Termin ID</th>
                    <th>Patient</th>
                    <th>Arzt & Abteilung</th>
                    <th>Datum & Uhrzeit</th>
                    <th>Grund</th>
                    <th>Status</th>
                    <th>Aktionen (Admin)</th> 
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alle_termine as $termin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($termin['termin_id']); ?></td>
                        <td><?php echo htmlspecialchars($termin['patient_vorname'] . " " . $termin['patient_nachname']); ?></td>
                        <td><?php echo htmlspecialchars($termin['arzt_vorname'] . " (" . $termin['abteilung_name'] . ")"); ?></td>
                        <td><?php echo htmlspecialchars($termin['termin_datum_zeit']); ?></td> 
                        <td><?php echo htmlspecialchars($termin['besuchsgrund']); ?></td>
                        
                        <td>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline;">
                                <input type="hidden" name="termin_id" value="<?php echo htmlspecialchars($termin['termin_id']); ?>">
                                <select name="neuer_status" required>
                                    <?php 
                                    $stati = ['geplant', 'bestätigt', 'abgeschlossen', 'abgesagt'];
                                    foreach ($stati as $status): ?>
                                        <option value="<?php echo $status; ?>" 
                                            <?php if ($termin['status'] == $status) echo 'selected'; ?>>
                                            <?php echo $status; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="submit" name="status_update" value="UPDATE">
                            </form>
                        </td>

                        <td>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline;" onsubmit="return confirm('Sicher? Dieser Termin wird dauerhaft gelöscht!');">
                                <input type="hidden" name="termin_id" value="<?php echo htmlspecialchars($termin['termin_id']); ?>">
                                <input type="submit" name="termin_loeschen" value="Löschen" style="background-color: red; color: white;">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="abmelden.php">Abmelden</a></p>
</body>
</html>