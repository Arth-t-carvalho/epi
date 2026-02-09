<?php
require_once 'auth.php';
require_once 'database.php';

header('Content-Type: application/json');

// Desativa exibição de erros na tela para não sujar o JSON, mas pode logar se precisar
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    $action = $_GET['action'] ?? '';
    $month  = isset($_GET['month']) ? intval($_GET['month']) + 1 : date('n');
    $year   = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $date   = $_GET['date'] ?? date('Y-m-d'); 

    // =====================================
    // 1. CALENDÁRIO (OCORRÊNCIAS DO DIA)
    // =====================================
    if ($action === 'calendar') {
        $sql = "
            SELECT 
                a.nome AS name, 
                e.nome AS `desc`, 
                DATE_FORMAT(o.data_hora, '%H:%i') AS time
            FROM ocorrencias o
            LEFT JOIN alunos a ON o.aluno_id = a.id
            LEFT JOIN epis e ON e.id = o.epi_id
            WHERE DATE(o.data_hora) = :data
            ORDER BY o.data_hora ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['data' => $date]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($result);
        exit;
    }

    // =====================================
    // 2. GRÁFICOS (BARRA E DOUGHNUT)
    // =====================================
    if ($action === 'charts') {

        // 1️⃣ Capacete
        $sqlCapacete = "
            SELECT MONTH(data_hora) AS mes, COUNT(*) AS capacete
            FROM ocorrencias
            WHERE epi_id = 2 AND YEAR(data_hora) = :ano
            GROUP BY mes ORDER BY mes
        ";
        $stmt = $pdo->prepare($sqlCapacete);
        $stmt->execute(['ano' => $year]);
        $capaceteArr = array_fill(0, 12, 0);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $capaceteArr[$r['mes'] - 1] = (int)$r['capacete'];
        }

        // 2️⃣ Óculos
        $sqlOculos = "
            SELECT MONTH(data_hora) AS mes, COUNT(*) AS oculos
            FROM ocorrencias
            WHERE epi_id = 1 AND YEAR(data_hora) = :ano
            GROUP BY mes ORDER BY mes
        ";
        $stmt = $pdo->prepare($sqlOculos);
        $stmt->execute(['ano' => $year]);
        $oculosArr = array_fill(0, 12, 0);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $oculosArr[$r['mes'] - 1] = (int)$r['oculos'];
        }

        // 3️⃣ Total
        $sqlTotal = "
            SELECT MONTH(data_hora) AS mes, COUNT(*) AS total
            FROM ocorrencias
            WHERE YEAR(data_hora) = :ano
            GROUP BY mes ORDER BY mes
        ";
        $stmt = $pdo->prepare($sqlTotal);
        $stmt->execute(['ano' => $year]);
        $totalArr = array_fill(0, 12, 0);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $totalArr[$r['mes'] - 1] = (int)$r['total'];
        }

        // 4️⃣ Doughnut (CORRIGIDO AQUI)
        $sqlDoughnut = "
            SELECT e.nome, COUNT(*) AS qtd
            FROM ocorrencias o
            JOIN epis e ON e.id = o.epi_id
            WHERE YEAR(o.data_hora) = :ano
            GROUP BY e.nome
        ";
        
        $stmtDough = $pdo->prepare($sqlDoughnut); // Tirei o espaço que estava aqui
        $stmtDough->execute(['ano' => $year]);
        
        $labels = []; $dataArr = [];
        foreach ($stmtDough->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $labels[] = $d['nome'];
            $dataArr[] = (int)$d['qtd'];
        }

        echo json_encode([
            'bar' => ['capacete' => $capaceteArr, 'oculos' => $oculosArr, 'total' => $totalArr],
            'doughnut' => ['labels' => $labels, 'data' => $dataArr]
        ]);
        exit;
    }

    // =====================================
    // 3. MODAL (DETALHES DO MÊS)
    // =====================================
   // =====================================
