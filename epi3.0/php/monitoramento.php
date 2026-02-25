<?php
// Mantendo a estrutura de autenticação e banco de dados
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Monitoramento</title>
    <link rel="stylesheet" href="../css/Ocorrencia.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* ==========================================
           TEMA CLARO - ESTILO APPLE (CLEAN & MINIMAL)
           ========================================== */
        
        .meet-wrapper {
            background-color: #f5f5f7; /* Fundo cinza super claro (padrão Apple) */
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1d1d1f; /* Texto escuro suave */
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            height: calc(100vh - 120px);
            margin-top: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); /* Sombra difusa e suave */
            border: 1px solid rgba(255, 255, 255, 0.6);
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .meet-header-info {
            height: 54px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            font-size: 14px;
            font-weight: 500;
            background: #ffffff;
            border-bottom: 1px solid #e5e5ea;
        }

        .meet-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #515154;
        }

        .meet-main {
            flex: 1;
            display: flex;
            padding: 16px;
            gap: 16px;
            overflow: hidden;
            background-color: #f5f5f7;
            transition: all 0.3s ease;
        }

        /* Área de Vídeo */
        .meet-presentation {
            flex: 3;
            background: #ffffff;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            transition: flex 0.4s ease;
        }

        .editor-header {
            height: 40px;
            background: transparent;
            display: flex;
            align-items: center;
            padding: 0 20px;
            font-size: 13px;
            font-weight: 600;
            color: #86868b;
            border-bottom: 1px solid #f0f0f2;
        }

        .editor-content {
            flex: 1;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000; /* Fundo do vídeo se mantém preto para contraste da imagem */
            overflow: hidden;
        }

        .editor-content img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Painel Lateral - Chat e Logs (Expandido) */
        .meet-right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 400px;
            transition: all 0.4s ease;
        }

        .chat-panel {
            flex: 1;
            background: #ffffff;
            border-radius: 20px;
            color: #1d1d1f;
            display: flex;
            flex-direction: column;
            padding: 24px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        }

        .chat-header {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-subtitle {
            font-size: 12px;
            color: #34c759; /* Verde estilo iOS */
            font-weight: 500;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .chat-subtitle::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #34c759;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 199, 89, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(52, 199, 89, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 199, 89, 0); }
        }

        .chat-logs {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            overflow-y: auto;
        }

        .chat-msg {
            background: #f5f5f7;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.5;
            color: #1d1d1f;
            border: 1px solid #e5e5ea;
        }

        .msg-alert {
            border-left: 4px solid #ff3b30; /* Vermelho alerta Apple */
        }

        /* Controles Inferiores */
        .meet-footer {
            height: 88px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            background: #ffffff;
            border-top: 1px solid #e5e5ea;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }

        .meeting-details {
            font-size: 15px;
            font-weight: 500;
            color: #515154;
        }

        .controls {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .btn-meet {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 1px solid #e5e5ea;
            background: #ffffff;
            color: #1d1d1f;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }

        .btn-meet:hover {
            background: #f5f5f7;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }

        .btn-end {
            background: #ff3b30; /* Vermelho vibrante */
            color: white;
            width: 72px;
            border-radius: 24px;
            border: none;
        }

        .btn-end:hover {
            background: #d32f2f;
        }

        /* Ferramentas do lado direito do Footer */
        .right-tools {
            display: flex;
            gap: 20px;
            color: #515154;
            align-items: center;
        }

        .right-tools i {
            cursor: pointer;
            transition: color 0.2s;
        }

        .right-tools i:hover {
            color: #007aff; /* Azul Apple */
        }

        /* Menu de Opções de Layout (Dropdown) */
        .layout-menu-container {
            position: relative;
        }

        .layout-dropdown {
            position: absolute;
            bottom: 60px;
            left: 50%;
            transform: translateX(-50%) translateY(10px);
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            padding: 8px;
            width: 200px;
            display: flex;
            flex-direction: column;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            border: 1px solid #e5e5ea;
            z-index: 100;
        }

        .layout-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        .layout-option {
            padding: 12px 16px;
            font-size: 14px;
            color: #1d1d1f;
            cursor: pointer;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s;
        }

        .layout-option:hover {
            background: #f5f5f7;
        }

        .layout-option.selected {
            color: #007aff;
            font-weight: 500;
            background: #f0f8ff;
        }

        /* Estilo Dinâmico via JS (Modo Expandido) */
        .meet-wrapper.layout-expanded .meet-right-panel {
            display: none; /* Esconde o log de sistema */
        }
        .meet-wrapper.layout-expanded .meet-presentation {
            flex: 1; /* Câmera ocupa a tela toda */
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
            <a class="nav-item" href="monitoramento.php">
                <i data-lucide="monitor"></i>
                <span>Monitoramento</span>
            </a>

        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <div class="page-title">
                <h1>Monitoramento de Laboratório</h1>
                <p>Laboratório B • Câmera Ao Vivo</p>
            </div>
            <div class="header-actions">
                <div class="user-profile-trigger" id="profileTrigger" onclick="toggleInstructorCard()">
                    <div class="user-info-mini">
                        <span class="user-name">João Silva</span>
                        <span class="user-role">Téc. Segurança</span>
                    </div>
                    <div class="user-avatar">JS</div>
                </div>
            </div>
            </header>

        <div class="meet-wrapper" id="meetWrapper">
            <div class="meet-header-info">
                <div class="meet-user-info">
                    <i data-lucide="shield-check" style="color: #34c759; width: 18px;"></i>
                    Visualizando como: <strong>Professor Logado</strong>
                </div>
            </div>

            <div class="meet-main">
                <section class="meet-presentation">
                    <div class="editor-header">
                        Câmera Principal - Lab B
                    </div>
                    <div class="editor-content">
                        <img src="http://localhost:5000/video_feed" alt="Câmera do Python Ao Vivo">
                    </div>
                </section>

                <aside class="meet-right-panel">
                    <div class="chat-panel">
                        <div class="chat-header">
                            Detalhes do Sistema
                            <i data-lucide="info" size="18" style="color: #86868b; cursor:pointer"></i>
                        </div>
                        <div class="chat-subtitle">
                            Monitoramento IA Contínuo Ativado
                        </div>
                        
                        <div class="chat-logs">
                            <div class="chat-msg">
                                <strong>Log de Inicialização</strong><br>
                                <span style="color:#86868b; font-size:11px;">14:50 - Sistema EPI Guard online. Câmeras calibradas.</span>
                            </div>
                            <div class="chat-msg msg-alert">
                                <strong>⚠️ ALERTA DE INFRAÇÃO</strong><br>
                                Aluno detectado sem óculos de proteção na bancada 3.<br>
                                <span style="color:#86868b; font-size:11px;">14:55 - Captura enviada ao dashboard.</span>
                            </div>
                            <div class="chat-msg">
                                <strong>Status Operacional</strong><br>
                                <span style="color:#86868b; font-size:11px;">14:58 - Varredura de ambiente concluída. 14 alunos seguros.</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <footer class="meet-footer">
                <div class="meeting-details">14:58 | lab-b-cam-01</div>

                <div class="controls">
                    <button class="btn-meet"><i data-lucide="mic-off" size="20"></i></button>
                    <button class="btn-meet"><i data-lucide="video" size="20"></i></button>
                    <button class="btn-meet"><i data-lucide="volume-2" size="20"></i></button>
                    <button class="btn-meet"><i data-lucide="monitor-up" size="20"></i></button>
                    
                    <div class="layout-menu-container">
                        <button class="btn-meet" onclick="toggleLayoutMenu()"><i data-lucide="more-vertical" size="20"></i></button>
                        <div class="layout-dropdown" id="layoutDropdown">
                            <div class="layout-option selected" id="opt-default" onclick="setLayout('default')">
                                <i data-lucide="sidebar" size="16"></i> Modo Padrão
                            </div>
                            <div class="layout-option" id="opt-expanded" onclick="setLayout('expanded')">
                                <i data-lucide="maximize" size="16"></i> Câmera Expandida
                            </div>
                        </div>
                    </div>

                    <button class="btn-meet btn-end"><i data-lucide="phone-off" size="20"></i></button>
                </div>

                <div class="right-tools">
                    <i data-lucide="settings" size="20"></i>
                    <i data-lucide="activity" size="20"></i>
                </div>
            </footer>
        </div>

    </main>

    <script src="../js/ocorrencias.js" defer></script>
    <script>
        // Inicializa os ícones do Lucide
        lucide.createIcons();

        // Controle do Dropdown de Layout
        function toggleLayoutMenu() {
            const dropdown = document.getElementById('layoutDropdown');
            dropdown.classList.toggle('active');
        }

        // Função para alterar o Layout (Padrão vs Expandido)
        function setLayout(mode) {
            const wrapper = document.getElementById('meetWrapper');
            const optDefault = document.getElementById('opt-default');
            const optExpanded = document.getElementById('opt-expanded');

            if (mode === 'expanded') {
                wrapper.classList.add('layout-expanded');
                optExpanded.classList.add('selected');
                optDefault.classList.remove('selected');
            } else {
                wrapper.classList.remove('layout-expanded');
                optDefault.classList.add('selected');
                optExpanded.classList.remove('selected');
            }
            
            // Fecha o menu após clicar
            document.getElementById('layoutDropdown').classList.remove('active');
        }

        // Fecha o dropdown se clicar fora dele
        window.addEventListener('click', function(e) {
            const container = document.querySelector('.layout-menu-container');
            if (container && !container.contains(e.target)) {
                document.getElementById('layoutDropdown').classList.remove('active');
            }
        });
    </script>

</body>
</html>