<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

send_security_headers();
require_login();

$filter_form_type = $_GET['form_type'] ?? '';
$filter_holiday = $_GET['holiday'] ?? '';
$filter_location = $_GET['location'] ?? '';

$where = ['1=1'];
$params = [];

if ($filter_form_type) {
    $where[] = 'form_type = ?';
    $params[] = $filter_form_type;
}
if ($filter_holiday) {
    $where[] = 'holiday = ?';
    $params[] = $filter_holiday;
}
if ($filter_location) {
    $where[] = 'location = ?';
    $params[] = $filter_location;
}

$where_clause = implode(' AND ', $where);

// Base queries for dashboard
$stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE $where_clause");
$stmt->execute($params);
$total_submissions = $stmt->fetchColumn();

// Fetch submissions for table
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE $where_clause ORDER BY created_at DESC LIMIT 100");
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// Fetch chart data if needed
$chart_data = [];
if ($total_submissions > 0) {
    if ($filter_form_type === 'parent' || empty($filter_form_type)) {
        // Aggregate parent data for overall satisfaction (q4_overall) as an example chart
        $stmt_parent = $pdo->prepare("
            SELECT p.q4_overall as answer, COUNT(*) as count
            FROM submissions s
            JOIN parent_answers p ON s.id = p.submission_id
            WHERE s.form_type = 'parent' AND $where_clause
            GROUP BY p.q4_overall
        ");
        $stmt_parent->execute($params);
        $chart_data['parent_overall'] = $stmt_parent->fetchAll();
    }

    if ($filter_form_type === 'child' || empty($filter_form_type)) {
        // Aggregate child data for "wiederkommen" (q5_return)
        $stmt_child = $pdo->prepare("
            SELECT c.q5_return as answer, COUNT(*) as count
            FROM submissions s
            JOIN child_answers c ON s.id = c.submission_id
            WHERE s.form_type = 'child' AND $where_clause
            GROUP BY c.q5_return
        ");
        $stmt_child->execute($params);
        $chart_data['child_return'] = $stmt_child->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Feedback</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #333;
            color: white;
            padding: 15px 20px;
        }
        .admin-nav a { color: #fff; text-decoration: none; margin-left: 20px; }
        .dashboard-content { padding: 20px; }
        .filters { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .filters form { display: flex; gap: 15px; align-items: flex-end; }
        .filters select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
        .stats { font-size: 20px; margin-bottom: 20px; }
        .actions { margin-top: 20px; display: flex; gap: 10px; }
        .btn-export { background: #28a745; }
        .btn-reset { background: #d9534f; }
        .links-area {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .links-area h3 { margin-top: 0; }
        .copy-link {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .copy-link input { flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .copy-link button { padding: 8px 15px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="admin-nav">
        <h2>Feedback Admin Panel</h2>
        <div>
            <span>Willkommen, <?= e($_SESSION['username'] ?? 'Admin') ?>!</span>
            <a href="<?= e(getBasePath()) ?>admin/logout.php">Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="filters">
            <h3>Filter</h3>
            <form method="GET" action="">
                <div>
                    <label>Formular:</label>
                    <select name="form_type">
                        <option value="">Alle</option>
                        <option value="parent" <?= $filter_form_type === 'parent' ? 'selected' : '' ?>>Eltern</option>
                        <option value="child" <?= $filter_form_type === 'child' ? 'selected' : '' ?>>Kinder</option>
                    </select>
                </div>
                <div>
                    <label>Ferien:</label>
                    <select name="holiday">
                        <option value="">Alle</option>
                        <option value="Ostern" <?= $filter_holiday === 'Ostern' ? 'selected' : '' ?>>Ostern</option>
                        <option value="Pfingsten" <?= $filter_holiday === 'Pfingsten' ? 'selected' : '' ?>>Pfingsten</option>
                        <option value="Sommer1" <?= $filter_holiday === 'Sommer1' ? 'selected' : '' ?>>Sommer1</option>
                        <option value="Sommer2" <?= $filter_holiday === 'Sommer2' ? 'selected' : '' ?>>Sommer2</option>
                        <option value="Sommer3" <?= $filter_holiday === 'Sommer3' ? 'selected' : '' ?>>Sommer3</option>
                        <option value="Sommer6" <?= $filter_holiday === 'Sommer6' ? 'selected' : '' ?>>Sommer6</option>
                        <option value="Herbst" <?= $filter_holiday === 'Herbst' ? 'selected' : '' ?>>Herbst</option>
                    </select>
                </div>
                <div>
                    <label>Standort:</label>
                    <select name="location">
                        <option value="">Alle</option>
                        <option value="OAS" <?= $filter_location === 'OAS' ? 'selected' : '' ?>>OAS</option>
                        <option value="LZ" <?= $filter_location === 'LZ' ? 'selected' : '' ?>>LZ</option>
                        <option value="RO" <?= $filter_location === 'RO' ? 'selected' : '' ?>>RO</option>
                        <option value="WHD" <?= $filter_location === 'WHD' ? 'selected' : '' ?>>WHD</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn">Filtern</button>
                    <a href="dashboard.php" class="btn" style="background:#6c757d; text-decoration:none;">Reset</a>
                </div>
            </form>
        </div>

        <div class="stats">
            Gesamte Einsendungen für diese Auswahl: <strong><?= $total_submissions ?></strong>
        </div>

        <div class="actions">
            <form method="GET" action="export.php" target="_blank">
                <input type="hidden" name="form_type" value="<?= e($filter_form_type) ?>">
                <input type="hidden" name="holiday" value="<?= e($filter_holiday) ?>">
                <input type="hidden" name="location" value="<?= e($filter_location) ?>">
                <button type="submit" class="btn btn-export">Als Excel/CSV exportieren</button>
            </form>
            <a href="reset.php" class="btn btn-reset">Daten zurücksetzen / löschen</a>
        </div>

        <div class="links-area">
            <h3>Öffentliche Links</h3>
            <?php
            $base_url = getBaseUrl();
            ?>
            <div class="copy-link">
                <strong>Eltern:</strong>
                <input type="text" id="link-parent" value="<?= e($base_url) ?>/public/eltern/" readonly>
                <button onclick="copyLink('link-parent')">Kopieren</button>
            </div>
            <div class="copy-link">
                <strong>Kinder:</strong>
                <input type="text" id="link-child" value="<?= e($base_url) ?>/public/kinder/" readonly>
                <button onclick="copyLink('link-child')">Kopieren</button>
            </div>
        </div>

        <h3>Letzte Einsendungen (Max. 100)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Typ</th>
                    <th>Ferien</th>
                    <th>Standort</th>
                    <th>Datum</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $sub): ?>
                <tr>
                    <td><?= $sub['id'] ?></td>
                    <td><?= $sub['form_type'] === 'parent' ? 'Eltern' : 'Kinder' ?></td>
                    <td><?= e($sub['holiday']) ?></td>
                    <td><?= e($sub['location']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($sub['created_at'])) ?></td>
                    <td>
                        <!-- Simple expanding details via JS, or text directly if preferred.
                             For simplicity, we show a button to toggle row. -->
                        <button onclick="alert('Exportieren Sie die Daten, um alle Details zu sehen, oder bauen Sie hier eine Modal-Ansicht ein.');">Details</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($submissions)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">Keine Daten gefunden.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top:40px;">
            <h3>Grafische Auswertungen</h3>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <?php if (!empty($chart_data['parent_overall'])): ?>
                <div style="width: 400px;">
                    <h4>Zufriedenheit Eltern (Gesamt)</h4>
                    <canvas id="chartParent"></canvas>
                </div>
                <?php endif; ?>

                <?php if (!empty($chart_data['child_return'])): ?>
                <div style="width: 400px;">
                    <h4>Wiederkommen Kinder (1=gut, 5=schlecht)</h4>
                    <canvas id="chartChild"></canvas>
                </div>
                <?php endif; ?>

                <?php if (empty($chart_data['parent_overall']) && empty($chart_data['child_return'])): ?>
                <p>Nicht genügend Daten für eine grafische Auswertung vorhanden.</p>
                <?php endif; ?>
            </div>
            <p style="margin-top: 20px; font-size: 0.9em; color: #666;">Detaillierte Auswertungen aller Fragen finden Sie im Excel-Export.</p>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        <?php if (!empty($chart_data['parent_overall'])): ?>
        const parentData = <?= json_encode($chart_data['parent_overall']) ?>;
        const ctxParent = document.getElementById('chartParent').getContext('2d');
        new Chart(ctxParent, {
            type: 'pie',
            data: {
                labels: parentData.map(d => d.answer),
                datasets: [{
                    data: parentData.map(d => d.count),
                    backgroundColor: ['#4CAF50', '#8BC34A', '#FFEB3B', '#FF9800', '#F44336', '#9E9E9E']
                }]
            }
        });
        <?php endif; ?>

        <?php if (!empty($chart_data['child_return'])): ?>
        const childData = <?= json_encode($chart_data['child_return']) ?>;
        const ctxChild = document.getElementById('chartChild').getContext('2d');
        new Chart(ctxChild, {
            type: 'bar',
            data: {
                labels: childData.map(d => 'Note ' + d.answer),
                datasets: [{
                    label: 'Anzahl',
                    data: childData.map(d => d.count),
                    backgroundColor: '#03A9F4'
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
        <?php endif; ?>

        function copyLink(id) {
            var copyText = document.getElementById(id);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Link kopiert: " + copyText.value);
        }
    </script>
</body>
</html>