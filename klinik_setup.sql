-- 1. Datenbank erstellen und auswählen
CREATE DATABASE IF NOT EXISTS klinik_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE klinik_db;

-- 2. Tabelle ABTEILUNGEN (Facharztbereiche) erstellen
CREATE TABLE abteilungen (
    abteilung_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- 3. Tabelle AERZTE (Ärzte) erstellen
CREATE TABLE aerzte (
    arzt_id INT AUTO_INCREMENT PRIMARY KEY,
    abteilung_id INT NOT NULL,
    vorname VARCHAR(50) NOT NULL,
    nachname VARCHAR(50) NOT NULL,
    titel VARCHAR(20),
    spezialisierung VARCHAR(255),
    email VARCHAR(100) NOT NULL UNIQUE,
    passwort_hash VARCHAR(255) NOT NULL,
    FOREIGN KEY (abteilung_id) REFERENCES abteilungen(abteilung_id)
);

-- 4. Tabelle PATIENTEN (Kunden) erstellen
CREATE TABLE patienten (
    patienten_id INT AUTO_INCREMENT PRIMARY KEY,
    vorname VARCHAR(50) NOT NULL,
    nachname VARCHAR(50) NOT NULL,
    geburtsdatum DATE,
    telefon VARCHAR(20),
    email VARCHAR(100) NOT NULL UNIQUE,
    passwort_hash VARCHAR(255) NOT NULL,
    registrierungsdatum DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabelle TERMINE (Termine) erstellen
CREATE TABLE termine (
    termin_id INT AUTO_INCREMENT PRIMARY KEY,
    patienten_id INT NOT NULL,
    arzt_id INT NOT NULL,
    termin_datum_zeit DATETIME NOT NULL,
    besuchsgrund TEXT,
    status ENUM('geplant', 'bestätigt', 'abgeschlossen', 'storniert') DEFAULT 'geplant',
    FOREIGN KEY (patienten_id) REFERENCES patienten(patienten_id),
    FOREIGN KEY (arzt_id) REFERENCES aerzte(arzt_id)
);


-- ********* MUSTERDATEN (TESTDATEN) *********

-- 6. Daten für ABTEILUNGEN einfügen
INSERT INTO abteilungen (name) VALUES
('Allgemeinchirurgie'),
('Gynäkologie'),
('Innere Medizin');

-- 7. Daten für AERZTE einfügen (Passwort ist Mock-Hash für 'arztpass')
INSERT INTO aerzte (abteilung_id, vorname, nachname, titel, spezialisierung, email, passwort_hash) VALUES
(1, 'Dr. Max', 'Mustermann', 'Dr. med.', 'Minimal-invasive Chirurgie', 'max.mustermann@klinik.de', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),
(2, 'Dr. Anna', 'Schmidt', 'Dr. med.', 'Schwangerschaftsvorsorge', 'anna.schmidt@klinik.de', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),
(3, 'Prof. Dr. Klaus', 'Wagner', 'Prof. Dr.', 'Kardiologie', 'klaus.wagner@klinik.de', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

-- 8. Daten für PATIENTEN einfügen (Passwort ist Mock-Hash für 'kunde1234')
INSERT INTO patienten (vorname, nachname, geburtsdatum, telefon, email, passwort_hash) VALUES
('Lukas', 'Müller', '1985-05-20', '01765554433', 'lukas.test@web.de', '$2y$10$YYY.TEST.HASH.YYYYYYYYYYYYYYYYYYYYYYYYYY');

-- 9. Muster-TERMIN einfügen
INSERT INTO termine (patienten_id, arzt_id, termin_datum_zeit, besuchsgrund, status) VALUES
(1, 1, '2025-12-05 10:30:00', 'Nachuntersuchung nach Leistenbruch', 'geplant');