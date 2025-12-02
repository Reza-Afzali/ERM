<?php
// DE: Konfigurationen einbinden (Verbindung zur Datenbank)
require_once 'config.php';

// DE: Setze den Header, um sicherzustellen, dass der Browser JSON erwartet
header('Content-Type: application/json');

// DE: Die ID der Abteilung aus dem GET-Parameter abrufen und validieren
$abteilung_id = filter_input(INPUT_GET, 'abteilung_id', FILTER_VALIDATE_INT);

$aerzte = [];

if ($abteilung_id) {
    try {
        // DE: SQL: Wähle alle Ärzte, die zur übergebenen Abteilungs-ID gehören
        $sql = "SELECT arzt_id, titel, vorname, nachname FROM aerzte WHERE abteilung_id = :aid ORDER BY nachname";
        $stmt = $db_verbindung->prepare($sql);
        $stmt->bindParam(':aid', $abteilung_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $aerzte = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // DE: Im Fehlerfall leeres Array zurückgeben
        http_response_code(500);
        echo json_encode(["error" => "Datenbankfehler: " . $e->getMessage()]);
        exit;
    }
}

// DE: Gib die Ergebnisse als JSON-Array aus
echo json_encode($aerzte);

// DE: WICHTIG: Kein Whitespace oder HTML nach diesem Block!
?>