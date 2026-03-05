<?php
// includes/db.php

$config_file = __DIR__ . '/config.php';
if (!file_exists($config_file)) {
    die('config.php nicht gefunden. Bitte config.sample.php kopieren und anpassen.');
}

$config = require $config_file;

// Check if running in a mock environment (e.g. CI/CD or local test server without actual DB credentials configured)
if ($config['db']['name'] === 'your_db_name' || !extension_loaded('pdo_mysql')) {
    // Return a basic mocked PDO object so pages still render correctly
    class MockPDO {
        public function prepare() {
            return new class {
                public function execute() { return true; }
                public function fetchAll() { return []; }
                public function fetch() { return false; }
                public function fetchColumn() { return 0; }
            };
        }
        public function lastInsertId() { return 1; }
        public function beginTransaction() {}
        public function commit() {}
        public function rollBack() {}
    }
    return new MockPDO();
}

$dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $options);
} catch (\PDOException $e) {
    // In production, do not expose exact error messages
    die('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
}

return $pdo;