// 3. MODAL (DETALHES DO MÊS) - CORRIGIDO
// =====================================
if ($action === 'modal_details') {
    
    // 1. Forçamos a captura das variáveis aqui dentro para ter certeza
    // Se vier do JS como 0 (Jan), soma 1. Se vier como 1 (Fev), soma 1.
    // SE O SEU JS JÁ MANDA O MÊS CERTO (ex: 2 para Fevereiro), remova o "+ 1".
    $m = isset($_GET['month']) ? intval($_GET['month']) + 1 : date('n'); 
    $y = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

    $sql = "
        SELECT 
            DATE_FORMAT(o.data_hora, '%d/%m/%Y') AS data,
            a.nome AS aluno,
            e.nome AS epis,
            DATE_FORMAT(o.data_hora, '%H:%i') AS hora,
            CASE 
                WHEN o.status = 'pendente' THEN 'Pendente'
                WHEN o.status = 'confirmada' THEN 'Resolvido'
                ELSE o.status
            END AS status
        FROM ocorrencias o
        LEFT JOIN alunos a ON a.id = o.aluno_id
        LEFT JOIN epis e ON e.id = o.epi_id
        WHERE MONTH(o.data_hora) = :mes AND YEAR(o.data_hora) = :ano
        ORDER BY o.data_hora DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['mes' => $m, 'ano' => $y]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // SE A LISTA ESTIVER VAZIA, enviamos um aviso para você ver o que houve
    if (empty($dados)) {
        echo json_encode([
            [
                'aluno' => 'Nenhum registro encontrado',
                'epis'  => '---',
                'data'  => '---',
                'hora'  => '---',
                'status' => 'debug',
                // AQUI ESTÁ O SEGREDO: Ele vai te falar o que pesquisou
                'debug_info' => "Pesquisei pelo Mês: $m e Ano: $y" 
            ]
        ]);
    } else {
        echo json_encode($dados);
    }
    exit;
}

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}


require_once 'auth.php';
require_once 'database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$month  = isset($_GET['month']) ? intval($_GET['month']) : date('n'); // removi o +1
$year   = isset($_GET['year']) ? intval($_GET['year']) : date('Y');


// =====================
// 1. CALENDÁRIO
// =====================
if ($action === 'charts') {

    // =====================
    // 1️⃣ Capacete
    // =====================
    $sqlCapacete = "
        SELECT MONTH(o.data_hora) AS mes, COUNT(*) AS capacete
        FROM ocorrencia_epis oe
        JOIN ocorrencias o ON o.id = oe.ocorrencia_id
        WHERE oe.epi_id = 2
          AND YEAR(o.data_hora) = :ano
        GROUP BY mes
        ORDER BY mes
    ";
    $stmt = $pdo->prepare($sqlCapacete);
    $stmt->execute(['ano' => $year]);
    $capaceteArr = array_fill(0, 12, 0);
    foreach ($stmt->fetchAll() as $r) {
        $capaceteArr[$r['mes'] - 1] = (int)$r['capacete'];
    }

    // =====================
    // 2️⃣ Óculos
    // =====================
    $sqlOculos = "
        SELECT MONTH(o.data_hora) AS mes, COUNT(*) AS oculos
        FROM ocorrencia_epis oe
        JOIN ocorrencias o ON o.id = oe.ocorrencia_id
        WHERE oe.epi_id = 1
          AND YEAR(o.data_hora) = :ano
        GROUP BY mes
        ORDER BY mes
    ";
    $stmt = $pdo->prepare($sqlOculos);
    $stmt->execute(['ano' => $year]);
    $oculosArr = array_fill(0, 12, 0);
    foreach ($stmt->fetchAll() as $r) {
        $oculosArr[$r['mes'] - 1] = (int)$r['oculos'];
    }

    // =====================
    // 3️⃣ Total de ocorrências
    // =====================
    $sqlTotal = "
        SELECT MONTH(o.data_hora) AS mes, COUNT(DISTINCT o.id) AS total
        FROM ocorrencias o
        WHERE YEAR(o.data_hora) = :ano
        GROUP BY mes
        ORDER BY mes
    ";
    $stmt = $pdo->prepare($sqlTotal);
    $stmt->execute(['ano' => $year]);
    $totalArr = array_fill(0, 12, 0);
    foreach ($stmt->fetchAll() as $r) {
        $totalArr[$r['mes'] - 1] = (int)$r['total'];
    }

    // =====================
    // 4️⃣ Doughnut geral (totais por EPI)
    // =====================
    $sqlDoughnut = "
        SELECT e.nome, COUNT(*) AS qtd
        FROM ocorrencia_epis oe
        LEFT JOIN epis e ON e.id = oe.epi_id
        GROUP BY e.nome
    ";
    $labels = [];
    $data = [];
    foreach ($pdo->query($sqlDoughnut) as $d) {
        $labels[] = $d['nome'];
        $data[] = (int)$d['qtd'];
    }

    // =====================
    // Retorna JSON
    // =====================
    echo json_encode([
        'bar' => [
            'capacete' => $capaceteArr,
            'oculos' => $oculosArr,
            'total' => $totalArr
        ],
        'doughnut' => [
            'labels' => $labels,
            'data' => $data
        ]
    ]);
    exit;
}


