<?php
// DE: Startet eine neue Session oder setzt eine vorhandene fort
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>