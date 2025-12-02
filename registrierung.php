<?php
// DE: Konfigurationsdatei einbinden, um Zugriff auf $db_verbindung zu haben
require_once 'config.php';

// DE: Variablen für Fehler und Erfolgsmeldungen initialisieren
$vorname_fehler = $nachname_fehler = $email_fehler = $passwort_fehler = "";
$erfolgs_meldung = $fehler_meldung = "";

// DE: Überprüfen, ob das Formular gesendet wurde (POST-Methode)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Datenvalidierung (Überprüfung der Eingaben)
    
    // DE: Vorname prüfen
    if (empty(trim($_POST["vorname"]))) {
        $vorname_fehler = "Bitte geben Sie Ihren Vornamen ein.";
    } else {
        $vorname = trim($_POST["vorname"]);
    }

    // DE: Nachname prüfen
    if (empty(trim($_POST["nachname"]))) {
        $nachname_fehler = "Bitte geben Sie Ihren Nachnamen ein.";
    } else {
        $nachname = trim($_POST["nachname"]);
    }
    
    // DE: E-Mail-Prüfung und Datenbank-Check (Vermeidung von Duplikaten)
    if (empty(trim($_POST["email"]))) {
        $email_fehler = "Bitte geben Sie eine E-Mail-Adresse ein.";
    } else {
        $email = trim($_POST["email"]);
        
        // DE: SQL-Prüfung: Ist die E-Mail bereits registriert?
        $sql = "SELECT patienten_id FROM patienten WHERE email = :email";
        
        if ($stmt = $db_verbindung->prepare($sql)) {
            // DE: Parameter binden (Schutz vor SQL-Injection)
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $param_email = $email;
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $email_fehler = "Diese E-Mail-Adresse ist bereits registriert.";
                }
            } else {
                $fehler_meldung = "Ups! Etwas ist schief gelaufen. Bitte versuchen Sie es später erneut.";
            }
            unset($stmt);
        }
    }

    // DE: Passwort-Prüfung
    if (empty(trim($_POST["passwort"]))) {
        $passwort_fehler = "Bitte geben Sie ein Passwort ein.";
    } elseif (strlen(trim($_POST["passwort"])) < 6) {
        $passwort_fehler = "Das Passwort muss mindestens 6 Zeichen lang sein.";
    } else {
        $passwort = trim($_POST["passwort"]);
    }
    
    // 2. Registrierung durchführen, wenn keine Fehler vorliegen
    if (empty($vorname_fehler) && empty($nachname_fehler) && empty($email_fehler) && empty($passwort_fehler)) {
        
        // DE: SQL-INSERT-Statement
        $sql = "INSERT INTO patienten (vorname, nachname, email, passwort_hash) VALUES (:vorname, :nachname, :email, :passwort_hash)";
         
        if ($stmt = $db_verbindung->prepare($sql)) {
            // DE: Parameter binden
            $stmt->bindParam(":vorname", $param_vorname, PDO::PARAM_STR);
            $stmt->bindParam(":nachname", $param_nachname, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":passwort_hash", $param_passwort_hash, PDO::PARAM_STR);
            
            // DE: Variablen zuweisen
            $param_vorname = $vorname;
            $param_nachname = $nachname;
            $param_email = $email;
            // DE: Passwort hashen (speichern der verschlüsselten Version)
            $param_passwort_hash = password_hash($passwort, PASSWORD_DEFAULT);
            
            // DE: Statement ausführen
            if ($stmt->execute()) {
                $erfolgs_meldung = "Ihr Konto wurde erfolgreich erstellt! Sie können sich jetzt anmelden.";
                // DE: Umleitung zur Anmeldeseite könnte hier erfolgen
                // header("location: anmeldung.php"); 
            } else {
                $fehler_meldung = "Fehler beim Erstellen des Kontos. Bitte erneut versuchen.";
            }
            unset($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Patienten-Registrierung</title>
</head>
<body>
    <h2>Registrierung im Patientenportal</h2>
    <p>Bitte füllen Sie das Formular aus, um ein Konto zu erstellen.</p>

    <?php 
        // DE: Anzeige der globalen Erfolgs- oder Fehlermeldung
        if (!empty($erfolgs_meldung)) {
            echo '<div style="color: green; font-weight: bold;">' . $erfolgs_meldung . '</div>';
        } elseif (!empty($fehler_meldung)) {
             echo '<div style="color: red; font-weight: bold;">' . $fehler_meldung . '</div>';
        }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        
        <div class="form-gruppe">
            <label>Vorname</label>
            <input type="text" name="vorname" value="<?php echo htmlspecialchars($vorname ?? ''); ?>">
            <span style="color: red;"><?php echo $vorname_fehler; ?></span>
        </div>
        <br>
        
        <div class="form-gruppe">
            <label>Nachname</label>
            <input type="text" name="nachname" value="<?php echo htmlspecialchars($nachname ?? ''); ?>">
            <span style="color: red;"><?php echo $nachname_fehler; ?></span>
        </div>
        <br>

        <div class="form-gruppe">
            <label>E-Mail</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
            <span style="color: red;"><?php echo $email_fehler; ?></span>
        </div>
        <br>

        <div class="form-gruppe">
            <label>Passwort</label>
            <input type="password" name="passwort">
            <span style="color: red;"><?php echo $passwort_fehler; ?></span>
        </div>
        <br>
        
        <div class="form-gruppe">
            <input type="submit" value="Registrieren">
        </div>
        <p>Sie haben bereits ein Konto? <a href="anmeldung.php">Jetzt anmelden</a>.</p>
    </form>
</body>
</html>