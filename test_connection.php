<?php
// DE: Die Konfigurationsdatei einbinden.
require_once 'config.php';

// DE: Überprüfen, ob die PDO-Variable existiert und verbunden ist.
if (isset($db_verbindung)) {
    echo "<h1>ERFOLG!</h1>";
    echo "Die Verbindung zur Datenbank '" . DB_NAME . "' wurde erfolgreich hergestellt.";
} else {
    echo "<h1>FEHLER!</h1>";
    echo "Die Variable \$db_verbindung wurde nicht definiert. Verbindung fehlgeschlagen.";
}
// DE: Schließe die Verbindung (optional, da PHP dies am Ende des Skripts automatisch tut)
$db_verbindung = null; 
?>