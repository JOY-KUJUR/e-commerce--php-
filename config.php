<?php
// config.php
$host = 'localhost';
$dbname = 'BookStore';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "connection done";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
