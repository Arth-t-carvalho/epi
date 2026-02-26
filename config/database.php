<?php
// config/database.php

$host = "localhost";
$db   = "epi_guard";
$user = "root";
$port = "3308";
$pass = ""; // Coloque sua senha aqui se houver

try {
    // Removi o espaço antes do 'port=' para evitar problemas de leitura do PDO
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    // CORREÇÃO: Removido o quinto parâmetro ($port)
    $pdo = new PDO($dsn, $user, $pass, $options);
    
} catch (PDOException $e) {
    // Em produção, não mostre a mensagem detalhada do erro para o usuário
    error_log($e->getMessage());
    die("Erro interno de conexão com o banco de dados.");
}
?>