// =====================
// 3. MODAL (DETALHES)
// =====================
if ($action === 'modal_details') {
    // 1. Limpa qualquer lixo de memória para evitar erro de JSON
    if (ob_get_length()) ob_clean();

    // 2. Define variáveis de data
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
    $year  = isset($_GET['year'])  ? (int)$_GET['year']  : date('Y');

    // 3. SQL EXATO PARA O SEU BANCO
    $sql = "
        SELECT 
            o.id,
            
            -- Data e Hora formatadas
            DATE_FORMAT(o.data_hora, '%d/%m/%Y') AS data,
            DATE_FORMAT(o.data_hora, '%H:%i') AS hora,
            
            -- Nome do Aluno (Tabela 'alunos', campo 'nome', ligado por 'aluno_id')
            a.nome AS aluno,
            
            -- Nome do EPI (Tabela 'epis', campo 'nome', ligado por 'epi_id')
            -- Usamos COALESCE para se não tiver EPI, mostrar 'Nenhum'
            COALESCE(e.nome, 'Nenhum') AS epis,

            -- STATUS (IMPORTANTE: Como não existe coluna status, criamos uma fixa)
            -- Se você quiser lógica real, precisaremos ver a tabela 'acoes_ocorrencia' depois.
            'Pendente' AS status_formatado

        FROM ocorrencias o
        LEFT JOIN alunos a ON a.id = o.aluno_id
        LEFT JOIN epis e ON e.id = o.epi_id
        
        WHERE MONTH(o.data_hora) = :mes
          AND YEAR(o.data_hora)  = :ano
        
        ORDER BY o.data_hora DESC
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['mes' => $month, 'ano' => $year]);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Retorna o JSON limpo
        echo json_encode($dados);

    } catch (PDOException $e) {
        // Se der erro, mostra qual foi
        echo json_encode(['error' => 'Erro SQL: ' . $e->getMessage()]);
    }
    
    exit; // Mata o script para não enviar mais nada
}
// ... (suas variáveis $action, $month, $year já existem aqui em cima) ...

    // ==========================================================
    // [NOVO] ROTA PADRÃO: Se não tiver 'action', lista os alunos
    // ==========================================================
    if (empty($action)) {
        // 1. Busca os alunos
        $stmt = $pdo->query("SELECT id, nome, curso FROM alunos");
        $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = [];

        foreach ($alunos as $aluno) {
            // 2. Verifica se tem infração HOJE (Risco Ativo)
            $stmtRisco = $pdo->prepare("SELECT COUNT(*) FROM ocorrencias WHERE aluno_id = ? AND DATE(data_hora) = CURDATE()");
            $stmtRisco->execute([$aluno['id']]);
            $temRisco = $stmtRisco->fetchColumn() > 0;

            // 3. Verifica se tem infração no PASSADO (Histórico)
            $stmtHist = $pdo->prepare("SELECT COUNT(*) FROM ocorrencias WHERE aluno_id = ?");
            $stmtHist->execute([$aluno['id']]);
            $temHistorico = $stmtHist->fetchColumn() > 0;

            // 4. Busca quais EPIs estão faltando hoje (se houver risco)
            $missing = [];
            if ($temRisco) {
                $stmtEpi = $pdo->prepare("
                    SELECT e.nome FROM ocorrencias o 
                    JOIN epis e ON e.id = o.epi_id 
                    WHERE o.aluno_id = ? AND DATE(o.data_hora) = CURDATE()
                ");
                $stmtEpi->execute([$aluno['id']]);
                $missing = $stmtEpi->fetchAll(PDO::FETCH_COLUMN);
            }

            // 5. Monta o objeto exatamente como o JS espera (Traduzindo nomes)
            $resultado[] = [
                'id'      => $aluno['id'],
                'name'    => $aluno['nome'],  // JS pede 'name', Banco tem 'nome'
                'course'  => $aluno['curso'], // JS pede 'course', Banco tem 'curso'
                'missing' => $missing,        // Array de EPIs
                'history' => $temHistorico    // true ou false
            ];
        }

        echo json_encode($resultado);
        exit; // IMPORTANTE: Encerra aqui para não executar o resto do arquivo
    }

    // ... (O seu código original começa aqui com if ($action === 'calendar')...)
?>