<?php
require_once "config.php";

$host = DB_HOST;
$db   = DB_NAME;
$user = DB_USER;
$pass = DB_PASSWORD;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>