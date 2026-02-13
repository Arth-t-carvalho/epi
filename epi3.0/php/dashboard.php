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


// --- COMPARAÇÕES (Acrescentar aqui) ---

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
        /* ==========================================================================
   ESTILOS ESPECÍFICOS DO CALENDÁRIO (MODAL)
   ========================================================================== */


        /* =============================================================
   NOVOS ESTILOS (Adicione ao final do dashboard.css)
   ============================================================= */

        /* --- 1. ANIMAÇÃO DE CHACOALHAR (ERRO NO INPUT) --- */
        @keyframes shake-horizontal {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-6px);
            }

            40%,
            80% {
                transform: translateX(6px);
            }
        }

        /* Classe que será adicionada via JS quando houver erro */
        .input-wrapper.error-shake {
            animation: shake-horizontal 0.4s ease-in-out;
            border-color: #EF4444 !important;
            /* Vermelho de erro */
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }

        /* Garante que o ícone e texto fiquem vermelhos no erro */
        .input-wrapper.error-shake svg.icon-left,
        .input-wrapper.error-shake input {
            color: #EF4444;
            fill: #EF4444;
        }


        /* --- 2. ESTILO DO SUBTÍTULO ("Dados atualizados") --- */
        /* Substitui o estilo antigo do .date-sub-text */
        .date-sub-text {
            display: inline-block;
            /* Necessário para o padding funcionar bem */
            margin-top: 1px;
            font-size: 12px;
            font-weight: 600;
            color: #047857;
            /* Verde mais profissional */
            background-color: #ECFDF5;
            /* Fundo verde bem claro */
            padding: 2px 1px;
            border-radius: 20px;
            /* Bordas bem redondas (estilo pílula) */

            /* Sombreado suave e elegante */
            box-shadow: 0 2px 6px rgba(4, 120, 87, 0.15),
                inset 0 1px 1px rgba(255, 255, 255, 0.8);

            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        /* Efeito opcional ao passar o mouse sobre a área da data */
        .date-center-display:hover .date-sub-text {
            background-color: #D1FAE5;
            transform: translateY(-1px);
        }

        /* Fundo Escuro do Modal (Centraliza tudo) */
        .modal-overlay-calendar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            /* Fundo escurecido */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            /* Garante que fique acima de tudo */

            /* Estado inicial: Escondido */
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(2px);
            /* Efeito de desfoque no fundo */
        }

        /* Quando o JS adiciona a classe .active, o modal aparece */
        .modal-overlay-calendar.active {
            opacity: 1;
            visibility: visible;
        }

        /* O "Cartão" Branco do Calendário */
        .calendar-wrapper {
            background: #fff;
            width: 380px;
            border-radius: 16px;
            padding: 25px;
            position: relative;
            border: 1px solid #D1D5DB;
            box-shadow:
                0 20px 25px -5px rgba(0, 0, 0, 0.25),
                0 10px 10px -5px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 1);
        }

        /* Botão Fechar (X) */
        .close-btn-cal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 18px;
            color: #9CA3AF;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .close-btn-cal:hover {
            background: #F3F4F6;
            color: #EF4444;
        }

        /* --- HEADER DO CALENDÁRIO (Mês e Ano) --- */
        .cal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-right: 20px;
            /* Espaço para o botão fechar não sobrepor */
        }

        .month-nav-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-btn-cal {
            background: none;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #4B5563;
            transition: all 0.2s;
        }

        .nav-btn-cal:hover {
            background: #E30613;
            color: white;
            border-color: #E30613;
        }

        /* Ajuste fino nos botões de navegação lateral */
        .nav-btn {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            width: 36px;
            height: 36px;
            cursor: pointer;
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .nav-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background-color: #fff;
            transform: scale(1.1);
        }

        /* Garante que o container de data não quebre linha */
        .date-display {
            flex: 1;
            user-select: none;
        }

        /* Estilo do Calendário Expandido (Modal) */
        .calendar-day-btn.active {
            background-color: var(--primary) !important;
            color: white !important;
            box-shadow: 0 4px 10px rgba(227, 6, 19, 0.3);
        }

        .calendar-day-btn:hover:not(.empty):not(.active) {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .selector-display {
            font-weight: 700;
            font-size: 16px;
            color: #1F2937;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* --- CORPO DO CALENDÁRIO (DIAS) --- */
        .calendar-body .weeks {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            /* 7 Colunas iguais */
            list-style: none;
            margin-bottom: 10px;
            border-bottom: 1px solid #F3F4F6;
            padding-bottom: 10px;
        }

        .calendar-body .weeks li {
            font-weight: 600;
            font-size: 13px;
            color: #9CA3AF;
            text-align: center;
        }

        .calendar-body .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            /* 7 Colunas iguais */
            list-style: none;
            gap: 5px;
            /* Espaço entre os dias */
        }

        .calendar-body .days li {
            height: 40px;
            /* Altura do botão do dia */
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            color: #374151;
            border-radius: 50%;
            /* Bolinha redonda */
            position: relative;
            transition: all 0.2s;
        }

        /* Dias Inativos (mês passado/futuro) */
        .days li.inactive {
            color: #D1D5DB;
            pointer-events: none;
        }

        /* Dia HOJE */
        .days li.today {
            color: #E30613;
            font-weight: bold;
            background: #FEF2F2;
        }

        /* Dia SELECIONADO */
        .days li.active {
            background: #E30613;
            color: #fff;
            font-weight: bold;
        }

        .days li:not(.active):not(.inactive):hover {
            background: #F3F4F6;
        }

        /* --- INPUT MANUAL (Rodapé do calendário) --- */
        .input-area {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .input-wrapper:focus-within {
            border-color: #E30613;
            box-shadow: 0 0 0 2px rgba(227, 6, 19, 0.1);
        }

        .icon-left {
            width: 20px;
            height: 20px;
            fill: #9CA3AF;
            margin-right: 10px;
        }

        .input-wrapper input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            font-size: 14px;
            color: #374151;
        }

        /* --- BOTÃO QUADRADO ARREDONDADO (LUPA) --- */

        /* --- BOTÃO QUADRADO COM SOMBREADO LEVE --- */

        /* --- BOTÃO QUADRADO COM SOMBRA FORTE E APARENTE --- */

        .btn-action-right {
            background: #F3F4F6;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            width: 32px;
            /* Tamanho fixo menor */
            height: 32px;
            /* Tamanho fixo menor */
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-left: 5px;
            padding: 0 !important;
            /* Remove qualquer padding que o dashboard.css aplique */
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
            flex-shrink: 0;
            /* Impede que o botão amasse */
        }

        /* Ajuste o ícone dentro dele */
        .btn-action-right svg {
            width: 16px !important;
            height: 16px !important;
            stroke-width: 2.5px;
        }

        /* Efeito ao passar o mouse (Hover) */
        .btn-action-right:hover {
            background: #FFFFFF;
            border-color: #9CA3AF;
            transform: translateY(-2px);
            /* Pula um pouco mais alto */

            /* Sombra fica mais intensa no hover */
            box-shadow:
                0 10px 15px -3px rgba(0, 0, 0, 0.2),
                0 4px 6px -2px rgba(0, 0, 0, 0.1);
        }

        .btn-action-right:hover svg {
            stroke: #E30613;
            /* Lupa brilha em vermelho */
            transform: scale(1.1);
            /* Lupa aumenta levemente */
        }

        /* Efeito de clique (Botão afunda e a sombra inverte) */
        .btn-action-right:active {
            transform: translateY(1px);
            box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.2);
            /* Sombra interna forte */
            background: #E5E7EB;
        }

        /* --- DROPDOWNS DO CALENDÁRIO (SELETORES) --- */

        .selector-container {
            position: relative;
            /* Necessário para o dropdown flutuar relativo a este botão */
        }

        .selector-dropdown {
            position: absolute;
            top: 100%;
            /* Logo abaixo do texto */
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 5px;
            z-index: 50;
            min-width: 120px;
            max-height: 200px;
            overflow-y: auto;
            /* Barra de rolagem se for muito longo */

            /* Escondido por padrão */
            display: none;
        }

        .selector-dropdown.active {
            display: block;
            /* Aparece quando tem a classe active */
        }

        .dropdown-item {
            padding: 8px 12px;
            font-size: 13px;
            color: #374151;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.2s;
            text-align: center;
        }

        .dropdown-item:hover {
            background-color: #F3F4F6;
            color: #E30613;
        }

        .dropdown-item.selected {
            background-color: #FEF2F2;
            color: #E30613;
            font-weight: bold;
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 8px;
            border-radius: 8px;
            border: none
                /* Box Shadow: Horizontal, Vertical, Desfoque, Espalhamento, Cor */
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Animação de pulo e brilho vermelho */
        /* Animação de chacoalhada (Shake) */

        .shake-attention {
            animation: shake-red 0.6s cubic-bezier(.36, .07, .19, .97) both;
            box-shadow: 0 0 15px rgba(227, 6, 19, 0.4);
            z-index: 10;
            /* Garante que fique acima de outros elementos ao tremer */
        }

        /* Container do Modal */
        .modal-ranking-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.7);
            z-index: 99999;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);
            animation: fadeIn 0.3s ease;
        }

        /* O Quadrado Centralizado */
        .modal-ranking-square {
            background: white;
            width: 500px;
            height: 500px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Cabeçalho */
        .modal-ranking-header {
            padding: 24px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }

        .modal-ranking-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: #1e293b;
        }

        /* Tabela e Scroll */
        .modal-ranking-body {
            flex: 1;
            overflow-y: auto;
            padding: 10px 20px;
        }

        /* Customização da Barra de Rolagem (Scrollbar) */
        .modal-ranking-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-ranking-body::-webkit-scrollbar-thumb {
            border-radius: 10px;
        }

        /* Estilo da Tabela */
        .ranking-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .ranking-table th {
            position: sticky;
            top: 0;
            background: white;
            padding: 10px;
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            z-index: 10;
        }

        .ranking-row {
            transition: all 0.2s ease;
        }

        /* Efeito Hover nas Linhas */
        .ranking-row:hover {
            transform: translateX(5px);
        }

        .ranking-row:hover td {
            background: #f8fafc !important;
        }

        .ranking-row td {
            padding: 12px 15px;
            font-size: 14px;
            background: #ffffff;
        }

        /* Arredondar pontas das linhas para o efeito de card */
        .ranking-row td:first-child {
            border-radius: 10px 0 0 10px;
            font-weight: 700;
            color: #94a3b8;
        }

        .ranking-row td:last-child {
            border-radius: 0 10px 10px 0;
            text-align: right;
        }

        /* Badge de Ocorrências */
        .badge-count {
            background: #fee2e2;
            color: #ef4444;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 12px;
        }

        /* Animação */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.45);
            }

            to {
                opacity: 0;
                transform: scale(1);
            }
        }

        /* Estilo Base para os Novos Status */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-left: 8px;
            vertical-align: middle;
        }

        /* Cores específicas para cada nível */
        .status-critico {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .status-alto {
            background-color: #ffedd5;
            color: #ea580c;
            border: 1px solid #fed7aa;
        }

        .status-moderado {
            background-color: #fef9c3;
            color: #a16207;
            border: 1px solid #fef08a;
        }

        .status-baixo {
            background-color: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
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
            <a class="nav-item active" href="dashboard.php"> Dashboard</a>
            <a class="nav-item" href="infracoes.php"> Infrações</a>
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
                <span class="section-title">Infraçoes de EPIs (Anual)</span>
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

                <div class="calendar-nav" onclick="openCalendarModal()"
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

                        <div style="font-size: 10px; color: #E30613; font-weight: 700; margin-top: 6px; display: flex; align-items: center; gap: 4px; cursor: pointer;"
                            onclick="toggleCalendar()">
                            <span style="font-size: 8px;">▼</span> Clique para expandir
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


    <script src="../js/dashboard.js"></script>
</body>

</html>