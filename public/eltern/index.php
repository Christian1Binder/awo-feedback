<?php
// public/eltern/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Disable csrf.php auto-check and handle it manually
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

$errors = [];

if (isPost()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF validation failed.');
    }

    $holiday = getPost('holiday');
    $location = getPost('location');
    $consent = isset($_POST['consent']) ? 1 : 0;

    if (!$holiday || !$location || !$consent) {
        $errors[] = 'Bitte füllen Sie alle Pflichtfelder aus und stimmen Sie der Datenschutzerklärung zu.';
    }

    $q1_source = getPost('q1_source');
    $q1_other = getPost('q1_other');
    if ($q1_source === 'Andere Quelle' && empty($q1_other)) {
        $errors[] = 'Bitte geben Sie die andere Quelle an.';
    }

    $required_fields = ['q2_prior', 'q3_children_count', 'q4_overall', 'q5_safe', 'q6_website', 'q7_registration', 'q8_organization', 'q9_cozy', 'q10_accessibility', 'q11_food', 'q12_program', 'q13_trip', 'q14_contact', 'q15_staff', 'q16_recommend'];

    foreach ($required_fields as $field) {
        if (!getPost($field)) {
            $errors[] = 'Bitte beantworten Sie alle Single-Choice-Fragen.';
            break;
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO submissions (form_type, holiday, location, consent) VALUES ('parent', ?, ?, ?)");
            $stmt->execute([$holiday, $location, $consent]);
            $submission_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO parent_answers (
                submission_id, q1_source, q1_other, q2_prior, q3_children_count, q4_overall, q5_safe, q6_website, q7_registration, q8_organization, q9_cozy, q10_accessibility, q11_food, q12_program, q13_trip, q14_contact, q15_staff, q16_recommend, q17_disliked, q18_liked, q19_suggestions
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $submission_id,
                $q1_source,
                $q1_other,
                getPost('q2_prior'),
                getPost('q3_children_count'),
                getPost('q4_overall'),
                getPost('q5_safe'),
                getPost('q6_website'),
                getPost('q7_registration'),
                getPost('q8_organization'),
                getPost('q9_cozy'),
                getPost('q10_accessibility'),
                getPost('q11_food'),
                getPost('q12_program'),
                getPost('q13_trip'),
                getPost('q14_contact'),
                getPost('q15_staff'),
                getPost('q16_recommend'),
                getPost('q17_disliked'),
                getPost('q18_liked'),
                getPost('q19_suggestions')
            ]);

            $pdo->commit();
            redirect(getBasePath() . 'public/danke/index.php');
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Fehler beim Speichern der Daten. Bitte versuchen Sie es später erneut.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eltern-Feedback - Ferienbetreuung</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Eltern-Feedback</h1>
            <p>Ihre Meinung ist uns wichtig!</p>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">

            <div class="form-group mandatory-header">
                <label>Welche Ferienbetreuung haben Sie besucht? *</label>
                <select name="holiday" required>
                    <option value="">Bitte wählen...</option>
                    <option value="Ostern">Ostern</option>
                    <option value="Pfingsten">Pfingsten</option>
                    <option value="Sommer1">Sommer (Woche 1)</option>
                    <option value="Sommer2">Sommer (Woche 2)</option>
                    <option value="Sommer3">Sommer (Woche 3)</option>
                    <option value="Sommer6">Sommer (Woche 6)</option>
                    <option value="Herbst">Herbst</option>
                </select>

                <label>Welchen Standort haben Sie besucht? *</label>
                <select name="location" required>
                    <option value="">Bitte wählen...</option>
                    <option value="OAS">Oberasbach (OAS)</option>
                    <option value="LZ">Langenzenn (LZ)</option>
                    <option value="RO">Roßtal (RO)</option>
                    <option value="WHD">Wilhermsdorf (WHD)</option>
                </select>
            </div>

            <fieldset>
                <legend>1. Wie haben Sie von unserer Ferienbetreuung erfahren? *</legend>
                <label><input type="radio" name="q1_source" value="Empfehlung von Bekannten/Freunden" required> Empfehlung von Bekannten/Freunden</label><br>
                <label><input type="radio" name="q1_source" value="Internetrecherche"> Internetrecherche</label><br>
                <label><input type="radio" name="q1_source" value="Soziale Medien (Facebook, Instagram, etc.)"> Soziale Medien (Facebook, Instagram, etc.)</label><br>
                <label><input type="radio" name="q1_source" value="Flyer/Werbung"> Flyer/Werbung</label><br>
                <label><input type="radio" name="q1_source" value="Andere Quelle" id="q1_source_other"> Andere Quelle</label><br>
                <input type="text" name="q1_other" id="q1_other_input" placeholder="Bitte angeben..." style="display:none;">
            </fieldset>

            <fieldset>
                <legend>2. War(en) Ihr(e) Kind(er) schon einmal in unserer Ferienbetreuung? *</legend>
                <label><input type="radio" name="q2_prior" value="Ja, schon mehrmals" required> Ja, schon mehrmals</label><br>
                <label><input type="radio" name="q2_prior" value="Ja, einmalig"> Ja, einmalig</label><br>
                <label><input type="radio" name="q2_prior" value="Nein, zum ersten Mal dabei"> Nein, zum ersten Mal dabei</label>
            </fieldset>

            <fieldset>
                <legend>3. Wie viele Kinder haben Sie in unserer Ferienbetreuung angemeldet? *</legend>
                <label><input type="radio" name="q3_children_count" value="1 Kind" required> 1 Kind</label><br>
                <label><input type="radio" name="q3_children_count" value="2 Kinder"> 2 Kinder</label><br>
                <label><input type="radio" name="q3_children_count" value="3 Kinder oder mehr"> 3 Kinder oder mehr</label>
            </fieldset>

            <fieldset>
                <legend>4. Wie zufrieden waren Sie insgesamt mit unserer Ferienbetreuung? *</legend>
                <label><input type="radio" name="q4_overall" value="Sehr zufrieden" required> Sehr zufrieden</label><br>
                <label><input type="radio" name="q4_overall" value="Zufrieden"> Zufrieden</label><br>
                <label><input type="radio" name="q4_overall" value="Neutral"> Neutral</label><br>
                <label><input type="radio" name="q4_overall" value="Unzufrieden"> Unzufrieden</label><br>
                <label><input type="radio" name="q4_overall" value="Sehr unzufrieden"> Sehr unzufrieden</label>
            </fieldset>

            <fieldset>
                <legend>5. Hat sich Ihr(e) Kind(er) während der Betreuungszeit wohl und sicher gefühlt? *</legend>
                <label><input type="radio" name="q5_safe" value="Ja, sehr wohl und sicher" required> Ja, sehr wohl und sicher</label><br>
                <label><input type="radio" name="q5_safe" value="Meistens wohl und sicher"> Meistens wohl und sicher</label><br>
                <label><input type="radio" name="q5_safe" value="Neutral"> Neutral</label><br>
                <label><input type="radio" name="q5_safe" value="Nicht immer wohl und sicher"> Nicht immer wohl und sicher</label><br>
                <label><input type="radio" name="q5_safe" value="Nein, überhaupt nicht wohl und sicher"> Nein, überhaupt nicht wohl und sicher</label>
            </fieldset>

            <fieldset>
                <legend>6. Wie finden Sie unsere Website? *</legend>
                <label><input type="radio" name="q6_website" value="Sehr ansprechend und informativ" required> Sehr ansprechend und informativ</label><br>
                <label><input type="radio" name="q6_website" value="In Ordnung, aber Verbesserung möglich"> In Ordnung, aber Verbesserung möglich</label><br>
                <label><input type="radio" name="q6_website" value="Schlecht, unübersichtlich oder veraltet"> Schlecht, unübersichtlich oder veraltet</label><br>
                <label><input type="radio" name="q6_website" value="Ich habe die Website nicht besucht"> Ich habe die Website nicht besucht</label>
            </fieldset>

            <fieldset>
                <legend>7. Finden Sie den Anmeldungsprozess bequem und verständlich? *</legend>
                <label><input type="radio" name="q7_registration" value="Ja, sehr bequem und verständlich" required> Ja, sehr bequem und verständlich</label><br>
                <label><input type="radio" name="q7_registration" value="Akzeptabel, aber Verbesserung möglich"> Akzeptabel, aber Verbesserung möglich</label><br>
                <label><input type="radio" name="q7_registration" value="Unbequem und verwirrend"> Unbequem und verwirrend</label><br>
                <label><input type="radio" name="q7_registration" value="Ich habe mein Kind nicht registriert, jemand anderes hat das erledigt"> Ich habe mein Kind nicht registriert, jemand anderes hat das erledigt</label>
            </fieldset>

            <fieldset>
                <legend>8. Bitte bewerten Sie die Organisation und Kommunikation vor Beginn der Ferienbetreuung. *</legend>
                <label><input type="radio" name="q8_organization" value="Sehr gut organisiert und kommuniziert" required> Sehr gut organisiert und kommuniziert</label><br>
                <label><input type="radio" name="q8_organization" value="Meistens gut organisiert und kommuniziert"> Meistens gut organisiert und kommuniziert</label><br>
                <label><input type="radio" name="q8_organization" value="Durchschnittlich, Verbesserung möglich"> Durchschnittlich, Verbesserung möglich</label><br>
                <label><input type="radio" name="q8_organization" value="Schlecht organisiert/kommuniziert"> Schlecht organisiert/kommuniziert</label>
            </fieldset>

            <fieldset>
                <legend>9. Wie würden Sie die Ferienbetreuungseinrichtung bewerten? Ist es Ihrer Meinung nach gemütlich? *</legend>
                <label><input type="radio" name="q9_cozy" value="Sehr gemütlich und ansprechend gestaltet" required> Sehr gemütlich und ansprechend gestaltet</label><br>
                <label><input type="radio" name="q9_cozy" value="Eher gemütlich und ansprechend gestaltet"> Eher gemütlich und ansprechend gestaltet</label><br>
                <label><input type="radio" name="q9_cozy" value="Neutral oder keine besondere Meinung"> Neutral oder keine besondere Meinung</label><br>
                <label><input type="radio" name="q9_cozy" value="Nicht besonders gemütlich und ansprechend gestaltet"> Nicht besonders gemütlich und ansprechend gestaltet</label>
            </fieldset>

            <fieldset>
                <legend>10. Wie fanden Sie die Erreichbarkeit der Ferienbetreuungseinrichtung? *</legend>
                <label><input type="radio" name="q10_accessibility" value="Sehr bequem und gut erreichbar" required> Sehr bequem und gut erreichbar</label><br>
                <label><input type="radio" name="q10_accessibility" value="Meistens bequem und gut erreichbar"> Meistens bequem und gut erreichbar</label><br>
                <label><input type="radio" name="q10_accessibility" value="Durchschnittlich, Verbesserung möglich"> Durchschnittlich, Verbesserung möglich</label><br>
                <label><input type="radio" name="q10_accessibility" value="Unbequem und schwer erreichbar"> Unbequem und schwer erreichbar</label>
            </fieldset>

            <fieldset>
                <legend>11. Ist Ihr Kind mit dem Essen in unserer Ferienbetreuung zufrieden? *</legend>
                <label><input type="radio" name="q11_food" value="Ja, sehr zufrieden" required> Ja, sehr zufrieden</label><br>
                <label><input type="radio" name="q11_food" value="Eher zufrieden"> Eher zufrieden</label><br>
                <label><input type="radio" name="q11_food" value="Neutral oder keine besondere Meinung"> Neutral oder keine besondere Meinung</label><br>
                <label><input type="radio" name="q11_food" value="Nicht besonders zufrieden"> Nicht besonders zufrieden</label><br>
                <label><input type="radio" name="q11_food" value="Überhaupt nicht zufrieden"> Überhaupt nicht zufrieden</label>
            </fieldset>

            <fieldset>
                <legend>12. Wie bewerten Sie unser Programm? *</legend>
                <label><input type="radio" name="q12_program" value="Sehr abwechslungsreich und ansprechend" required> Sehr abwechslungsreich und ansprechend</label><br>
                <label><input type="radio" name="q12_program" value="Eher abwechslungsreich und ansprechend"> Eher abwechslungsreich und ansprechend</label><br>
                <label><input type="radio" name="q12_program" value="Durchschnittlich, Verbesserung möglich"> Durchschnittlich, Verbesserung möglich</label><br>
                <label><input type="radio" name="q12_program" value="Nicht besonders ansprechend oder verbesserungswürdig"> Nicht besonders ansprechend oder verbesserungswürdig</label>
            </fieldset>

            <fieldset>
                <legend>13. Wie bewerten Sie unseren Ausflug? *</legend>
                <label><input type="radio" name="q13_trip" value="Sehr sinnvoll und interessant" required> Sehr sinnvoll und interessant</label><br>
                <label><input type="radio" name="q13_trip" value="Eher sinnvoll und interessant"> Eher sinnvoll und interessant</label><br>
                <label><input type="radio" name="q13_trip" value="Neutral oder keine besondere Meinung"> Neutral oder keine besondere Meinung</label><br>
                <label><input type="radio" name="q13_trip" value="Nicht besonders sinnvoll oder uninteressant"> Nicht besonders sinnvoll oder uninteressant</label>
            </fieldset>

            <fieldset>
                <legend>14. Hatten Sie während der Ferienbetreuung ausreichende Möglichkeiten, mit unseren Betreuer*innen in Kontakt zu treten? *</legend>
                <label><input type="radio" name="q14_contact" value="Ja, ausreichend Möglichkeiten" required> Ja, ausreichend Möglichkeiten</label><br>
                <label><input type="radio" name="q14_contact" value="Eher ja als nein"> Eher ja als nein</label><br>
                <label><input type="radio" name="q14_contact" value="Unentschieden oder keine klare Meinung"> Unentschieden oder keine klare Meinung</label><br>
                <label><input type="radio" name="q14_contact" value="Eher nein als ja"> Eher nein als ja</label><br>
                <label><input type="radio" name="q14_contact" value="Nein, überhaupt nicht ausreichend"> Nein, überhaupt nicht ausreichend</label>
            </fieldset>

            <fieldset>
                <legend>15. Wie empfanden Sie die Freundlichkeit und Professionalität unserer Betreuer*innen? *</legend>
                <label><input type="radio" name="q15_staff" value="Sehr freundlich und professionell" required> Sehr freundlich und professionell</label><br>
                <label><input type="radio" name="q15_staff" value="Eher freundlich und professionell"> Eher freundlich und professionell</label><br>
                <label><input type="radio" name="q15_staff" value="Durchschnittlich, Verbesserung möglich"> Durchschnittlich, Verbesserung möglich</label><br>
                <label><input type="radio" name="q15_staff" value="Unfreundlich oder unprofessionell"> Unfreundlich oder unprofessionell</label>
            </fieldset>

            <fieldset>
                <legend>16. Würden Sie unsere Ferienbetreuung weiterempfehlen? *</legend>
                <label><input type="radio" name="q16_recommend" value="Ja, auf jeden Fall" required> Ja, auf jeden Fall</label><br>
                <label><input type="radio" name="q16_recommend" value="Eher ja"> Eher ja</label><br>
                <label><input type="radio" name="q16_recommend" value="Unentschieden oder keine klare Meinung"> Unentschieden oder keine klare Meinung</label><br>
                <label><input type="radio" name="q16_recommend" value="Eher nein"> Eher nein</label><br>
                <label><input type="radio" name="q16_recommend" value="Nein, definitiv nicht"> Nein, definitiv nicht</label>
            </fieldset>

            <fieldset>
                <legend>17. Gibt es etwas, das Ihnen absolut nicht gefallen hat?</legend>
                <textarea name="q17_disliked" rows="4"></textarea>
            </fieldset>

            <fieldset>
                <legend>18. Was hat Ihnen am besten gefallen?</legend>
                <textarea name="q18_liked" rows="4"></textarea>
            </fieldset>

            <fieldset>
                <legend>19. Haben Sie Verbesserungsvorschläge oder Anregungen für unsere Ferienbetreuung?</legend>
                <textarea name="q19_suggestions" rows="4"></textarea>
            </fieldset>

            <div class="form-group consent">
                <label>
                    <input type="checkbox" name="consent" required>
                    Ich stimme der Datenschutzerklärung zu und willige ein, dass meine Daten anonymisiert zur Auswertung gespeichert werden.
                </label>
                <p><a href="<?= e(getBasePath()) ?>public/legal/datenschutz.php" target="_blank">Datenschutzhinweis lesen</a></p>
            </div>

            <button type="submit" class="btn">Feedback senden</button>
        </form>
    </div>
    <script src="../../assets/js/form.js"></script>
</body>
</html>