<?php require_once "../php/auth.php"; ?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Controle de Turma</title>
    <link rel="stylesheet" href="../css/ControleSala.css">
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
            <a class="nav-item " href="../dashboard.html"> Dashboard</a>
            <a class="nav-item" href="/infraçoes.html"> Infrações</a>
            <a class="nav-item active" href="controleSala.html"> Controle de Sala</a>
            <a class="nav-item" href="/ocorrencias.html">Ocorrencias</a>
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
                        <span class="user-name">João Silva</span>
                        <span class="user-role">Téc. Segurança</span>
                    </div>
                    <div class="user-avatar">JS</div>
                </div>
            </div>

            <div class="instructor-card" id="instructorCard">
                <div style="margin-bottom: 20px;">
                    <h3>João Silva</h3>
                    <p style="color: #64748B; font-size: 13px;">ID: 9821-BR</p>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cargo</span>
                    <span class="detail-value">Supervisor</span>
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

        <div class="dashboard-container">
            <div class="content-card">
                <div class="controls-bar">
                    <div class="search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" id="searchInput" placeholder="Buscar aluno...">
                    </div>
                    <select class="filter-select" id="statusFilter">
                        <option value="all"> Todos</option>
                        <option value="Risk"> Risco Ativo</option>
                        <option value="History"> Histórico</option>
                        <option value="Repeat"> Reincidentes (Crítico)</option>
                        <option value="Safe"> Regulares</option>
                    </select>
                </div>

                <div class="student-list" id="studentList">
                </div>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h2 id="modalName">Nome</h2>
                    <p id="modalCourse">Curso</p>
                </div>
                <button class="close-btn" onclick="closeModal()">✕</button>
            </div>

            <h4 style="margin-bottom: 12px; font-size: 14px; color: var(--text-secondary);">Checklist (Tempo Real)</h4>
            <div class="epi-list" id="modalEpiList"></div>

            <div class="modal-actions" id="modalFooterActions">
            </div>
        </div>
    </div>
  <script src="../js/crontroleSala.js" defer></script>
    
</body>

</html>