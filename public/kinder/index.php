<?php
// public/kinder/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

send_security_headers();

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
    // Honeypot check
    if (!empty($_POST['contact_me_by_fax_only'])) {
        // Silently discard spam
        redirect(getBasePath() . 'public/danke/index.php');
    }

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF validation failed.');
    }

    $holiday = getPost('holiday');
    $location = getPost('location');
    $consent = isset($_POST['consent']) ? 1 : 0;

    if (!$holiday || !$location || !$consent) {
        $errors[] = 'Bitte wähle Ferien, Standort und stimme zu!';
    }

    $required_fields = ['q1_crafts', 'q2_food', 'q3_staff', 'q4_trip', 'q5_return'];
    foreach ($required_fields as $field) {
        $val = getPost($field);
        if (!$val || $val < 1 || $val > 5) {
            $errors[] = 'Bitte bewerte alle Fragen mit den Smileys!';
            break;
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO submissions (form_type, holiday, location, consent) VALUES ('child', ?, ?, ?)");
            $stmt->execute([$holiday, $location, $consent]);
            $submission_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO child_answers (
                submission_id, q1_crafts, q2_food, q3_staff, q4_trip, q5_return, q6_disliked, q7_liked
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $submission_id,
                getPost('q1_crafts'),
                getPost('q2_food'),
                getPost('q3_staff'),
                getPost('q4_trip'),
                getPost('q5_return'),
                getPost('q6_disliked'),
                getPost('q7_liked')
            ]);

            $pdo->commit();
            redirect(getBasePath() . 'public/danke/index.php');
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Es gab einen Fehler. Bitte versuche es später nochmal.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinder-Feedback</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="kids-theme">
    <header style="background: #ffffff; border-bottom: 3px solid #fbc02d;">
        <div class="header-container">
            <a href="../../index.html">
                <img src="../../assets/img/logo.png" alt="AWO Bezirksjugendwerk Logo" class="header-logo">
            </a>
        </div>
    </header>

    <main>
    <div class="container">
        <div class="form-header" style="text-align: center; margin-bottom: 30px;">
            <h1>Hallo! Wie war's?</h1>
            <p style="font-size: 1.2em; color: #e65100;">Deine Meinung ist uns wichtig!</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">

            <!-- Honeypot -->
            <div style="display:none;" aria-hidden="true">
                <label for="contact_me_by_fax_only">Do not fill this out if you are human:</label>
                <input type="text" name="contact_me_by_fax_only" id="contact_me_by_fax_only" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-group mandatory-header">
                <label>Welche Ferienbetreuung hast du besucht? *</label>
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

                <label>An welchem Ort warst du? *</label>
                <select name="location" required>
                    <option value="">Bitte wählen...</option>
                    <option value="OAS">Oberasbach (OAS)</option>
                    <option value="LZ">Langenzenn (LZ)</option>
                    <option value="RO">Roßtal (RO)</option>
                    <option value="WHD">Wilhermsdorf (WHD)</option>
                </select>
            </div>

            <!-- Fragen mit Smileys -->
            <?php
            $questions = [
                'q1_crafts' => '1. Wie haben Dir die Bastelangebote gefallen?',
                'q2_food' => '2. Hat Dir das Essen geschmeckt?',
                'q3_staff' => '3. Mochtest Du die Betreuerinnen?',
                'q4_trip' => '4. Hat Dir der Ausflug gefallen?',
                'q5_return' => '5. Würdest Du noch mal in die Ferienbetreuung kommen?'
            ];
            foreach ($questions as $name => $text):
            ?>
            <fieldset class="smiley-rating">
                <legend><?= e($text) ?> *</legend>
                <div class="smileys">
                    <label class="smiley"><input type="radio" name="<?= e($name) ?>" value="1" required> <span class="icon excellent">😁</span></label>
                    <label class="smiley"><input type="radio" name="<?= e($name) ?>" value="2"> <span class="icon good">🙂</span></label>
                    <label class="smiley"><input type="radio" name="<?= e($name) ?>" value="3"> <span class="icon neutral">😐</span></label>
                    <label class="smiley"><input type="radio" name="<?= e($name) ?>" value="4"> <span class="icon bad">🙁</span></label>
                    <label class="smiley"><input type="radio" name="<?= e($name) ?>" value="5"> <span class="icon terrible">😠</span></label>
                </div>
            </fieldset>
            <?php endforeach; ?>

            <fieldset>
                <legend>6. Was hat Dir gar nicht gefallen?</legend>
                <textarea name="q6_disliked" rows="3"></textarea>
            </fieldset>

            <fieldset>
                <legend>7. Was hat Dir am besten gefallen?</legend>
                <textarea name="q7_liked" rows="3"></textarea>
            </fieldset>

            <div class="form-group consent">
                <label>
                    <input type="checkbox" name="consent" required>
                    Ich bin einverstanden, dass meine Antworten für die Auswertung genutzt werden (anonym).
                </label>
                <p><a href="<?= e(getBasePath()) ?>public/legal/datenschutz.php" target="_blank">Datenschutzhinweis</a></p>
            </div>

            <button type="submit" class="btn btn-kids">Fertig & Abschicken!</button>
        </form>
    </div>
    </main>

    <footer style="background-color: #fbc02d;">
        <nav class="footer-nav">
            <a href="../../index.html" style="color: #e65100;">STARTSEITE</a>
            <span class="footer-divider" style="color: #e65100;">|</span>
            <a href="../../admin/index.php" style="color: #e65100;">LOGIN</a>
            <span class="footer-divider" style="color: #e65100;">|</span>
            <a href="../legal/impressum.php" style="color: #e65100;">IMPRESSUM</a>
            <span class="footer-divider" style="color: #e65100;">|</span>
            <a href="../legal/datenschutz.php" style="color: #e65100;">DATENSCHUTZ</a>
        </nav>
    </footer>

    <script src="../../assets/js/form.js"></script>
</body>
</html>