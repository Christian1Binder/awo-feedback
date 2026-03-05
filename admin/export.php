<?php
// admin/export.php
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
    $where[] = 's.form_type = ?';
    $params[] = $filter_form_type;
}
if ($filter_holiday) {
    $where[] = 's.holiday = ?';
    $params[] = $filter_holiday;
}
if ($filter_location) {
    $where[] = 's.location = ?';
    $params[] = $filter_location;
}

$where_clause = implode(' AND ', $where);

// Base file name
$filename = "Feedback_Export_" . date('Ymd_His');

// Determine if we are outputting a CSV or Excel
// For simplicity and since we don't have composer guaranteed on IONOS without ssh,
// we will output a robust CSV file that Excel can read properly (UTF-8 with BOM, semicolon separated).
// If a real XLSX is mandatory, the user needs to run `composer require phpoffice/phpspreadsheet` locally and upload the vendor folder.

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
header('Cache-Control: max-age=0');

// Output UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');
$delimiter = ';';

if ($filter_form_type === 'parent' || empty($filter_form_type)) {
    // Parent headers
    fputcsv($output, ['--- ELTERN-FEEDBACK ---'], $delimiter);
    fputcsv($output, [
        'ID', 'Datum', 'Ferien', 'Standort',
        'Q1: Quelle', 'Q1: Andere Quelle',
        'Q2: Zuvor dabei',
        'Q3: Anzahl Kinder',
        'Q4: Gesamtzufriedenheit',
        'Q5: Wohl/Sicher',
        'Q6: Website',
        'Q7: Anmeldung',
        'Q8: Orga/Kommunikation',
        'Q9: Gemütlichkeit',
        'Q10: Erreichbarkeit',
        'Q11: Essen',
        'Q12: Programm',
        'Q13: Ausflug',
        'Q14: Kontaktmöglichkeiten',
        'Q15: Personal',
        'Q16: Weiterempfehlung',
        'Q17: Nicht gefallen',
        'Q18: Am besten gefallen',
        'Q19: Vorschläge'
    ], $delimiter);

    $sql = "SELECT s.id, s.created_at, s.holiday, s.location, p.*
            FROM submissions s
            JOIN parent_answers p ON s.id = p.submission_id
            WHERE s.form_type = 'parent' AND $where_clause
            ORDER BY s.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['created_at'],
            $row['holiday'],
            $row['location'],
            $row['q1_source'],
            $row['q1_other'],
            $row['q2_prior'],
            $row['q3_children_count'],
            $row['q4_overall'],
            $row['q5_safe'],
            $row['q6_website'],
            $row['q7_registration'],
            $row['q8_organization'],
            $row['q9_cozy'],
            $row['q10_accessibility'],
            $row['q11_food'],
            $row['q12_program'],
            $row['q13_trip'],
            $row['q14_contact'],
            $row['q15_staff'],
            $row['q16_recommend'],
            $row['q17_disliked'],
            $row['q18_liked'],
            $row['q19_suggestions']
        ], $delimiter);
    }
}

if ($filter_form_type === 'child' || empty($filter_form_type)) {
    if (empty($filter_form_type)) {
        fputcsv($output, [], $delimiter); // empty row separator
    }
    // Child headers
    fputcsv($output, ['--- KINDER-FEEDBACK ---'], $delimiter);
    fputcsv($output, [
        'ID', 'Datum', 'Ferien', 'Standort',
        'Q1: Basteln (1-5)',
        'Q2: Essen (1-5)',
        'Q3: Betreuer (1-5)',
        'Q4: Ausflug (1-5)',
        'Q5: Wiederkommen (1-5)',
        'Q6: Nicht gefallen',
        'Q7: Am besten gefallen'
    ], $delimiter);

    $sql = "SELECT s.id, s.created_at, s.holiday, s.location, c.*
            FROM submissions s
            JOIN child_answers c ON s.id = c.submission_id
            WHERE s.form_type = 'child' AND $where_clause
            ORDER BY s.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['created_at'],
            $row['holiday'],
            $row['location'],
            $row['q1_crafts'],
            $row['q2_food'],
            $row['q3_staff'],
            $row['q4_trip'],
            $row['q5_return'],
            $row['q6_disliked'],
            $row['q7_liked']
        ], $delimiter);
    }
}

fclose($output);
exit;
