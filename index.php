<?php
// =======================================================
// DE: Hauptseite (Index) der Klinik-Webseite
// =======================================================

// DE: Datenbankverbindung einbinden
require_once 'config.php';

// DE: Array zur Speicherung der Fachbereiche
$fachbereiche_liste = [];

// 1. Daten aus der Datenbank abrufen (SELECT-Statement)
try {
    // DE: SQL-Abfrage zum Abrufen von ID und Namen aller Abteilungen aus der Tabelle 'abteilungen'
    $sql_fachbereiche = "SELECT abteilung_id, name FROM abteilungen";
    
    // DE: Abfrage vorbereiten und ausführen
    $statement = $db_verbindung->prepare($sql_fachbereiche);
    $statement->execute();
    
    // DE: Alle Ergebnisse in die Liste abrufen
    $fachbereiche_liste = $statement->fetchAll();

} catch (PDOException $e) {
    // DE: Fehler beim Abrufen der Fachbereiche behandeln
    $fehlermeldung = "FEHLER beim Laden der Fachbereiche: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Klinik Muster-Webseite</title>
    <style>
        .navigation { list-style: none; padding: 0; background: #f4f4f4; }
        .navigation li { display: inline; margin-right: 20px; }
    </style>
</head>
<body>

    <header>
        <h1>Willkommen im Facharztzentrum!</h1>
        <a href="terminbuchung.php" style="background: green; color: white; padding: 10px; text-decoration: none;">JETZT TERMIN BUCHEN</a>
    </header>

    <nav>
        <ul class="navigation">
            <li><a href="index.php">Startseite</a></li>
            
            <?php 
            // 2. Schleife über die abgerufenen Fachbereiche
            if (!empty($fachbereiche_liste)):
                // DE: Hauptmenüpunkt für die Fachbereiche
                echo '<li><a href="#">Fachbereiche</a>';
                echo '<ul class="untermenü" style="display: none; border: 1px solid #ccc; padding: 10px;">';
                
                // DE: Erstellung der Untermenüpunkte basierend auf den Datenbankdaten
                foreach ($fachbereiche_liste as $fachbereich):
                    // DE: Name und ID aus der Datenbank holen
                    $fachbereich_name = htmlspecialchars($fachbereich['name']);
                    $fachbereich_id = htmlspecialchars($fachbereich['abteilung_id']);
                    
                    // DE: Link zur Detailseite des Fachbereichs (z.B. abteilung_details.php?id=1)
                    echo '<li><a href="abteilung_details.php?id=' . $fachbereich_id . '">' . $fachbereich_name . '</a></li>';
                endforeach;
                
                echo '</ul></li>';
            else:
                // DE: Fehlermeldung anzeigen, falls keine Fachbereiche geladen werden konnten
                echo '<li><p style="color: red;">' . ($fehlermeldung ?? 'Fehler beim Laden der Navigation.') . '</p></li>';
            endif;
            ?>
            
            <li><a href="aerzteteam.php">Ärzteteam</a></li>
            <li><a href="patienteninfo.php">Patienten-Info & Service</a></li>
            <li><a href="kontakt.php">Kontakt</a></li>
        </ul>
    </nav>

    <main>
        <h2>Ihre Gesundheit ist unser Fokus.</h2>
        <p>Wir bieten Ihnen spezialisierte medizinische Versorgung in den Bereichen: Allgemeinchirurgie, Gynäkologie und Innere Medizin.</p>
    </main>

</body>
</html>