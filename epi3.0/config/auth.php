<?php
session_start();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// Verifica se está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Atualiza última atividade
$_SESSION['last_activity'] = time();

// Segurança extra
session_regenerate_id(true);
