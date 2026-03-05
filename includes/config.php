<?php
// includes/config.sample.php

// Copy this file to config.php and enter your database details.
// For security on IONOS, place this file outside the public_html folder if possible,
// or protect the includes folder with .htaccess

return [
    'db' => [
        'host' => 'localhost', // e.g., dbxxx.hosting-data.io
        'name' => 'your_db_name',
        'user' => 'your_db_user',
        'pass' => 'your_db_password',
        'charset' => 'utf8mb4'
    ],
    'admin' => [
        'username' => 'admin',
        // Default password is 'admin123'. Change it!
        // Generate a new hash with: echo password_hash('YourSecretPassword', PASSWORD_DEFAULT);
        'password_hash' => '$2y$10$wE4S5j5w/aO4B8G4K0qKGu0X/L5/S.X3O9J.v7K89j.vQ.1y3u.1e'
    ]
];
