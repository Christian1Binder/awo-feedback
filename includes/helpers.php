<?php
// includes/helpers.php

// Helper functions for common tasks

/**
 * Sends global HTTP security headers
 */
function send_security_headers() {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

function e($string) {
    return htmlspecialchars((string) ($string ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function getPost($key, $default = null) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function getBaseUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];

    // Attempt to guess the base path.
    // If the script is /lernsystem/feedback/public/eltern/index.php, we want /lernsystem/feedback
    $parts = explode('/', trim($script, '/'));

    $base_parts = [];
    foreach ($parts as $part) {
        if ($part === 'public' || $part === 'admin' || $part === 'includes' || $part === 'assets') {
            break;
        }
        $base_parts[] = $part;
    }

    $path = implode('/', $base_parts);
    return $protocol . '://' . $host . ($path ? '/' . $path : '');
}

function getBasePath() {
    $script = $_SERVER['SCRIPT_NAME'];
    $parts = explode('/', trim($script, '/'));

    $base_parts = [];
    foreach ($parts as $part) {
        if ($part === 'public' || $part === 'admin' || $part === 'includes' || $part === 'assets') {
            break;
        }
        $base_parts[] = $part;
    }

    $path = implode('/', $base_parts);
    return '/' . ($path ? $path . '/' : '');
}
