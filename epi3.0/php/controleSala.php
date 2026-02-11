<?php
require_once __DIR__ . '/../config/database.php';
// Se usar sistema de login, mantenha a linha abaixo
// require_once __DIR__ . '/../config/auth.php';

// Simulação de sessão para teste (remova se já tiver login real)
if (session_status() === PHP_SESSION_NONE) session_start();
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
    
    <style>
        /* CSS Rápido para garantir funcionamento básico do modal e layout */
        .modal-overlay {
            display: none; /* Oculto por padrão */
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1000;
            justify-content: center; align-items: center;
        }
        .modal-content {
            background: white; width: 90%; max-width: 500px;
            border-radius: 12px; overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .modal-header {
            padding: 15px 20px; border-bottom: 1px solid #eee;
            display: flex; justify-content: space-between; align-items: center;
            background: #f9fafb;
        }
        .close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; }
        .instructor-card { display: none; position: absolute; right: 20px; top: 70px; background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 100; }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3">
                <circle cx="12" cy="12" r="10" />
            </svg>
            &nbsp; EPI <span>GUARD</span>
        </div>
        <nav class="nav-menu">
            <a class="nav-item" href="dashboard.php">Dashboard</a>
            <a class="nav-item" href="infracoes.php">Infrações</a>
            <a class="nav-item active" href="controleSala.php">Controle de Sala</a>
            <a class="nav-item" href="ocorrencias.php">Ocorrências</a>
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
                <button class="btn-close-card" style="width:100%; padding:8px;" onclick="location.href='../php/logout.php'">Sair</button>
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
                <div>
                    <h2 id="modalName" style="margin:0; font-size:18px;">Nome do Aluno</h2>
                    <small id="modalCourse" style="color:#666;">Curso...</small>
                </div>
                <button class="close-btn" onclick="closeModal()">✕</button>
            </div>

            <div style="padding: 20px;">
                <h4 style="margin-bottom: 10px; color: #333;">Checklist de EPIs:</h4>
                <div id="modalEpiList" class="epi-list">
                    </div>
            </div>

            <div id="modalFooterActions" style="padding: 20px; border-top: 1px solid #eee;">
                </div>
        </div>
    </div>

    <script src="../js/controleSala.js"></script>
</body>
</html>