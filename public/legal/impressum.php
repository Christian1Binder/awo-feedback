<?php
// public/legal/impressum.php
require_once __DIR__ . '/../../includes/helpers.php';
send_security_headers();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impressum</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="../../index.html">
                <img src="../../assets/img/logo.png" alt="AWO Bezirksjugendwerk Logo" class="header-logo">
            </a>
        </div>
    </header>

    <main>
    <div class="container legal-container">
        <h1>Impressum</h1>
        <p><strong>Angaben gemäß § 5 TMG</strong></p>
        <p>
            Musterorganisation AWO<br>
            Musterstraße 1<br>
            12345 Musterstadt
        </p>

        <p><strong>Vertreten durch:</strong><br>
        Max Mustermann</p>

        <p><strong>Kontakt:</strong><br>
        Telefon: +49 (0) 123 44 55 66<br>
        E-Mail: info@muster-awo.de</p>

        <p>Dies ist ein Platzhalter-Impressum. Bitte passen Sie diese Seite mit den realen Daten der Organisation an.</p>

        <div style="margin-top: 30px;">
             <a href="../../index.html" class="btn">Zurück zur Startseite</a>
        </div>
    </div>
    </main>

    <footer>
        <nav class="footer-nav">
            <a href="../../index.html">STARTSEITE</a>
            <span class="footer-divider">|</span>
            <a href="../../admin/index.php">LOGIN</a>
            <span class="footer-divider">|</span>
            <a href="impressum.php">IMPRESSUM</a>
            <span class="footer-divider">|</span>
            <a href="datenschutz.php">DATENSCHUTZ</a>
        </nav>
    </footer>
</body>
</html>