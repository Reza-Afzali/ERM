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

// KORREKTUR: Defensive Zuweisung der Patienten-ID, um Fehler zu vermeiden (Behebt Warning: Undefined array key "benutzer_id")
$patienten_id = $_SESSION["patienten_id"] ?? $_SESSION["benutzer_id"] ?? null; 
// DE: Stellt sicher, dass die ID vorhanden ist
if (is_null($patienten_id)) {
    header("location: anmeldung.php"); 
    exit;
}

$abteilungen = []; 

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
    
    // DE: Die Eingaben werden von den neuen Feldern Stunde/Minute/Tag übernommen
    $arzt_id = filter_input(INPUT_POST, 'arzt', FILTER_VALIDATE_INT);
    $termin_datum = trim($_POST['datum']);
    $stunde = filter_input(INPUT_POST, 'stunde', FILTER_VALIDATE_INT); 
    $minute = filter_input(INPUT_POST, 'minute', FILTER_VALIDATE_INT); 
    $besuchsgrund = trim($_POST['besuchsgrund']);

    // DE: Validierung (mit Prüfung auf Stunde/Minute)
    if (empty($arzt_id)) { $arzt_fehler = "Bitte einen Arzt auswählen."; }
    if (empty($termin_datum)) { $datum_fehler = "Datum ist erforderlich."; }
    if (!is_numeric($stunde) || !is_numeric($minute)) { $zeit_fehler = "Uhrzeit ist erforderlich."; }
    if (empty($besuchsgrund)) { $grund_fehler = "Bitte geben Sie den Grund für die Beschwerde an."; }

    if (empty($arzt_fehler) && empty($datum_fehler) && empty($zeit_fehler) && empty($grund_fehler)) {
        
        // DE: Wichtig! Datum und Uhrzeit zusammenführen in korrektem Format YYYY-MM-DD HH:MM:SS
        $zeit_teil = str_pad($stunde, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minute, 2, '0', STR_PAD_LEFT) . ":00";
        $termin_datum_zeit = $termin_datum . " " . $zeit_teil;
        
        // DE: Zusätzliche Prüfung: Termin in der Vergangenheit?
        if (strtotime($termin_datum_zeit) < time()) {
             $fehlermeldung = "Sie können keinen Termin in der Vergangenheit buchen.";
        } else {
             try {
                // SQL-Befehl INSERT INTO termine
                $sql_insert = "INSERT INTO termine (patienten_id, arzt_id, termin_datum_zeit, besuchsgrund, status) VALUES (:pid, :aid, :dt, :grund, 'geplant')";
                
                $stmt = $db_verbindung->prepare($sql_insert);
                
                $stmt->bindParam(':pid', $patienten_id, PDO::PARAM_INT);
                $stmt->bindParam(':aid', $arzt_id, PDO::PARAM_INT);
                $stmt->bindParam(':dt', $termin_datum_zeit, PDO::PARAM_STR);
                $stmt->bindParam(':grund', $besuchsgrund, PDO::PARAM_STR); 
                
                $stmt->execute();
                
                $erfolgsmeldung = "Ihr Termin wurde erfolgreich am " . date('d.m.Y H:i', strtotime($termin_datum_zeit)) . " gespeichert.";
                
                // DE: Felder nach Erfolg leeren
                $termin_datum = $stunde = $minute = $besuchsgrund = "";

            } catch (PDOException $e) {
                $fehlermeldung = "FEHLER beim Speichern des Termins: " . $e->getMessage();
            }
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
        <select name="abteilung_id" id="abteilung" required onchange="ladeAerzte(this.value)"> 
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

        <label for="datum">Datum (ab heute):</label>
        <input type="date" name="datum" id="datum" required onchange="pruefeWochenende(this.value)" min="<?php echo date('Y-m-d'); ?>">
        <span style="color: red;"><?php echo $datum_fehler; ?></span>
        <br>
        
        <label for="stunde">Uhrzeit (Stunde):</label>
        <select id="stunde" name="stunde" required>
            <option value="">-- Stunde --</option>
            <?php 
            // DE: Zeitfenster von 8 Uhr bis 17 Uhr
            for ($h = 8; $h < 18; $h++): ?>
                <option value="<?php echo $h; ?>"><?php echo str_pad($h, 2, '0', STR_PAD_LEFT); ?></option>
            <?php endfor; ?>
        </select>
        
        <label for="minute">Uhrzeit (Minute):</label>
        <select id="minute" name="minute" required>
            <option value="">-- Minute --</option>
            <?php 
            // KORREKTUR: 5-Minuten-Schritte
            for ($m = 0; $m < 60; $m += 5): ?>
                <option value="<?php echo $m; ?>"><?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?></option>
            <?php endfor; ?>
        </select>
        <span style="color: red;"><?php echo $zeit_fehler; ?></span>
        <br><br>

        <label for="besuchsgrund">Grund des Besuchs (Anliegen):</label><br>
        <textarea name="besuchsgrund" id="besuchsgrund" rows="4" cols="50" required><?php echo htmlspecialchars($besuchsgrund ?? ''); ?></textarea>
        <span style="color: red;"><?php echo $grund_fehler; ?></span>
        <br><br>

        <input type="submit" value="Termin jetzt buchen">
    </form>
    
    <p><a href="patienten_dashboard.php">Zurück zum Dashboard</a></p>

    <script>
        // Funktion zum Laden der Ärzte (AJAX)
        function ladeAerzte(abteilung_id) {
            const arztDropdown = document.getElementById('arzt');
            arztDropdown.innerHTML = '<option value="">-- Ärzte werden geladen... --</option>'; // Lade-Nachricht
            
            if (!abteilung_id) {
                arztDropdown.innerHTML = '<option value="">-- Zuerst Abteilung wählen --</option>';
                return;
            }

            // KORREKTUR DES PFADES: Relativer Pfad
            var url = 'fetch_doctors.php?abteilung_id=' + abteilung_id;
            
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var doctors = JSON.parse(xhr.responseText);
                        
                        arztDropdown.innerHTML = '<option value="">-- Arzt auswählen --</option>'; // Liste leeren

                        if (doctors.length > 0) {
                            doctors.forEach(doctor => {
                                var option = document.createElement('option');
                                option.value = doctor.arzt_id;
                                // Namen des Arztes zusammenstellen
                                option.textContent = doctor.titel + ' ' + doctor.vorname + ' ' + doctor.nachname;
                                arztDropdown.appendChild(option);
                            });
                        } else {
                            arztDropdown.innerHTML = '<option value="">-- Keine Ärzte in dieser Abteilung gefunden --</option>';
                        }
                    } catch (e) {
                        console.error("Fehler beim Parsen der JSON-Antwort:", e);
                        console.error("Server-Antwort:", xhr.responseText);
                        arztDropdown.innerHTML = '<option value="">-- FEHLER beim Laden der Ärzte --</option>';
                    }
                } else {
                    console.error('AJAX-Fehler. Status:', xhr.status);
                    arztDropdown.innerHTML = '<option value="">-- FEHLER: Verbindung zum Server fehlgeschlagen --</option>';
                }
            };
            xhr.onerror = function() {
                console.error('Netzwerkfehler beim Abrufen der Ärzte.');
                arztDropdown.innerHTML = '<option value="">-- Netzwerkfehler --</option>';
            };

            xhr.send();
        }

        // NEU: Funktion zur Prüfung auf Wochenende (Samstag=6, Sonntag=0)
        function pruefeWochenende(datumString) {
            var inputDatum = new Date(datumString);
            var tagDerWoche = inputDatum.getDay(); // 0 = Sonntag, 6 = Samstag

            if (tagDerWoche === 0 || tagDerWoche === 6) {
                alert("Terminbuchungen sind am Wochenende (Samstag und Sonntag) nicht möglich. Bitte wählen Sie ein Datum von Montag bis Freitag.");
                
                // DE: Eingabefeld leeren, um die Buchung zu blockieren
                document.getElementById('datum').value = ''; 
            }
        }
    </script>
</body>
</html>