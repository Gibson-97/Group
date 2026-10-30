<?php
// config.php

$host = "localhost";
$dbname = "nce_clinic";
$username = "root";      // Change if you use a different MySQL user
$password = "";          // Change if you set a password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>