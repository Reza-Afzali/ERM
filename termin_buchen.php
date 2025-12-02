<?php
// DE: 1. Session- und Datenbankkonfiguration einbinden
require_once 'session_config.php';
require_once 'config.php';

// DE: Prüfen, ob der Benutzer eingeloggt und ein Patient ist
if (!isset($_SESSION["eingeloggt"]) || $_SESSION["eingeloggt"] !== true || $_SESSION["benutzer_typ"] !== "patient") {
    header("location: anmeldung.php"); 
    exit;
}

$erfolgsmeldung = $fehlermeldung = "";
$termin_fehler = $arzt_fehler = $datum_fehler = $zeit_fehler = $grund_fehler = "";
$abteilungen = []; 
$patienten_id = $_SESSION["patienten_id"]; 

// --- 2. SELECT: Hole alle Abteilungen ---
try {
    $sql_abt = "SELECT abteilung_id, name FROM abteilungen ORDER BY name";
    $statement = $db_verbindung->prepare($sql_abt);
    $statement->execute();
    $abteilungen = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $fehlermeldung = "FEHLER: Abteilungen konnten nicht geladen werden.";
}

// --- 3. POST-Anfrage verarbeiten (INSERT-Logik) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $abteilung_id = filter_input(INPUT_POST, 'abteilung', FILTER_VALIDATE_INT);
    $arzt_id = filter_input(INPUT_POST, 'arzt', FILTER_VALIDATE_INT);
    $termin_datum = trim($_POST['datum']);
    $termin_zeit = trim($_POST['zeit']);
    $besuchsgrund = trim($_POST['besuchsgrund']); // Geändert zu 'besuchsgrund'

    // Validierung
    if (empty($arzt_id)) { $arzt_fehler = "Bitte einen Arzt auswählen."; }
    if (empty($termin_datum)) { $datum_fehler = "Datum ist erforderlich."; }
    if (empty($termin_zeit)) { $zeit_fehler = "Uhrzeit ist erforderlich."; }
    if (empty($besuchsgrund)) { $grund_fehler = "Bitte geben Sie den Grund für die Beschwerde an."; }

    if (empty($arzt_fehler) && empty($datum_fehler) && empty($zeit_fehler) && empty($grund_fehler)) {
        
        // DE: Wichtig! Datum und Uhrzeit zusammenführen, da die Datenbank nur EINEN Termin-Zeit-Feld hat.
        $termin_datum_zeit = $termin_datum . " " . $termin_zeit . ":00"; 
        
        try {
            // SQL-Befehl INSERT INTO termine mit den korrigierten Spaltennamen
            $sql_insert = "INSERT INTO termine (patienten_id, arzt_id, termin_datum_zeit, besuchsgrund, status) VALUES (:pid, :aid, :dt, :grund, 'geplant')";
            
            $stmt = $db_verbindung->prepare($sql_insert);
            
            $stmt->bindParam(':pid', $patienten_id, PDO::PARAM_INT);
            $stmt->bindParam(':aid', $arzt_id, PDO::PARAM_INT);
            $stmt->bindParam(':dt', $termin_datum_zeit, PDO::PARAM_STR);
            $stmt->bindParam(':grund', $besuchsgrund, PDO::PARAM_STR); // Geändert
            
            $stmt->execute();
            
            $erfolgsmeldung = "Ihr Termin wurde erfolgreich gespeichert.";
            $termin_datum = $termin_zeit = $besuchsgrund = "";

        } catch (PDOException $e) {
            $fehlermeldung = "FEHLER beim Speichern des Termins: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Termin Buchen</title>
</head>
<body>
    <h1>Terminbuchung für <?php echo htmlspecialchars($_SESSION["vorname"] . " " . $_SESSION["nachname"]); ?></h1>
    
    <?php if ($erfolgsmeldung): ?>
        <div style="color: green; font-weight: bold;"><?php echo $erfolgsmeldung; ?></div>
    <?php elseif ($fehlermeldung): ?>
        <div style="color: red; font-weight: bold;"><?php echo $fehlermeldung; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        
        <fieldset>
            <legend>Ihre Daten</legend>
            <p>E-Mail: <strong><?php echo htmlspecialchars($_SESSION["email"] ?? "Nicht verfügbar"); ?></strong></p>
        </fieldset>
        <hr>

        <label for="abteilung">Gewünschte Abteilung:</label><br>
        <select name="abteilung" id="abteilung" required onchange="ladeAerzte(this.value)">
            <option value="">-- Abteilung wählen --</option>
            <?php 
            foreach ($abteilungen as $abt) {
                echo '<option value="' . htmlspecialchars($abt['abteilung_id']) . '">' . htmlspecialchars($abt['name']) . '</option>';
            }
            ?>
        </select>
        <br><br>

        <label for="arzt">Arzt wählen:</label><br>
        <select name="arzt" id="arzt" required>
            <option value="">-- Zuerst Abteilung wählen --</option>
        </select>
        <span style="color: red;"><?php echo $arzt_fehler; ?></span>
        <br><br>

        <label for="datum">Datum:</label>
        <input type="date" name="datum" id="datum" required>
        <span style="color: red;"><?php echo $datum_fehler; ?></span>
        
        <label for="zeit">Uhrzeit (HH:MM):</label>
        <input type="time" name="zeit" id="zeit" required>
        <span style="color: red;"><?php echo $zeit_fehler; ?></span>
        <br><br>

        <label for="besuchsgrund">Grund der Beschwerde (Anliegen):</label><br>
        <textarea name="besuchsgrund" id="besuchsgrund" rows="4" cols="50" required><?php echo htmlspecialchars($besuchsgrund ?? ''); ?></textarea>
        <span style="color: red;"><?php echo $grund_fehler; ?></span>
        <br><br>

        <input type="submit" value="Termin jetzt buchen">
    </form>
    
    <p><a href="patienten_dashboard.php">Zurück zum Dashboard</a></p>

    <script>
        function ladeAerzte(abteilung_id) {
            const arztDropdown = document.getElementById('arzt');
            arztDropdown.innerHTML = '<option value="">-- Zuerst Abteilung wählen --</option>' +
                                     '<option value="1">Dr. Max Mustermann</option>' + 
                                     '<option value="2">Dr. Anna Schmidt</option>'; // Platzhalter
        }
    </script>
</body>
</html>