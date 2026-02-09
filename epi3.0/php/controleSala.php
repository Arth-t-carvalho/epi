<?php
// ==========================================
// 1. CONFIGURAÇÕES E AUTH
// ==========================================
// Ajuste o caminho conforme sua estrutura de pastas
if (file_exists('../config/auth.php')) {
    require_once '../config/auth.php';
} elseif (file_exists('../config/auth.php')) {
    require_once '../config/auth.php';
} else {
    die("Erro: Arquivo auth.php não encontrado.");
}

// Se o database não estiver dentro do auth, incluímos aqui
if (file_exists('../php/database.php')) {
    require_once '../config/database.php';
}

// Dados do Usuário para o Cabeçalho (Header)
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Instrutor'; // Pega da sessão ou usa padrão
$cargoUsuario = $_SESSION['usuario_cargo'] ?? 'Supervisor';
$iniciais = strtoupper(substr($nomeUsuario, 0, 2));
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Controle de Sala</title>

    <link rel="stylesheet" href="../css/controleSala.css">
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
            <a class="nav-item" href="dashboard.php"> Dashboard</a>
            <a class="nav-item" href="infracoes.php"> Infrações</a>
            <a class="nav-item active" href="controleSala.php"> Controle de Sala</a>
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
                <div class="user-profile-trigger" id="profileTrigger" onclick="toggleInstructorCard()">
                    <div class="user-info-mini">
                        <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($cargoUsuario); ?></span>
                    </div>
                    <div class="user-avatar"><?php echo $iniciais; ?></div>
                </div>
            </div>

            <div class="instructor-card" id="instructorCard">
                <div style="margin-bottom: 20px;">
                    <h3><?php echo htmlspecialchars($nomeUsuario); ?></h3>
                    <p style="color: #64748B; font-size: 13px;">ID: <?php echo $_SESSION['usuario_id'] ?? '0000'; ?></p>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value" style="color:var(--success)">Online</span>
                </div>
                <button class="btn-close-card" onclick="location.href='../php/logout.php'">Sair</button>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="content-card">

                <div class="controls-bar">
                    <div class="search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" id="searchInput" placeholder="Buscar aluno...">
                    </div>

                    <select class="filter-select" id="statusFilter">
                        <option value="all">Todos os Status</option>
                        <option value="Risk">🔴 Risco Ativo (Hoje)</option>
                        <option value="History">🟡 Histórico (Passado)</option>
                        <option value="Safe">🟢 Regular</option>
                    </select>
                </div>

                <div class="student-list" id="studentList">
                    <p style="text-align:center; padding: 20px; color: #666;">Carregando alunos...</p>
                </div>

            </div>
        </div>

    </main>

    <div class="modal-overlay" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalName">Nome do Aluno</h2>
                <button class="close-btn" onclick="closeModal()">✕</button>
            </div>

            <div style="padding: 20px;">
                <p style="margin-bottom: 10px; font-weight: bold; color: #4b5563;">EPIs Pendentes:</p>
                <div id="modalEpiList" class="epi-list"></div>
            </div>

            <div id="modalFooterActions" class="modal-actions"
                style="padding: 20px; border-top: 1px solid #eee; text-align: right;">
                <button class="btn-cancel" onclick="closeModal()">Fechar</button>
            </div>
        </div>
    </div>

  <div id="detailModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalName">Nome do Aluno</h2>
            <button onclick="closeModal()" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <p id="modalCourse" style="color:#666; margin-bottom:15px;">Curso...</p>
            
            <h4>Status dos EPIs:</h4>
            <div id="modalEpiList" class="epi-list">
                </div>
        </div>
        <div id="modalFooterActions" class="modal-footer">
            </div>
    </div>
</div>
    <script src="../js/controleSala.js"></script>
</body>

</html>