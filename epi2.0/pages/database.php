<?php
$host = "localhost";
$db   = "epi_guard";
$user = "root";
$pass = "";
$port = "3308";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass,);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão");
}
