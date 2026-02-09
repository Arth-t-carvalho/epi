<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

try {
    // CAMINHOS (ajuste se necessário, mas o __DIR__ costuma resolver)
    if (file_exists(__DIR__ . '/auth.php')) {
        require_once __DIR__ . '/auth.php';
        require_once __DIR__ . '/database.php';
    } else {
        throw new Exception("Arquivos de sistema não encontrados.");
    }

    // BUSCAR ALUNOS
    // Pegamos id, nome e curso_id
    $sql = "SELECT id, nome, curso_id FROM alunos ORDER BY nome ASC";
    $stmt = $pdo->query($sql);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [];

    foreach ($alunos as $aluno) {
        $id = $aluno['id'];

        // 1. CONTA O TOTAL DE OCORRÊNCIAS (Para definir: Regular, Alerta ou Reincidente)
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM ocorrencias WHERE aluno_id = ?");
        $stmtCount->execute([$id]);
        $totalOcorrencias = $stmtCount->fetchColumn();

        // 2. VERIFICA SE FALTOU EPI HOJE (Para mostrar no modal)
        $stmtEpi = $pdo->prepare("
            SELECT e.nome FROM ocorrencias o 
            JOIN epis e ON e.id = o.epi_id 
            WHERE o.aluno_id = ? AND DATE(o.data_hora) = CURDATE()
        ");
        $stmtEpi->execute([$id]);
        $episFaltantesHoje = $stmtEpi->fetchAll(PDO::FETCH_COLUMN);

        $resultado[] = [
            'id'      => $aluno['id'],
            'name'    => $aluno['nome'],
            'course'  => "Curso " . $aluno['curso_id'], 
            'missing' => $episFaltantesHoje,     // Lista de EPIs que faltou hoje
            'history_count' => $totalOcorrencias // Número total de erros (0, 1, 2...)
        ];
    }

    echo json_encode($resultado);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>