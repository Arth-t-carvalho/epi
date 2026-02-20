<?php
// ARQUIVO: php/check_notificacoes.php
require_once __DIR__ . '/../config/database.php';
session_start();

$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$cursoId = 1; // Mantendo o curso padrão do seu dashboard

header('Content-Type: application/json');

// Se for a primeira vez, pega o ID mais alto para não mostrar alertas velhos
if ($last_id === 0) {
    $stmt = $pdo->query("SELECT MAX(id) FROM ocorrencias");
    $max_id = (int) $stmt->fetchColumn();
    
    echo json_encode(['status' => 'init', 'last_id' => $max_id ?: 0]);
    exit;
}

// Busca ocorrências NOVAS. 
// O CASE traduz o ID: 1 = oculos, 2 = capacete.
$query = "SELECT o.id, a.nome AS aluno, 
                 CASE o.epi_id 
                    WHEN 1 THEN 'oculos' 
                    WHEN 2 THEN 'capacete' 
                    ELSE 'epi não identificado' 
                 END AS epi_nome
          FROM ocorrencias o 
          JOIN alunos a ON a.id = o.aluno_id 
          WHERE a.curso_id = ? AND o.id > ? 
          ORDER BY o.id ASC";

$stmt = $pdo->prepare($query);
$stmt->execute([$cursoId, $last_id]);
$novasOcorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'status' => 'success',
    'dados' => $novasOcorrencias
]);