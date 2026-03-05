<?php
// admin/reset.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/csrf.php';

send_security_headers();
require_login();

$msg = '';

if (isPost()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $msg = 'CSRF validation failed.';
    } else {
        $action = getPost('action');
        if ($action === 'delete') {
            try {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
                $pdo->exec('TRUNCATE TABLE child_answers;');
                $pdo->exec('TRUNCATE TABLE parent_answers;');
                $pdo->exec('TRUNCATE TABLE submissions;');
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
                $msg = 'Alle Daten wurden erfolgreich gelöscht. Das System ist für das neue Jahr zurückgesetzt.';
            } catch (\PDOException $e) {
                $msg = 'Fehler beim Löschen: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jahresabschluss / Daten löschen - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container { max-width: 600px; margin-top: 50px; }
        .warning { background: #ffeeba; border: 1px solid #ffeeba; padding: 20px; border-radius: 5px; color: #856404; }
        .danger-zone { border: 2px dashed #d9534f; padding: 20px; border-radius: 5px; margin-top: 20px; }
        .danger-zone h3 { color: #d9534f; margin-top: 0; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Jahresabschluss & Daten-Reset</h1>
        <p><a href="dashboard.php">&larr; Zurück zum Dashboard</a></p>

        <?php if ($msg): ?>
            <div class="success"><?= e($msg) ?></div>
        <?php endif; ?>

        <div class="warning">
            <strong>Wichtig:</strong> Exportieren Sie alle Daten als Excel/CSV-Datei, bevor Sie die Datenbank löschen. Ein Wiederherstellen ist nicht möglich!
            <br><br>
            <a href="export.php" target="_blank" class="btn" style="display:inline-block;">Vorher Daten exportieren (CSV)</a>
        </div>

        <div class="danger-zone">
            <h3>Gefahrenzone: Alle Daten unwiderruflich löschen</h3>
            <p>Dieser Vorgang leert alle Tabellen (submissions, parent_answers, child_answers) und bereitet das System auf das neue Jahr vor.</p>
            <form method="POST" action="" onsubmit="return confirm('SIND SIE SICHER? ALLE DATEN WERDEN GELÖSCHT! Dieser Vorgang kann nicht rückgängig gemacht werden.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn" style="background: #d9534f;">Ja, alle Daten löschen (Jahresreset)</button>
            </form>
        </div>
    </div>
</body>
</html>