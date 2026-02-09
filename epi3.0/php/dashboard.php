<?php
require_once '../config/auth.php';
require_once '../config/database.php';

/* =========================
   CONFIGURAÇÃO DO PROFESSOR
========================= */
$cursoId = 1; // curso que o professor pode visualizar

/* =========================
   1. DADOS DO USUÁRIO
========================= */
$stmtUser = $pdo->prepare("
    SELECT nome, cargo 
    FROM usuarios 
    WHERE usuario = ?
");
$stmtUser->execute([$_SESSION['usuario_nome'] ?? 'admin']);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

$nomeUsuario  = $userData['nome']  ?? 'Usuário';
$cargoUsuario = ucfirst($userData['cargo'] ?? 'Visitante');

/* =========================
   2. KPIs (FILTRADOS POR CURSO)
========================= */

// Infrações do dia
$stmtDia = $pdo->prepare("
    SELECT COUNT(o.id)
    FROM ocorrencias o
    JOIN alunos a ON a.id = o.aluno_id
    WHERE a.curso_id = ?
      AND o.data_hora >= CURDATE()
      AND o.data_hora < CURDATE() + INTERVAL 1 DAY
");
$stmtDia->execute([$cursoId]);
$infraDia = $stmtDia->fetchColumn();

// Infrações da semana
$stmtSemana = $pdo->prepare("
    SELECT COUNT(o.id)
    FROM ocorrencias o
    JOIN alunos a ON a.id = o.aluno_id
    WHERE a.curso_id = ?
      AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE(), 1)
");
$stmtSemana->execute([$cursoId]);
$infraSemana = $stmtSemana->fetchColumn();

// Infrações do mês
$stmtMes = $pdo->prepare("
    SELECT COUNT(o.id)
    FROM ocorrencias o
    JOIN alunos a ON a.id = o.aluno_id
    WHERE a.curso_id = ?
      AND MONTH(o.data_hora) = MONTH(CURDATE())
      AND YEAR(o.data_hora) = YEAR(CURDATE())
");
$stmtMes->execute([$cursoId]);
$infraMes = $stmtMes->fetchColumn();

/* =========================
   3. MÉDIA DA TURMA (REAL)
========================= */

// Total de alunos do curso
$stmtAlunosTotal = $pdo->prepare("
    SELECT COUNT(*) 
    FROM alunos 
    WHERE curso_id = ?
");
$stmtAlunosTotal->execute([$cursoId]);
$totalAlunos = (int) $stmtAlunosTotal->fetchColumn();

// Evita divisão por zero
if ($totalAlunos === 0) {
    $mediaTurma = 100;
} else {
    $mediaTurma = 100 - (($infraMes / $totalAlunos) * 100);
    $mediaTurma = max(0, min(100, round($mediaTurma)));
}

/* =========================
   4. ALUNOS COM MAIS INFRAÇÕES
========================= */
$stmtAlunosCriticos = $pdo->prepare("
    SELECT a.nome, COUNT(o.id) AS total
    FROM alunos a
    JOIN ocorrencias o ON a.id = o.aluno_id
    WHERE a.curso_id = ?
    GROUP BY a.id
    ORDER BY total DESC
    LIMIT 4
");
$stmtAlunosCriticos->execute([$cursoId]);
$alunosCriticos = $stmtAlunosCriticos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Dashboard Unificado</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>

    <aside class="sidebar">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3"
                style="filter: drop-shadow(0 2px 4px rgba(227, 6, 19, 0.3));">
                <circle cx="12" cy="12" r="10" />
            </svg>
            &nbsp; EPI <span>GUARD</span>
        </div>
        <nav class="nav-menu">
            <a class="nav-item active" href="dashboard.php"> Dashboard</a>
            <a class="nav-item" href="infraçoes.php"> Infrações</a>
            <a class="nav-item" href="controleSala.php"> Controle de Sala</a>
            <a class="nav-item" href="ocorrencias.php">Ocorrencias</a>
        </nav>
    </aside>

    <main class="main-content">

        <header class="header">
            <div class="page-title">
                <h1>Painel Geral</h1>
                <p>Laboratório B • Monitoramento em Tempo Real</p>
            </div>

            <div class="header-actions">
                <button class="btn-export" onclick="exportData()">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z" />
                    </svg>
                    Exportar
                </button>

                <div class="user-profile-trigger" id="profileTrigger" onclick="toggleInstructorCard()">
                    <div class="user-info-mini">
                        <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($cargoUsuario); ?></span>
                    </div>
                    <div class="user-avatar"><?php echo strtoupper(substr($nomeUsuario, 0, 2)); ?></div>
                </div>
            </div>

            <div class="instructor-card" id="instructorCard">
                <div style="margin-bottom: 20px;">
                    <h3><?php echo htmlspecialchars($nomeUsuario); ?></h3>
                    <p style="color: #64748B; font-size: 13px;"></p>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cargo</span>
                    <span class="detail-value"><?php echo htmlspecialchars($cargoUsuario); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Turno</span>
                    <span class="detail-value">Manhã/Tarde</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value" style="color:var(--success)">Online</span>
                </div>
                <button class="btn-close-card" onclick="toggleInstructorCard()">Sair</button>
            </div>
        </header>

        <div class="kpi-grid">
            <div class="card">
                <div class="kpi-header">Infrações no dia</div>
                <div class="kpi-value"><?php echo $infraDia; ?> <span class="badge up">↗ HOJE</span></div>
            </div>
            <div class="card">
                <div class="kpi-header">Infrações Semanais</div>
                <div class="kpi-value"><?php echo $infraSemana; ?><span class="badge down">↘ SEMANA</span></div>
            </div>
            <div class="card">
                <div class="kpi-header">Infrações Mês</div>
                <div class="kpi-value"><?php echo $infraMes; ?><span class="badge up">↗ MÊS</span></div>
            </div>
            <div class="card">
                <div class="kpi-header">Média Turma</div>
                <div class="kpi-value"><?php echo $mediaTurma; ?>% <span class="badge up">↗ 1.2%</span></div>
            </div>
        </div>

        <div class="card" style="height: 380px; display: flex; flex-direction: column;">
            <div class="section-header">
                <span class="section-title">Consumo de EPIs (Anual)</span>
                <div style="display:flex; gap:15px; font-size:13px; color: #64748B;">
                </div>
            </div>
            <div style="flex: 1; position: relative;">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <div class="chart-grid">

            <div class="card">
                <div class="section-header">
                    <span class="section-title">Registro Diário</span>
                </div>
                <div class="calendar-nav">
                    <button class="nav-btn" onclick="changeDay(-1)">❮</button>
                    <div class="date-display">
                        <div class="date-day" id="displayDay">02</div>
                        <div class="date-month">Setembro 2024</div>
                    </div>
                    <button class="nav-btn" onclick="changeDay(1)">❯</button>
                </div>
                <div class="occurrences-list" id="occurrenceList">
                    </div>
            </div>

            <div class="card">
                <div class="section-header">
                    <span class="section-title">EPI Menos Usado</span>
                </div>
                <div style="height: 200px; position: relative;">
                    <canvas id="doughnutChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="section-header">
                    <span class="section-title">Alunos + Infrações</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    
                    <?php 
                    // Loop PHP para gerar a lista baseada no banco de dados real
                    if (count($alunosCriticos) > 0): 
                        foreach($alunosCriticos as $aluno): 
                            // Lógica simples para cor da barra
                            $width = ($aluno['total'] > 20) ? 100 : ($aluno['total'] * 5); 
                            $color = ($aluno['total'] > 10) ? '#E30613' : '#1F2937'; 
                    ?>
                        <div class="list-item">
                            <span style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($aluno['nome']); ?></span>
                            <div class="progress-bar">
                                <div class="progress-fill"
                                    style="width: <?php echo $width; ?>%; background: <?php echo $color; ?>; box-shadow: 0 2px 4px rgba(0,0,0, 0.2);">
                                </div>
                            </div>
                            <span style="font-size: 12px; font-weight: bold;"><?php echo $aluno['total']; ?></span>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="list-item"><span style="font-size:13px;">Sem dados ainda.</span></div>
                    <?php endif; ?>

                    <div style="text-align:center; margin-top:10px;">
                        <a href="#" style="font-size:12px; color:#64748B; text-decoration:none; font-weight: 600;">Ver todos</a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <div class="modal-overlay" id="detailModal">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title">
                    <h2>Relatório de Infrações: <span id="modalMonthTitle">Mês</span></h2>
                    <p style="font-size: 0.85rem; color: #64748B; margin-top: 4px;">Detalhamento completo dos registros
                        neste período.</p>
                </div>
                <button class="btn-close-modal" onclick="closeModal()">&times;</button>
            </div>

            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Aluno</th>
                            <th>Infração (EPI)</th>
                            <th>Horário</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody">
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 10px; text-align: right;">
                <button class="btn-modal-action" onclick="alert('Relatório baixado!')">
                    Baixar PDF
                </button>
            </div>
        </div>
    </div>

    <script src="../js/dashboard.js"></script>
</body>
</html>