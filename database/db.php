<?php
require_once __DIR__. '/../security/env.php';
loadEnv(__DIR__ . '/../.env');

function connectDB()
{
    $host = $_ENV['DB_HOST'] ?? '';
    $user = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    $database = $_ENV['DB_NAME'] ?? '';

    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        die("La connexion à la base de données a échoué : " . $conn->connect_error);
    } else {
        return $conn;
    }
}
