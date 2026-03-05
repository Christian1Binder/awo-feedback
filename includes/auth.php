<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    // Set strict session cookie parameters before starting session
    session_set_cookie_params([
        'lifetime' => 0, // Until browser closes
        'path' => '/',
        'domain' => '', // Current domain
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Send only over HTTPS if active
        'httponly' => true, // Prevent JavaScript access to session cookie
        'samesite' => 'Strict' // Prevent CSRF attacks via cross-site requests
    ]);
    session_start();
}

$config_file = __DIR__ . '/config.php';
if (!file_exists($config_file)) {
    die('config.php nicht gefunden.');
}
$config = require $config_file;

function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_login() {
    if (!is_logged_in()) {
        require_once __DIR__ . '/helpers.php';
        header('Location: ' . getBasePath() . 'admin/index.php');
        exit;
    }
}

function login($username, $password, $config) {
    // Simple rate limiting logic using session
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }

    if ($_SESSION['login_attempts'] >= 5) {
        if (!isset($_SESSION['lockout_time'])) {
            $_SESSION['lockout_time'] = time() + (5 * 60); // 5 minutes lockout
        }
        if (time() < $_SESSION['lockout_time']) {
            return 'Zu viele Versuche. Bitte warten Sie.';
        } else {
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['lockout_time']);
        }
    }

    if ($username === $config['admin']['username'] && password_verify($password, $config['admin']['password_hash'])) {
        // Prevent session fixation by regenerating ID on privilege escalation
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_attempts'] = 0;
        return true;
    } else {
        $_SESSION['login_attempts']++;
        return 'Ungültiger Benutzername oder Passwort.';
    }
}

function logout() {
    session_destroy();
    require_once __DIR__ . '/helpers.php';
    header('Location: ' . getBasePath() . 'admin/index.php');
    exit;
}
