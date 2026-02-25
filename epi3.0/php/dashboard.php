<?php
// ARQUIVO: php/dashboard.php

// Ajuste os requires conforme a localização da sua pasta config
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// CONFIGURAÇÃO DO PROFESSOR
$cursoId = 1;

// DADOS DO USUÁRIO
$stmtUser = $pdo->prepare("SELECT nome, cargo FROM usuarios WHERE usuario = ?");
$stmtUser->execute([$_SESSION['usuario_nome'] ?? 'admin']);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

$nomeUsuario = $userData['nome'] ?? 'Usuário';
$cargoUsuario = ucfirst($userData['cargo'] ?? 'Visitante');

// KPIs
$stmtDia = $pdo->prepare("SELECT COUNT(o.id) FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() AND o.data_hora < CURDATE() + INTERVAL 1 DAY");
$stmtDia->execute([$cursoId]);
$infraDia = $stmtDia->fetchColumn();

$stmtSemana = $pdo->prepare("SELECT COUNT(o.id) FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE(), 1)");
$stmtSemana->execute([$cursoId]);
$infraSemana = $stmtSemana->fetchColumn();

$stmtMes = $pdo->prepare("SELECT COUNT(o.id) FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND MONTH(o.data_hora) = MONTH(CURDATE()) AND YEAR(o.data_hora) = YEAR(CURDATE())");
$stmtMes->execute([$cursoId]);
$infraMes = $stmtMes->fetchColumn();

// MÉDIA TURMA
$stmtAlunosTotal = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE curso_id = ?");
$stmtAlunosTotal->execute([$cursoId]);
$totalAlunos = (int) $stmtAlunosTotal->fetchColumn();

if ($totalAlunos === 0) {
    $mediaTurma = 100;
} else {
    $mediaTurma = 100 - (($infraMes / $totalAlunos) * 100);
    $mediaTurma = max(0, min(100, round($mediaTurma)));
}

// ALUNOS CRÍTICOS
$stmtAlunosCriticos = $pdo->prepare("SELECT a.nome, COUNT(o.id) AS total FROM alunos a JOIN ocorrencias o ON a.id = o.aluno_id WHERE a.curso_id = ? GROUP BY a.id ORDER BY total DESC LIMIT 5");
$stmtAlunosCriticos->execute([$cursoId]);
$alunosCriticos = $stmtAlunosCriticos->fetchAll(PDO::FETCH_ASSOC);


// --- COMPARAÇÕES ---

// Ontem (para comparar com hoje)
$stmtOntem = $pdo->prepare("SELECT COUNT(o.id) FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND o.data_hora >= CURDATE() - INTERVAL 1 DAY AND o.data_hora < CURDATE()");
$stmtOntem->execute([$cursoId]);
$infraOntem = (int) $stmtOntem->fetchColumn();
$percDia = ($infraOntem > 0) ? round((($infraDia - $infraOntem) / $infraOntem) * 100, 1) : ($infraDia * 100);

// Semana Anterior
$stmtSemAnt = $pdo->prepare("SELECT COUNT(o.id) FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)");
$stmtSemAnt->execute([$cursoId]);
$infraSemanaAnterior = (int) $stmtSemAnt->fetchColumn();
$percSemana = ($infraSemanaAnterior > 0) ? round((($infraSemana - $infraSemanaAnterior) / $infraSemanaAnterior) * 100, 1) : ($infraSemana * 100);

// Mês Anterior
$stmtMesAnt = $pdo->prepare("SELECT COUNT(o.id) FROM ocorrencias o JOIN alunos a ON a.id = o.aluno_id WHERE a.curso_id = ? AND MONTH(o.data_hora) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(o.data_hora) = YEAR(CURDATE() - INTERVAL 1 MONTH)");
$stmtMesAnt->execute([$cursoId]);
$infraMesAnterior = (int) $stmtMesAnt->fetchColumn();
$percMes = ($infraMesAnterior > 0) ? round((($infraMes - $infraMesAnterior) / $infraMesAnterior) * 100, 1) : ($infraMes * 100);

