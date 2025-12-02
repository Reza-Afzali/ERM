<?php
// DE: Session und Datenbankkonfiguration einbinden
require_once 'session_config.php';
require_once 'config.php';

// DE: Wenn der Benutzer bereits eingeloggt ist, zur passenden Seite umleiten
if (isset($_SESSION["eingeloggt"]) && $_SESSION["eingeloggt"] === true) {
    if ($_SESSION["benutzer_typ"] === "patient") {
        header("location: patienten_dashboard.php");
        exit;
    } elseif ($_SESSION["benutzer_typ"] === "admin") {
        header("location: admin_dashboard.php");
        exit;
    }
}

$email_fehler = $passwort_fehler = $login_fehler = "";
$email = "";

// DE: Verarbeitung der Formulardaten
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validierung der Eingaben
    if (empty(trim($_POST["email"]))) {
        $email_fehler = "Bitte E-Mail eingeben.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["passwort"]))) {
        $passwort_fehler = "Bitte Passwort eingeben.";
    } else {
        $passwort = trim($_POST["passwort"]);
    }

    // 2. Datenbankprüfung (SELECT)
    if (empty($email_fehler) && empty($passwort_fehler)) {
        
        // Versuche zuerst als Patient
        if (versuche_login($db_verbindung, $email, $passwort, "patienten", "patienten_id", "patient")) {
            // Erfolgreich als Patient eingeloggt
            header("location: patienten_dashboard.php");
            exit;
        } 
        
        // Wenn nicht Patient, versuche als Admin (Arzt)
        else if (versuche_login($db_verbindung, $email, $passwort, "aerzte", "arzt_id", "admin")) {
            // Erfolgreich als Admin/Arzt eingeloggt
            header("location: admin_dashboard.php");
            exit;
        } 
        
        // Login fehlgeschlagen
        else {
            $login_fehler = "Ungültige E-Mail oder Passwort.";
        }
    }
}

// Hilfsfunktion zum Prüfen des Logins in einer bestimmten Tabelle
function versuche_login($db_verbindung, $email, $passwort, $tabelle, $id_feld, $typ) {
    
    // DE: SQL SELECT Statement
    $sql = "SELECT {$id_feld}, vorname, nachname, passwort_hash FROM {$tabelle} WHERE email = :email";

    if ($stmt = $db_verbindung->prepare($sql)) {
        
        $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
        $param_email = $email;
        
        if ($stmt->execute()) {
            // Prüfen, ob der Benutzer existiert
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    $hashed_passwort = $row["passwort_hash"];
                    
                    // Passwort verifizieren
                    if (password_verify($passwort, $hashed_passwort)) {
                        // Anmeldung erfolgreich! Setze Session-Variablen
                        $_SESSION["eingeloggt"] = true;
                        $_SESSION["{$id_feld}"] = $row["{$id_feld}"]; // patienten_id oder arzt_id
                        $_SESSION["benutzer_typ"] = $typ; // patient oder admin
                        $_SESSION["vorname"] = $row["vorname"];
                        $_SESSION["nachname"] = $row["nachname"];
                        return true;
                    }
                }
            }
        }
        unset($stmt);
    }
    return false;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Anmeldung - Patientenportal</title>
</head>
<body>
    <h2>Anmeldung im Portal</h2>
    <p>Bitte melden Sie sich mit Ihrer E-Mail und Ihrem Passwort an.</p>

    <?php 
        // Anzeige des allgemeinen Login-Fehlers
        if (!empty($login_fehler)) {
            echo '<div style="color: red; font-weight: bold;">' . $login_fehler . '</div>';
        }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        
        <div class="form-gruppe">
            <label>E-Mail</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
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
            <input type="submit" value="Anmelden">
        </div>
        <p>Noch kein Konto? <a href="registrierung.php">Jetzt registrieren</a>.</p>
    </form>
</body>
</html>