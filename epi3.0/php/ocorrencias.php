<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Buscar lista de alunos para o select (opcional)
try {
    $stmtAlunos = $pdo->query("SELECT id, nome FROM alunos ORDER BY nome ASC");
    $alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);
    
    $stmtEpis = $pdo->query("SELECT id, nome FROM epis ORDER BY nome ASC");
    $epis = $stmtEpis->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $alunos = [];
    $epis = [];
}

// Pegar dados da URL se vier de alguma seleção
$alunoSelecionado = $_GET['aluno'] ?? $_GET['nome'] ?? '';
$epiSelecionado = $_GET['epi'] ?? '';
$dataSelecionada = $_GET['data'] ?? '';

// Dados do usuário logado
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$cargoUsuario = $_SESSION['usuario_cargo'] ?? 'Téc. Segurança';
$iniciais = strtoupper(substr($nomeUsuario, 0, 2));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Nova Ocorrência</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <link rel="stylesheet" href="../css/Ocorrencia.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos adicionais específicos */
        #notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 280px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 9999;
            pointer-events: none;
        }

        .toast {
            background: var(--bg-white);
            border-left: 4px solid var(--primary);
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.4);
            border-radius: 12px;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            pointer-events: auto;
            width: 100%;
            animation: dropIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            border: 1px solid var(--border);
        }

        .toast-icon {
            color: var(--primary);
            background: var(--status-critico-bg);
            padding: 6px;
            border-radius: 8px;
            display: flex;
        }

        .toast-icon svg {
            width: 20px;
            height: 20px;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-main);
        }

        .toast-message {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .toast-time {
            font-size: 0.65rem;
            font-weight: bold;
            color: var(--primary);
            margin-top: 4px;
            display: block;
        }

        @keyframes dropIn {
            0% { transform: translateY(-40px) scale(0.9); opacity: 0; }
            100% { transform: translateY(0) scale(1); opacity: 1; }
        }

        .toast.removing {
            animation: slideOutRight 0.3s ease forwards;
        }

        @keyframes slideOutRight {
            to { transform: translateX(120%); opacity: 0; }
        }

        .page-exit {
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }
        
        /* Ajuste para o select de alunos */
        .form-select option {
            background: var(--bg-white);
            color: var(--text-main);
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
            <a class="nav-item" href="dashboard.php">
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
                <h1>Nova Ocorrência</h1>
                <p>Registrar infração de EPI</p>
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
                    <div class="user-avatar"><?php echo $iniciais; ?></div>
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
                <button class="btn-close-card" onclick="sair()">Sair</button>
            </div>
        </header>

        <form class="form-container" id="incidentForm" method="POST" action="../php/salvar_ocorrencia.php" enctype="multipart/form-data">
            <div class="form-section-title">
                🚨 Dados da Infração
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Aluno</label>
                    <select class="form-select" id="studentSelect" name="aluno_id" required>
                        <option value="">Selecione um aluno...</option>
                        <?php foreach ($alunos as $aluno): ?>
                            <option value="<?php echo $aluno['id']; ?>" <?php echo ($alunoSelecionado == $aluno['nome'] || strpos($alunoSelecionado, $aluno['nome']) !== false) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($aluno['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">EPI não utilizado</label>
                    <select class="form-select" id="epiSelect" name="epi_id" required>
                        <option value="">Selecione o EPI...</option>
                        <?php foreach ($epis as $epi): ?>
                            <option value="<?php echo $epi['id']; ?>" <?php echo ($epiSelecionado == $epi['nome']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($epi['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Data e Hora</label>
                    <input type="datetime-local" class="form-input" id="dateTimeInput" name="data_hora" required value="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
            </div>

            <div class="form-section-title">
                📝 Ação Tomada
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Tipo de Registro / Advertência</label>
                    <select class="form-select" name="tipo_registro">
                        <option value="obs" selected>📌 Adicionar Observação (Padrão)</option>
                        <option value="adv_verbal">🗣️ Advertência Verbal</option>
                        <option value="adv_escrita">📄 Advertência Escrita</option>
                        <option value="suspensao">🚫 Suspensão</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Observações Adicionais</label>
                    <textarea class="form-textarea" name="observacoes" placeholder="Descreva detalhes sobre a ocorrência..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Evidências (fotos)</label>
                    <div class="photos-container" id="photoGallery">
                        <input type="file" id="fileInput" hidden multiple accept="image/*" name="fotos[]">
                        <div class="btn-add-photo" onclick="document.getElementById('fileInput').click()">
                            <span>+</span>
                            <p>Adicionar Fotos</p>
                        </div>
                    </div>
                    <small style="color: var(--text-muted);">Você pode selecionar múltiplas fotos</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="window.history.back()">Cancelar</button>
                <button type="submit" class="btn btn-submit" id="submitBtn">
                    Confirmar Ocorrência
                </button>
            </div>
        </form>
    </main>

    <div id="notification-container"></div>

    <script>
        lucide.createIcons();

        // Funções do Header
        function toggleInstructorCard() {
            const card = document.getElementById('instructorCard');
            card.classList.toggle('active');
        }

        function exportData() {
            alert("Exportando dados...");
        }

        function sair() {
            window.location.href = "../php/logout.php";
        }

        // Lógica de preview de fotos
        const fileInput = document.getElementById('fileInput');
        const gallery = document.getElementById('photoGallery');

        fileInput.addEventListener('change', function () {
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const div = document.createElement('div');
                        div.className = 'photo-wrapper-new';

                        const img = document.createElement('img');
                        img.src = e.target.result;

                        div.appendChild(img);

                        // Inserir antes do botão "+"
                        const addBtn = gallery.lastElementChild;
                        gallery.insertBefore(div, addBtn);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });

        // Submit do formulário
        document.getElementById('incidentForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = 'Salvando...';
            btn.disabled = true;
            
            // Simular envio (aqui você faria o fetch real para o PHP)
            setTimeout(() => {
                showNotification('Ocorrência registrada com sucesso!', 'success');
                setTimeout(() => {
                    window.location.href = 'infracoes.php';
                }, 1500);
            }, 800);
        });

        // Sistema de Notificações
        function showNotification(message, type = 'success') {
            const container = document.getElementById('notification-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            
            toast.innerHTML = `
                <div class="toast-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 16v-4M12 8h.01"></path>
                    </svg>
                </div>
                <div class="toast-content">
                    <span class="toast-title">${type === 'success' ? 'Sucesso' : 'Aviso'}</span>
                    <span class="toast-message">${message}</span>
                    <span class="toast-time">agora</span>
                </div>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Sistema de Tema
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
            }
        });

        window.toggleTheme = function() {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        // Transição de página nos links da sidebar
        document.querySelectorAll('a.nav-item').forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (!href || href === '#' || href.startsWith('javascript:')) return;
                
                // Não prevenir se for o link atual
                if (link.classList.contains('active')) return;
                
                e.preventDefault();
                document.body.classList.add('page-exit');
                setTimeout(() => { window.location.href = href; }, 300);
            });
        });

        // Preencher data/hora atual se veio vazio
        const dateInput = document.getElementById('dateTimeInput');
        if (dateInput && !dateInput.value) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            dateInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    </script>
</body>
</html>