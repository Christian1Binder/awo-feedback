<?php
// admin/index.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_logged_in()) {
    redirect(getBasePath() . 'admin/dashboard.php');
}

$error = '';

if (isPost()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed.';
    } else {
        $username = getPost('username');
        $password = getPost('password');

        $result = login($username, $password, $config);

        if ($result === true) {
            redirect(getBasePath() . 'admin/dashboard.php');
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Feedback</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .login-container h1 { margin-bottom: 20px; text-align: center; }
        .login-container input { width: 100%; margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .login-container button { width: 100%; }
        .error-msg { color: #d9534f; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <div class="error-msg"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <?= csrf_field() ?>
            <input type="text" name="username" placeholder="Benutzername" required autofocus>
            <input type="password" name="password" placeholder="Passwort" required>
            <button type="submit" class="btn">Anmelden</button>
        </form>
    </div>
</body>
</html>