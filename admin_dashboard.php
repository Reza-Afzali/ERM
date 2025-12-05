<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 30px;
        }

        h1, h2 {
            color: #333;
        }

        /* SUCCESS + ERROR MESSAGES */
        .success {
            background: #e6ffed;
            border-left: 4px solid #28a745;
            padding: 12px 15px;
            color: #155724;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .error {
            background: #ffe6e6;
            border-left: 4px solid #d93025;
            padding: 12px 15px;
            color: #a30000;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        /* TABLE DESIGN */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        th {
            background: #fafafa;
            padding: 14px;
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f5f5f5;
        }

        /* FORM ELEMENTS */
        select {
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            outline: none;
            transition: .2s;
        }

        select:focus {
            border-color: #888;
        }

        /* BUTTONS */
        .btn-update {
            padding: 8px 14px;
            background: #1a73e8;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: 0.25s;
        }

        .btn-update:hover {
            background: #1558b0;
        }

        .btn-delete {
            padding: 8px 14px;
            background: #d93025;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: 0.25s;
        }

        .btn-delete:hover {
            background: #b3251c;
        }

        .logout-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #555;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .logout-link:hover {
            background: #333;
        }
    </style>
</head>

<body>

<h1>Willkommen, Admin <?php echo htmlspecialchars($_SESSION["vorname"] . " " . $_SESSION["nachname"]); ?>!</h1>

<?php if ($erfolgsmeldung): ?>
    <div class="success"><?php echo $erfolgsmeldung; ?></div>
<?php elseif ($fehlermeldung): ?>
    <div class="error"><?php echo $fehlermeldung; ?></div>
<?php endif; ?>

<h2>Übersicht aller Termine</h2>

<?php if (empty($alle_termine)): ?>
    <p>Es sind noch keine Termine im System vorhanden.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Termin ID</th>
                <th>Patient</th>
                <th>Arzt & Abteilung</th>
                <th>Datum & Uhrzeit</th>
                <th>Grund</th>
                <th>Status ändern</th>
                <th>Aktion</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($alle_termine as $termin): ?>
                <tr>
                    <td><?php echo htmlspecialchars($termin['termin_id']); ?></td>
                    <td><?php echo htmlspecialchars($termin['patient_vorname'] . " " . $termin['patient_nachname']); ?></td>

                    <td><?php echo htmlspecialchars($termin['arzt_vorname'] . " (" . $termin['abteilung_name'] . ")"); ?></td>

                    <td><?php echo htmlspecialchars($termin['termin_datum_zeit']); ?></td>

                    <td><?php echo htmlspecialchars($termin['besuchsgrund']); ?></td>

                    <td>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <input type="hidden" name="termin_id" value="<?php echo $termin['termin_id']; ?>">

                            <select name="neuer_status" required>
                                <?php 
                                $stati = ['geplant', 'bestätigt', 'abgeschlossen', 'abgesagt'];
                                foreach ($stati as $status): ?>
                                    <option value="<?php echo $status; ?>" 
                                        <?php if ($termin['status'] == $status) echo 'selected'; ?>>
                                        <?php echo ucfirst($status); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit" name="status_update" class="btn-update">Update</button>
                        </form>
                    </td>

                    <td>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
                              onsubmit="return confirm('Sicher? Dieser Termin wird dauerhaft gelöscht!');">
                              
                            <input type="hidden" name="termin_id" value="<?php echo $termin['termin_id']; ?>">

                            <button type="submit" name="termin_loeschen" class="btn-delete">Löschen</button>
                        </form>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
<?php endif; ?>

<a href="abmelden.php" class="logout-link">Abmelden</a>

</body>
</html>
