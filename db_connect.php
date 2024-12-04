<?php
// db_connect.php

// Wczytanie autoloadera Composer
require_once __DIR__ . '/vendor/autoload.php';

// Wczytanie zmiennych środowiskowych z pliku .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!file_exists(__DIR__ . '/.env')) {
    die("Brak pliku .env. Upewnij się, że plik został poprawnie skonfigurowany.");
}

// Pobranie danych logowania z pliku .env
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
