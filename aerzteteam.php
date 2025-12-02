<?php
// DE: Konfigurationsdatei einbinden
require_once 'config.php';

// DE: Array zur Speicherung der strukturierten Daten (Abteilung -> Ärzte)
$aerzte_nach_abteilung = [];

try {
    // 1. SQL-Abfrage mit JOIN ausführen
    $sql_team = "
        SELECT 
            A.vorname, A.nachname, A.spezialisierung, B.name AS abteilungs_name
        FROM 
            aerzte AS A  
        INNER JOIN 
            abteilungen AS B
        ON 
            A.abteilung_id = B.abteilung_id
        ORDER BY
            B.name, A.nachname;
    ";
    
    $statement = $db_verbindung->prepare($sql_team);
    $statement->execute();
    $ergebnisse = $statement->fetchAll();

    // 2. Datenstruktur im PHP neu organisieren
    // DE: Die Ergebnisse nach dem Abteilungsnamen gruppieren
    foreach ($ergebnisse as $arzt) {
        $abteilung_name = $arzt['abteilungs_name'];
        
        // DE: Wenn die Abteilung noch nicht im Array existiert, erstellen
        if (!isset($aerzte_nach_abteilung[$abteilung_name])) {
            $aerzte_nach_abteilung[$abteilung_name] = [];
        }
        
        // DE: Arzt zur entsprechenden Abteilung hinzufügen
        $aerzte_nach_abteilung[$abteilung_name][] = $arzt;
    }

} catch (PDOException $e) {
    $fehlermeldung = "FEHLER beim Laden des Ärzteteams: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Unser Ärzteteam</title>
</head>
<body>
    <h1>Unser Kompetentes Ärzteteam</h1>

    <?php if (!empty($aerzte_nach_abteilung)): ?>
        
        <?php 
        // 3. Ausgabe der strukturierten Daten
        // DE: Schleife über die Hauptgruppen (Abteilungen)
        foreach ($aerzte_nach_abteilung as $abteilung_name => $aerzte_liste):
        ?>
            
            <hr>
            <h2><?php echo htmlspecialchars($abteilung_name); ?></h2>
            <p>Spezialisiert auf: <?php echo htmlspecialchars($abteilung_name); ?></p>
            
            <ul>
                <?php 
                // DE: Schleife über die Ärzte innerhalb jeder Abteilung
                foreach ($aerzte_liste as $arzt): 
                ?>
                    <li>
                        <strong>Dr. <?php echo htmlspecialchars($arzt['nachname']); ?></strong>, 
                        <?php echo htmlspecialchars($arzt['vorname']); ?> (Spezialisierung: <?php echo htmlspecialchars($arzt['spezialisierung']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
            
        <?php endforeach; ?>

    <?php elseif (isset($fehlermeldung)): ?>
        <p style="color: red;"><?php echo $fehlermeldung; ?></p>
    <?php else: ?>
        <p>Aktuell sind keine Ärzte im System registriert.</p>
    <?php endif; ?>

</body>
</html>