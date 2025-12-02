<?php
require_once 'session_config.php';
require_once 'config.php';

if (!isset($_SESSION["eingeloggt"]) || $_SESSION["eingeloggt"] !== true || $_SESSION["benutzer_typ"] !== "patient") {
    header("location: anmeldung.php");
    exit;
}

$patienten_id = $_SESSION["patienten_id"];
$erfolgsmeldung = $fehlermeldung = "";

// --- 1. DELETE-Logik: Termin löschen ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['termin_loeschen'])) {
    $termin_id_zu_loeschen = filter_input(INPUT_POST, 'termin_id', FILTER_VALIDATE_INT);

    if ($termin_id_zu_loeschen) {
        try {
            $sql_delete = "DELETE FROM termine WHERE termin_id = :tid AND patienten_id = :pid";
            $stmt = $db_verbindung->prepare($sql_delete);
            $stmt->bindParam(':tid', $termin_id_zu_loeschen, PDO::PARAM_INT);
            $stmt->bindParam(':pid', $patienten_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $erfolgsmeldung = "Ihr Termin wurde erfolgreich storniert (gelöscht).";
            } else {
                $fehlermeldung = "FEHLER: Der Termin konnte nicht gelöscht werden.";
            }
        } catch (PDOException $e) {
            $fehlermeldung = "FEHLER beim Löschen des Termins: " . $e->getMessage();
        }
    }
}

// --- 2. SELECT-Logik: Termine des Patienten abrufen ---
$termine = [];
try {
    // KORREKTUR: Verwende termin_datum_zeit und besuchsgrund
    $sql_select = "
        SELECT 
            t.termin_id, 
            t.termin_datum_zeit, 
            t.besuchsgrund, 
            t.status, 
            a.vorname AS arzt_vorname, 
            a.nachname AS arzt_nachname, 
            abt.name AS abteilung_name
        FROM termine t
        JOIN aerzte a ON t.arzt_id = a.arzt_id
        JOIN abteilungen abt ON a.abteilung_id = abt.abteilung_id
        WHERE t.patienten_id = :pid
        ORDER BY t.termin_datum_zeit
    ";
    $stmt = $db_verbindung->prepare($sql_select);
    $stmt->bindParam(':pid', $patienten_id, PDO::PARAM_INT);
    $stmt->execute();
    $termine = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $fehlermeldung = "FEHLER beim Abrufen Ihrer Termine: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Patienten Dashboard</title>
</head>
<body>
    <h1>Willkommen, Patient <?php echo htmlspecialchars($_SESSION["vorname"] . " " . $_SESSION["nachname"]); ?>!</h1>
    
    <?php if ($erfolgsmeldung): ?>
        <div style="color: green; font-weight: bold;"><?php echo $erfolgsmeldung; ?></div>
    <?php elseif ($fehlermeldung): ?>
        <div style="color: red; font-weight: bold;"><?php echo $fehlermeldung; ?></div>
    <?php endif; ?>

    <h2>Ihre Aktuellen Termine</h2>
    <p><a href="termin_buchen.php">Neuen Termin buchen</a></p>

    <?php if (empty($termine)): ?>
        <p>Sie haben noch keine Termine gebucht.</p>
    <?php else: ?>
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Datum & Uhrzeit</th>
                    <th>Arzt</th>
                    <th>Abteilung</th>
                    <th>Grund der Beschwerde</th>
                    <th>Status</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($termine as $termin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($termin['termin_datum_zeit']); ?></td>
                        <td><?php echo htmlspecialchars($termin['arzt_vorname'] . " " . $termin['arzt_nachname']); ?></td>
                        <td><?php echo htmlspecialchars($termin['abteilung_name']); ?></td>
                        <td><?php echo htmlspecialchars($termin['besuchsgrund']); ?></td>
                        <td><?php echo htmlspecialchars($termin['status']); ?></td>
                        <td>
                            <?php if ($termin['status'] == 'geplant'): ?>
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirm('Sind Sie sicher?');">
                                    <input type="hidden" name="termin_id" value="<?php echo htmlspecialchars($termin['termin_id']); ?>">
                                    <input type="submit" name="termin_loeschen" value="Stornieren" style="background-color: red; color: white;">
                                </form>
                            <?php else: ?>
                                Nicht änderbar
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="abmelden.php">Abmelden</a></p>
</body>
</html>