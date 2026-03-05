<?php
// public/danke/index.php
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vielen Dank!</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .thank-you-box {
            text-align: center;
            padding: 50px 20px;
            background: #fff;
            border-radius: 8px;
            margin-top: 20px;
        }
        .thank-you-box h1 {
            color: var(--primary);
            margin-bottom: 20px;
        }
        .thank-you-box p {
            font-size: 18px;
            color: #555;
            margin-bottom: 30px;
        }
    </style>
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
    <div class="container">
        <div class="thank-you-box">
            <h1>Vielen Dank für Ihr Feedback!</h1>
            <p>Ihre Antworten wurden erfolgreich übermittelt und helfen uns, unsere Ferienbetreuung weiter zu verbessern.</p>
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
            <a href="../legal/impressum.php">IMPRESSUM</a>
            <span class="footer-divider">|</span>
            <a href="../legal/datenschutz.php">DATENSCHUTZ</a>
        </nav>
    </footer>
</body>
</html>