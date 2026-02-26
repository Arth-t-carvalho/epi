<?php
// Correção solicitada: auth.php (caminho relativo assumindo que está na pasta /pages/)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Nova Ocorrência</title>
    <link rel="stylesheet" href="../css/Ocorrencia.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/Dark.css">
    <script src="https://unpkg.com/lucide@latest"></script>

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

            <a class="nav-item" href="dashboard.php">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>

            <a class="nav-item" href="infracoes.php">
                <i data-lucide="alert-triangle"></i>
                <span>Infrações</span>
            </a>
            
            <a class="nav-item " href="gestaoUsu.php">
                <i data-lucide="users"></i>
                <span>Gestão</span>
            </a>

            <a class="nav-item" href="controleSala.php">
                <i data-lucide="school"></i>
                <span>Controle de Sala</span>
            </a>

            <a class="nav-item active" href="ocorrencias.php">
                <i data-lucide="file-text"></i>
                <span>Ocorrências</span>
            </a>

            <a class="nav-item" href="configuracoes.php">
                <i data-lucide="settings"></i>
                <span>Configurações</span>
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
                <button class="btn-close-card" onclick="sair()">Sair</button>
            </div>
        </header>

        <form class="form-container" id="incidentForm">

            <div class="form-section-title">
                🚨 Dados da Infração (Automático)
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Aluno Identificado</label>
                    <input type="text" class="form-input" id="studentNameInput" value="Aguardando seleção..." readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Motivo Principal</label>
                    <input type="text" class="form-input" id="reasonInput" value="..." readonly
                        style="color: var(--primary); font-weight: 700; background: #FEF2F2; border-color: #FCA5A5;">
                </div>

                <div class="form-group">
                    <label class="form-label">Data e Hora</label>
                    <input type="text" class="form-input" id="dateTimeInput" readonly>
                </div>
            </div>

            <div class="form-section-title">
                📝 Ação Tomada
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Tipo de Registro / Advertência</label>
                    <select class="form-select">
                        <option value="obs" selected>📌 Adicionar Observação (Padrão)</option>
                        <option value="adv_verbal">🗣️ Advertência Verbal</option>
                        <option value="adv_escrita">📄 Advertência Escrita</option>
                        <option value="suspensao">🚫 Suspensão</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Observações Adicionais</label>
                    <textarea class="form-textarea" placeholder="Descreva detalhes sobre a ocorrência..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Evidências</label>

                    <div class="photos-container" id="photoGallery">

                        <div class="photo-wrapper">
                            <img src="https://images.unsplash.com/photo-1544717305-2782549b5136?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80"
                                class="photo-preview" alt="Infração">
                            <div class="photo-badge">Detecção IA</div>
                        </div>

                        <input type="file" id="fileInput" hidden multiple accept="image/*">

                        <div class="btn-add-photo" onclick="document.getElementById('fileInput').click()">
                            <span>+</span>
                            <p>Adicionar</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="window.history.back()">Cancelar</button>
                <button type="submit" class="btn btn-submit">
                    Confirmar Ocorrência
                </button>
            </div>
        </form>

    </main>
    <script src="../js/ocorrencias.js" defer></script>
    <script>
        lucide.createIcons();
    </script>

</body>

</html>