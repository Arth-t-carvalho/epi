<?php require_once "../php/auth.php"; ?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Galeria de Infrações</title>
    <link rel="stylesheet" href="../css/infracoes.css">

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
            <a class="nav-item" href="dashboard.html"> Dashboard</a>
            <a class="nav-item active" href="/infraçoes.html"> Infrações</a>
            <a class="nav-item" href="controleSala.html"> Controle de Sala</a>
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
        <div class="gallery-container">

            <div class="filter-bar">
                <select class="filter-select">
                    <option>Hoje</option>
                    <option>Ontem</option>
                    <option>Últimos 7 dias</option>
                </select>
                <select class="filter-select">
                    <option>Todos os Setores</option>
                    <option>Usinagem</option>
                    <option>Solda</option>
                </select>
                <select class="filter-select">
                    <option>Todas as Infrações</option>
                    <option>Sem Óculos</option>
                    <option>Sem Capacete</option>
                </select>
            </div>

            <div class="grid-cards" id="cardsContainer">
                </div>
        </div>
    </main>

    <div class="modal-overlay" id="imageModal" onclick="closeModal(event)">
        <div class="modal-content">
            <button class="close-modal-btn" onclick="forceClose()">✕</button>
            <img src="" id="modalImg" class="full-image">
            <div class="modal-info-bar">
                <div>
                    <h3 id="modalName" style="color: #1F2937; margin-bottom: 4px;">Nome do Infrator</h3>
                    <p id="modalDesc" style="color: #E30613; font-weight: 600;">Infração: ...</p>
                </div>
                <div style="text-align: right;">
                    <p id="modalTime" style="color: #64748B; font-size: 14px;">Horário</p>
                    <p id="modalCam" style="color: #64748B; font-size: 12px;">Camera ID</p>
                </div>
            </div>
        </div>
    </div>

      <script src="../js/infraçoes.js" defer></script>
</body>
</html>