$stmtRankingModal = $pdo->prepare("SELECT a.nome, COUNT(o.id) AS total FROM alunos a JOIN ocorrencias o ON a.id = o.aluno_id WHERE a.curso_id = ? GROUP BY a.id ORDER BY total DESC");
$stmtRankingModal->execute([$cursoId]);
$rankingModal = $stmtRankingModal->fetchAll(PDO::FETCH_ASSOC);

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
    <style>
        /* =========================================
        VARIÁVEIS GLOBAIS
        ========================================= */
        :root {
            --primary: #E30613;
            --primary-dark: #bf040f;
            --primary-light: rgba(227, 6, 19, 0.1);
            --secondary: #1F2937;
            --text-main: #111827;
            --text-muted: #64748B;
            --bg-body: #e6e8ec;
            --bg-card: #ebebeb;
            --border: #CBD5E1;
            --success: #10B981;
            --danger: #EF4444;
            --chart-main-color: #E30613;
        }

        /* =========================================
        TEMA ESCURO
        ========================================= */
        [data-theme="dark"] {
            --primary: #ff4d4d;
            --primary-dark: #e63939;
            --primary-light: rgba(255, 77, 77, 0.15);
            --secondary: #E5E7EB;
            --text-main: #F3F4F6;
            --text-muted: #9CA3AF;
            --bg-body: #1a1e24;
            --bg-card: #2d3748;
            --border: #4B5563;
            --success: #34D399;
            --danger: #F87171;
            --chart-main-color: #ff4d4d;
        }

        [data-theme="dark"] body {
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        [data-theme="dark"] .sidebar {
            background: #1f2937;
            border-right-color: var(--border);
        }

        [data-theme="dark"] .brand {
            color: white;
        }

        [data-theme="dark"] .brand span {
            color: var(--primary);
        }

        [data-theme="dark"] .nav-item {
            color: #9ca3af;
        }

        [data-theme="dark"] .nav-item:hover,
        [data-theme="dark"] .nav-item.active {
            background-color: rgba(227, 6, 19, 0.1);
            color: var(--primary);
        }

        [data-theme="dark"] .page-title h1 {
            color: white;
        }

        [data-theme="dark"] .page-title p {
            color: #9ca3af;
        }

        [data-theme="dark"] .btn-export {
            background: #1f2937;
            border-color: var(--border);
            color: white;
        }

        [data-theme="dark"] .btn-export:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        [data-theme="dark"] .user-profile-trigger {
            background: #1f2937;
            border-color: var(--border);
        }

        [data-theme="dark"] .user-name {
            color: white;
        }

        [data-theme="dark"] .user-role {
            color: #9ca3af;
        }

        [data-theme="dark"] .user-avatar {
            background: var(--primary);
            color: white;
        }

        [data-theme="dark"] .instructor-card {
            background: #1f2937;
            border-color: var(--border);
        }

        [data-theme="dark"] .detail-row {
            border-bottom-color: var(--border);
        }

        [data-theme="dark"] .detail-label {
            color: #9ca3af;
        }

        [data-theme="dark"] .detail-value {
            color: white;
        }

        [data-theme="dark"] .btn-close-card {
            background: #374151;
            color: #9ca3af;
        }

        [data-theme="dark"] .card {
            background: #1f2937;
            border-color: var(--border);
        }

        [data-theme="dark"] .card:hover {
            border-color: var(--primary);
        }

        [data-theme="dark"] .kpi-header {
            color: #9ca3af;
        }

        [data-theme="dark"] .kpi-value {
            color: white;
        }

        [data-theme="dark"] .badge.up {
            background: #065f46;
            color: #6ee7b7;
            border-color: #047857;
        }

        [data-theme="dark"] .badge.down {
            background: #7f1d1d;
            color: #fca5a5;
            border-color: #b91c1c;
        }

        [data-theme="dark"] .status-badge {
            color: white;
        }

        [data-theme="dark"] .status-critico {
            background: #7f1d1d;
            color: #fecaca;
            border-color: #b91c1c;
        }

        [data-theme="dark"] .status-alto {
            background: #7c2d12;
            color: #fed7aa;
            border-color: #9a3412;
        }

        [data-theme="dark"] .status-moderado {
            background: #713f12;
            color: #fef08a;
            border-color: #854d0e;
        }

        [data-theme="dark"] .status-baixo {
            background: #14532d;
            color: #bbf7d0;
            border-color: #166534;
        }

        [data-theme="dark"] .section-title {
            color: white;
        }

        [data-theme="dark"] .calendar-nav {
            background: #1f2937;
            border-color: var(--border);
        }

        [data-theme="dark"] .nav-btn {
            background: #374151;
            border-color: var(--border);
            color: white;
        }

        [data-theme="dark"] .nav-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        [data-theme="dark"] #displayDayNum {
            color: var(--primary);
        }

        [data-theme="dark"] #displayMonthStr {
            color: #9ca3af;
        }

        [data-theme="dark"] .occurrence-item {
            border-bottom-color: var(--border);
        }

        [data-theme="dark"] .occ-avatar {
            background: #374151;
            color: white;
        }

        [data-theme="dark"] .occ-name {
            color: white;
        }

        [data-theme="dark"] .occ-desc {
            color: #9ca3af;
        }

        [data-theme="dark"] .occ-time {
            background: rgba(227, 6, 19, 0.2);
            color: #fca5a5;
            border-color: var(--border);
        }

        [data-theme="dark"] .list-item {
            border-bottom-color: var(--border);
        }

        [data-theme="dark"] .list-item span {
            color: white;
        }

        [data-theme="dark"] .progress-bar {
            background: #374151;
        }

        [data-theme="dark"] a {
            color: #9ca3af;
        }

        [data-theme="dark"] a:hover {
            color: var(--primary);
        }

        /* Modal de detalhes no modo escuro */
        [data-theme="dark"] .modal-overlay {
            background: rgba(0, 0, 0, 0.8);
        }

        [data-theme="dark"] .modal-container {
            background: #1f2937;
            border-color: var(--border);
        }

        [data-theme="dark"] .modal-header {
            border-bottom-color: var(--border);
        }

        [data-theme="dark"] .modal-title h2 {
            color: white;
        }

        [data-theme="dark"] .modal-title span {
            color: var(--primary);
        }

        [data-theme="dark"] .btn-close-modal {
            color: #9ca3af;
        }

        [data-theme="dark"] .btn-close-modal:hover {
            color: var(--primary);
        }

        [data-theme="dark"] .custom-table th {
            background: #374151;
            color: #9ca3af;
        }

        [data-theme="dark"] .custom-table td {
            color: white;
            border-bottom-color: var(--border);
        }

        [data-theme="dark"] .custom-table tr:hover {
            background-color: #374151;
        }

        [data-theme="dark"] .btn-modal-action {
            background: #374151;
            color: white;
        }

        [data-theme="dark"] .btn-modal-action:hover {
            background: var(--primary);
            color: white;
        }

        /* Calendário modal no modo escuro */
        [data-theme="dark"] .modal-overlay-calendar {
            background: rgba(0, 0, 0, 0.8);
        }

        [data-theme="dark"] .calendar-wrapper {
            background: #1f2937;
            border-color: var(--border);
        }

        [data-theme="dark"] .close-btn-cal {
            color: #9ca3af;
        }

        [data-theme="dark"] .close-btn-cal:hover {
            background: #374151;
            color: var(--primary);
        }

        [data-theme="dark"] .selector-display {
            color: white;
        }

        [data-theme="dark"] .selector-dropdown {
            background: #1f2937;
            border-color: var(--border);
        }

        [data-theme="dark"] .dropdown-item {
            color: white;
        }

        [data-theme="dark"] .dropdown-item:hover {
            background-color: #374151;
            color: var(--primary);
        }

        [data-theme="dark"] .calendar-body .weeks li {
            color: #9ca3af;
        }

        [data-theme="dark"] .calendar-body .days li {
            color: white;
        }

        [data-theme="dark"] .days li.today {
            background: rgba(227, 6, 19, 0.2);
            color: #fca5a5;
        }

        [data-theme="dark"] .days li.active {
            background: var(--primary);
            color: white;
        }

        [data-theme="dark"] .days li:not(.active):not(.inactive):hover {
            background: #374151;
        }

        [data-theme="dark"] .days li.inactive {
            color: #6b7280;
        }

        [data-theme="dark"] .input-wrapper {
            background: #374151;
            border-color: var(--border);
        }

        [data-theme="dark"] .input-wrapper input {
            color: white;
        }

        [data-theme="dark"] .input-wrapper input::placeholder {
            color: #9ca3af;
        }

        [data-theme="dark"] .btn-action-right {
            background: #4b5563;
            border-color: var(--border);
        }

        [data-theme="dark"] .btn-action-right:hover {
            background: white;
        }

        [data-theme="dark"] .btn-action-right:hover svg {
            stroke: var(--primary);
        }

        /* Ranking modal no modo escuro */
        [data-theme="dark"] .modal-ranking-overlay {
            background: rgba(0, 0, 0, 0.8);
        }

        [data-theme="dark"] .modal-ranking-square {
            background: #1f2937;
            border-color: var(--border);
        }

        [data-theme="dark"] .modal-ranking-header {
            background: #1f2937;
            border-bottom-color: var(--border);
        }

        [data-theme="dark"] .modal-ranking-header h2 {
            color: white;
        }

        [data-theme="dark"] .ranking-table th {
            background: #1f2937;
            color: #9ca3af;
        }

        [data-theme="dark"] .ranking-row td {
            background: #374151;
            color: white;
        }

        [data-theme="dark"] .ranking-row:hover td {
            background: #4b5563 !important;
        }

        [data-theme="dark"] .badge-count {
            background: rgba(227, 6, 19, 0.2);
            color: #fca5a5;
        }

        /* Notificações no modo escuro */
        [data-theme="dark"] .toast {
            background: #1f2937;
            border-left-color: var(--primary);
        }

        [data-theme="dark"] .toast-icon {
            background: rgba(227, 6, 19, 0.2);
            color: #fca5a5;
        }

        [data-theme="dark"] .toast-title {
            color: white;
        }

        [data-theme="dark"] .toast-message {
            color: #9ca3af;
        }

        [data-theme="dark"] .toast-message b {
            color: white;
        }

        [data-theme="dark"] .toast-time {
            color: #fca5a5;
        }

        /* Ajustes para gráficos no modo escuro */
        [data-theme="dark"] canvas {
            filter: brightness(1.1);
        }

        [data-theme="dark"] .empty-state {
            color: #9ca3af !important;
        }

        .card.clickable {
            cursor: pointer;
        }

        .card.clickable:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px -10px rgba(227, 6, 19, 0.15), 0 10px 20px -5px rgba(0, 0, 0, 0.1);
            border-color: rgba(255, 0, 17, 0.473);
        }
    </style>
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

            <a class="nav-item active" href="dashboard.php">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>

            <a class="nav-item" href="infracoes.php">
                <i data-lucide="alert-triangle"></i>
                <span>Infrações</span>
            </a>

            <a class="nav-item" href="controleSala.php">
                <i data-lucide="users"></i>
                <span>Controle de Sala</span>
            </a>

            <a class="nav-item" href="ocorrencias.php">
                <i data-lucide="file-text"></i>
                <span>Ocorrências</span>
            </a>

            <a class="nav-item" href="configuracoes.php">
                <i data-lucide="settings"></i>
                <span>Configurações</span>
            </a>
            <a class="nav-item" href="gestaoUsu.html">
                <i data-lucide="settings"></i>
                <span>Gestão</span>
            </a>
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
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cargo</span>
                    <span class="detail-value"><?php echo htmlspecialchars($cargoUsuario); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value" style="color:var(--success)">Online</span>
                </div>
                <button class="btn-close-card" onclick="toggleInstructorCard()">Sair</button>
            </div>
        </header>

        <div class="kpi-grid">
            <div class="card" id="cardInfraDia" onclick="highlightDaily('dia')" style="cursor: pointer;">
                <div class="kpi-header">Infrações Diarias</div>
                <div class="kpi-value">
                    <span id="kpiDia"><?php echo $infraDia; ?></span>
                    <span id="badgeDia" class="badge <?php echo $percDia >= 0 ? 'up' : 'down'; ?>">
                        <?php echo ($percDia >= 0 ? '↗ ' : '↘ ') . abs($percDia); ?>%
                    </span>
                </div>
            </div>
            <div class="card" onclick="highlightDaily('semana')">
                <div class="kpi-header">Infrações Semanais</div>
                <div class="kpi-value">
                    <span id="kpiSemana"><?php echo $infraSemana; ?></span>
                    <span id="badgeSemana" class="badge <?php echo $percSemana >= 0 ? 'up' : 'down'; ?>">
                        <?php echo ($percSemana >= 0 ? '↗ ' : '↘ ') . abs($percSemana); ?>%
                    </span>
                </div>
            </div>
            <div class="card" onclick="highlightDaily('mes')">
                <div class="kpi-header">Infrações Mês</div>
                <div class="kpi-value">
                    <span id="kpiMes"><?php echo $infraMes; ?></span>
                    <span id="badgeMes" class="badge <?php echo $percMes >= 0 ? 'up' : 'down'; ?>">
                        <?php echo ($percMes >= 0 ? '↗ ' : '↘ ') . abs($percMes); ?>%
                    </span>
                </div>
            </div>
            <div class="card">
                <div class="kpi-header">Conformidade</div>
                <div class="kpi-value">
                    <span id="kpiMedia"><?php echo $mediaTurma; ?>%</span>

                    <?php
                    // Lógica de Status de Conformidade
                    if ($mediaTurma < 70) {
                        echo '<span class="status-badge status-critico" title="Risco alto! Bloqueio ou intervenção imediata">🚨 CRÍTICO</span>';
                    } elseif ($mediaTurma < 85) {
                        echo '<span class="status-badge status-alto" title="Abaixo do aceitável! Requer plano de ação">🟠 ALTO RISCO</span>';
                    } elseif ($mediaTurma < 95) {
                        echo '<span class="status-badge status-moderado" title="Nível aceitável, mas requer monitoramento">🟡 MODERADO</span>';
                    } else {
                        echo '<span class="status-badge status-baixo" title="Operação segura e padrão ideal">🟢 CONTROLADO</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="card" style="height: 380px; display: flex; flex-direction: column;">
            <div class="section-header">
                <span class="section-title">Infrações de EPIs (Anual)</span>
            </div>
            <div style="flex: 1; position: relative;">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <div class="chart-grid">

            <div class="card" id="cardRegistroDiario">
                <div class="section-header">
                    <span class="section-title">Registro Diário</span>
                </div>

                <div class="calendar-nav" onclick="toggleCalendar()"
                    style="cursor: pointer; transition: transform 0.2s; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid var(--border);"
                    onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">

                    <button class="nav-btn" onclick="event.stopPropagation(); changeDay(-1)">❮</button>

                    <div class="date-display"
                        style="text-align: center; display: flex; flex-direction: column; align-items: center;">
                        <div id="displayDayNum"
                            style="color: #E30613; font-size: 28px; font-weight: 800; line-height: 1;">
                            02
                        </div>
                        <div id="displayMonthStr" style="color: #64748B; font-size: 13px; font-weight: 600;">
                            Setembro 2024
                        </div>

                        <div
                            style="font-size: 10px; color: #E30613; font-weight: 700; margin-top: 6px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <span style="font-size: 8px;"></span> Clique para expandir
                        </div>
                    </div>

                    <button class="nav-btn" onclick="event.stopPropagation(); changeDay(1)">❯</button>
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
                    if (count($alunosCriticos) > 0):
                        foreach ($alunosCriticos as $aluno):
                            $width = ($aluno['total'] > 20) ? 100 : ($aluno['total'] * 5);
                            $color = ($aluno['total'] > 10) ? '#E30613' : '#1F2937';
                            ?>
                            <div class="list-item">
                                <span
                                    style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($aluno['nome']); ?></span>
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
                        <a href="javascript:void(0)" onclick="openAlunosModal()"
                            style="font-size:12px; color:#64748B; text-decoration:none; font-weight: 600;">
                            Ver todos
                        </a>
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
                    <p style="font-size: 0.85rem; color: #64748B; margin-top: 4px;">Detalhamento completo dos registros.
                    </p>
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
    <div class="modal-overlay-calendar" id="calendarModal">
        <div class="calendar-wrapper">
            <button class="close-btn-cal" onclick="toggleCalendar()">✕</button>

            <header class="cal-header">
                <div class="month-nav-wrapper">
                    <button class="nav-btn-cal" id="prevMonth">❮</button>

                    <div class="selector-container" id="monthSelector">
                        <div class="selector-display" onclick="toggleMonthList()">
                            <span id="calMonthDisplay">Janeiro</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z" />
                            </svg>
                        </div>
                        <div class="selector-dropdown" id="monthDropdown">
                        </div>
                    </div>

                    <button class="nav-btn-cal" id="nextMonth">❯</button>
                </div>

                <div class="selector-container" id="yearSelector">
                    <div class="selector-display" onclick="toggleYearList()">
                        <span id="calYearDisplay">2026</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </div>
                    <div class="selector-dropdown" id="yearDropdown">
                    </div>
                </div>
            </header>

            <div class="calendar-body">
                <ul class="weeks">
                    <li>Dom</li>
                    <li>Seg</li>
                    <li>Ter</li>
                    <li>Qua</li>
                    <li>Qui</li>
                    <li>Sex</li>
                    <li>Sáb</li>
                </ul>
                <ul class="days" id="calendarDays"></ul>
            </div>

            <div class="input-area" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">
                <div class="input-wrapper"
                    style="display: flex; align-items: center; height: 38px; background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 8px;">

                    <svg class="icon-left" style="width: 16px; height: 16px; fill: #9CA3AF; margin-right: 8px;">
                        <path
                            d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                    </svg>

                    <input type="text" id="manualDateInput" placeholder="DD/MM/AAAA" maxlength="10"
                        style="border: none; background: transparent; outline: none; width: 100%; font-size: 13px; height: 100%; padding: 0;">

                    <button class="btn-action-right" onclick="commitManualDate()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="alunosRankingModal" class="modal-ranking-overlay" onclick="closeAlunosModal()">
        <div class="modal-ranking-square" onclick="event.stopPropagation()">

            <div class="modal-ranking-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2>Ranking Geral</h2>
                        <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Lista completa de infrações</p>
                    </div>
                    <button onclick="closeAlunosModal()"
                        style="background:none; border:none; cursor:pointer; font-size:20px; color:#94a3b8;">&times;</button>
                </div>
            </div>

            <div class="modal-ranking-body">
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>Pos.</th>
                            <th>Aluno</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rankingModal)): ?>
                            <?php foreach ($rankingModal as $index => $aluno): ?>
                                <tr class="ranking-row">
                                    <td>#<?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                                    <td>
                                        <span class="badge-count"><?php echo $aluno['total']; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align:center;">Nenhum dado encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="padding: 15px; border-top: 1px solid #f1f5f9; text-align: center;">

            </div>
        </div>
    </div>

    <div id="notification-container"></div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../js/chart-manager.js"></script>
    <script src="../js/dashboard.js"></script>
    <script>
        // Inicializa ícones do Lucide
        lucide.createIcons();

        // Aplicar tema salvo ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
            }
            
            // Verificar estado do link nos cards
            const linksEnabled = localStorage.getItem('linksEnabled') === 'true';
            if (linksEnabled) {
                document.querySelectorAll('.card').forEach(c => c.classList.add('clickable'));
            }
            
            // Carregar cor do gráfico
            const chartColor = localStorage.getItem('chartColor') || '#E30613';
            document.documentElement.style.setProperty('--chart-main-color', chartColor);
        });

        // Observer para mudanças no localStorage (sincronizar entre abas)
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme' || e.key === 'theme_trigger') {
                const currentTheme = localStorage.getItem('theme');
                if (currentTheme === 'dark') {
                    document.body.setAttribute('data-theme', 'dark');
                } else {
                    document.body.removeAttribute('data-theme');
                }
            }
            
            if (e.key === 'linksEnabled') {
                const enabled = localStorage.getItem('linksEnabled') === 'true';
                document.querySelectorAll('.card').forEach(card => {
                    if (enabled) {
                        card.classList.add('clickable');
                    } else {
                        card.classList.remove('clickable');
                    }
                });
            }
            
            if (e.key === 'chartColor') {
                document.documentElement.style.setProperty('--chart-main-color', e.newValue);
            }
        });

        // Função para alternar tema (será chamada pela página de configurações)
        window.toggleTheme = function() {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            
            if (newTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
            } else {
                document.body.removeAttribute('data-theme');
            }
            
            localStorage.setItem('theme', newTheme);
        }

        // Função para alternar links nos cards
        window.toggleLinkAbility = function() {
            const enabled = localStorage.getItem('linksEnabled') === 'true';
            document.querySelectorAll('.card').forEach(card => {
                if (enabled) {
                    card.classList.add('clickable');
                } else {
                    card.classList.remove('clickable');
                }
            });
        }

        // Função para manipular clique nos cards
        window.handleCardClick = function(cardElement) {
            const linksEnabled = localStorage.getItem('linksEnabled') === 'true';
            if (linksEnabled) {
                const header = cardElement.querySelector('.kpi-header');
                if (header) {
                    const cardType = header.textContent.includes('Diarias') ? 'dia' :
                                    header.textContent.includes('Semanais') ? 'semana' :
                                    header.textContent.includes('Mês') ? 'mes' : 'conformidade';
                    
                    if (cardType !== 'conformidade') {
                        highlightDaily(cardType);
                    }
                }
            }
        }

        // Adicionar event listeners aos cards
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.card').forEach(card => {
                card.addEventListener('click', function(e) {
                    window.handleCardClick(this);
                });
            });
        });
    </script>

    <script>
    // Sincroniza a cor de destaque em todo o CSS do Dashboard
    document.addEventListener('DOMContentLoaded', function() {
        const corDestaque = localStorage.getItem('chartColor');
        if (corDestaque) {
            // Atualiza a variável de cor principal da interface
            document.documentElement.style.setProperty('--primary', corDestaque);
            document.documentElement.style.setProperty('--chart-main-color', corDestaque);
        }
    });
</script>

</body>

</html>