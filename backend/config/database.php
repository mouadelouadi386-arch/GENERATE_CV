<?php
$envPath = dirname(__DIR__, 2) . '/.env';
if (!file_exists($envPath)) {
    die("Erreur : Le fichier .env est introuvable au chemin : " . $envPath);
}
$env = parse_ini_file($envPath);
$host = $env['DB_HOST'] ?? 'localhost';
$dbname = $env['DB_NAME'] ?? 'cv_generator';
$username = $env['DB_USER'] ?? 'root';
$password = $env['DB_PASS'] ?? '';
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>