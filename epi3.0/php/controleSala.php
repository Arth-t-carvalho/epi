<?php
require_once __DIR__ . '/../config/database.php';
// Se usar sistema de login, mantenha a linha abaixo
// require_once __DIR__ . '/../config/auth.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Instrutor';
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
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Container dos botões no rodapé do modal */
        #modalFooterActions {
            display: flex;
            gap: 12px;
            padding: 20px;
            border-top: 1px solid #eee;
            background: #fff;
            border-radius: 0 0 12px 12px;
        }

        /* Botão Ver Infrações (Estilo Secundário/Clean) */
        .btn-view-infracoes {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-view-infracoes:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        /* Botão Abrir Ocorrência (Estilo Primário/Alerta) */
        .btn-open-occurrence {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #DC2626;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-open-occurrence:hover {
            background-color: #B91C1C;
            transform: translateY(-1px);
        }

        /* Ajuste do Modal Overlay para alinhar ao centro */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .modal-overlay.active {
            display: flex;
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
            <a class="nav-item" href="dashboard.php"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
            <a class="nav-item" href="infracoes.php"><i data-lucide="alert-triangle"></i><span>Infrações</span></a>
            <a class="nav-item active" href="controleSala.php"><i data-lucide="users"></i><span>Controle de Sala</span></a>
            <a class="nav-item" href="ocorrencias.php"><i data-lucide="file-text"></i><span>Ocorrências</span></a>
            <a class="nav-item" href="configuracoes.php"><i data-lucide="settings"></i><span>Configurações</span></a>
            <a class="nav-item" href="monitoramento.php"><i data-lucide="monitor"></i><span>Monitoramento</span></a>
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
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color:green; font-weight:bold;">Online</span>
                </div>
                <hr style="margin: 15px 0; border:0; border-top:1px solid #eee;">
                <button class="btn-close-card" style="width:100%; padding:8px;"
                    onclick="location.href='../php/logout.php'">Sair</button>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="content-card">
                <div class="controls-bar">
                    <div class="search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" id="searchInput" placeholder="Buscar aluno...">
                    </div>
                    <select class="filter-select" id="statusFilter" name="statusFilter">
                        <option value="all">Todos os status</option>
                        <option value="Risk">🔴 Risco Ativo</option>
                        <option value="Recurrent">🟠 Reincidente</option>
                        <option value="History">🟡 Histórico</option>
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
                <div>
                    <h2 id="modalName" style="margin:0; font-size:18px;">Nome do Aluno</h2>
                    <small id="modalCourse" style="color:#666;">Curso...</small>
                </div>
                <button class="close-btn" onclick="closeModal()">✕</button>
            </div>

            <div style="padding: 20px;">
                <h4 style="margin-bottom: 10px; color: #333;">Checklist de EPIs:</h4>
                <div id="modalEpiList" class="epi-list"></div>
            </div>

            <div id="modalFooterActions"></div>
        </div>
    </div>

    <script src="../js/controleSala.js"></script>
    <script>
        lucide.createIcons();

        // Função que popula o modal com os dados do aluno e os botões
        function exibirDetalhesAluno(aluno) {
            document.getElementById('modalName').innerText = aluno.nome;
            document.getElementById('modalCourse').innerText = aluno.curso;

            const footer = document.getElementById('modalFooterActions');

            // Inserindo os dois botões dinamicamente
            footer.innerHTML = `
                <button class="btn-view-infracoes" onclick="irParaInfracoes('${aluno.nome}')">
                    <i data-lucide="history"></i> Ver Infrações
                </button>
                <button class="btn-open-occurrence" onclick="abrirOcorrencia(${aluno.id})">
                    <i data-lucide="plus-circle"></i> Abrir Ocorrência
                </button>
            `;

            lucide.createIcons();
            
            // Abre o modal
            const modal = document.getElementById('detailModal');
            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        // Redireciona para infrações passando o nome na URL
        function irParaInfracoes(nomeAluno) {
            const nomeCodificado = encodeURIComponent(nomeAluno);
            window.location.href = `infracoes.php?periodo=todos&busca=${nomeCodificado}`;
        }

        function abrirOcorrencia(id) {
            console.log("Iniciando ocorrência para o ID:", id);
            // window.location.href = `ocorrencias.php?novo=true&aluno_id=${id}`;
        }
    </script>
</body>
</html>