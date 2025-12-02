<?php
require_once 'session_config.php';

// DE: Alle Session-Variablen löschen
$_SESSION = array();

// DE: Session zerstören
session_destroy();

// DE: Zur Anmeldeseite umleiten
header("location: anmeldung.php");
exit;
?>