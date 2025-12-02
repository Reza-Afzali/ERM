<?php
// =======================================================
// DE: Konfigurationsdatei für die Datenbankverbindung
// =======================================================

// DE: Definition der Datenbank-Zugangsdaten (Standard für XAMPP)
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORT', ''); // DE: Passwort ist standardmäßig leer bei XAMPP
define('DB_NAME', 'klinik_db'); // DE: Der Name unserer erstellten Klinik-Datenbank

/**
 * DE: Erstellt eine sichere PDO-Datenbankverbindung
 * @var PDO $db_verbindung
 */
try {
    // DE: Datenquellenname (DSN) für MySQL
    $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // DE: PDO-Objekt erstellen
    $db_verbindung = new PDO($dsn, DB_USERNAME, DB_PASSWORT);
    
    // DE: Setzen der PDO-Attribute (Fehlermodus und Fetch-Modus)
    $db_verbindung->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_verbindung->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // DE: Abfangen des Fehlers beim Verbindungsaufbau
    die("FEHLER: Die Datenbankverbindung konnte nicht hergestellt werden. Grund: " . $e->getMessage());
}

// DE: Die Variable $db_verbindung enthält nun die aktive Datenbankverbindung.
?>