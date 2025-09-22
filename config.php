<?php
// config.php

$host = 'localhost';
$db   = 'gaatech';        // My database name
$user = 'root';           // MySQL username
$pass = '';               // Default password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // show errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
// config.php (example)
define('STRIPE_SECRET_KEY', 'sk_test_...');       // your Stripe test secret key
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');  // your Stripe test publishable key
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');     // webhook signing secret (optional but recommended)

?>
