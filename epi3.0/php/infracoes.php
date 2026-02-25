<?php
require_once __DIR__ . '/../config/database.php';

$filtroData = $_GET['periodo'] ?? ($_GET['filtro'] ?? 'hoje');
$filtroEpi = isset($_GET['epi']) ? $_GET['epi'] : '';

try {
    $stmtEpis = $pdo->query("SELECT id, nome FROM epis ORDER BY nome ASC");
    $listaEpis = $stmtEpis->fetchAll(PDO::FETCH_ASSOC);

    $sql = "
        SELECT 
            o.id, 
            o.data_hora,
            a.nome AS aluno_nome,
            c.nome AS aluno_curso,
            e.nome AS epi_nome,
            ev.imagem AS foto_caminho
        FROM ocorrencias o
        JOIN alunos a ON a.id = o.aluno_id
        LEFT JOIN cursos c ON c.id = a.curso_id
        JOIN epis e ON e.id = o.epi_id
        LEFT JOIN evidencias ev ON ev.ocorrencia_id = o.id 
        WHERE 1=1
    ";
    if ($filtroData == 'hoje' || $filtroData == 'dia') {
        $sql .= " AND DATE(o.data_hora) = CURDATE()";
    } elseif ($filtroData == '7dias' || $filtroData == 'semana') {
        $sql .= " AND o.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($filtroData == '30dias' || $filtroData == 'mes') {
        $sql .= " AND o.data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }

    if (!empty($filtroEpi)) {
        $sql .= " AND o.epi_id = :epi_id";
    }

    $sql .= " ORDER BY o.data_hora DESC LIMIT 100";

    $stmt = $pdo->prepare($sql);
    if (!empty($filtroEpi)) {
        $stmt->bindValue(':epi_id', $filtroEpi);
    }
    $stmt->execute();
    $infracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $infracoes = [];
    $listaEpis = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Infrações</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <link rel="stylesheet" href="../css/infracoes.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <aside class="sidebar">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3"
                style="filter: drop-shadow(0 2px 4px rgba(227, 6, 19, 0.3));">
                <circle cx="12" cy="12" r="10"/>
            </svg>
            &nbsp; EPI <span>GUARD</span>
        </div>

        <nav class="nav-menu">
            <a class="nav-item" href="dashboard.php">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a class="nav-item active" href="infracoes.php">
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
        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <div>
                <div class="page-title">
                    <h1>Infrações Registradas</h1>
                    <p>Monitoramento de Segurança</p>
                </div>
                <form method="GET" class="header-controls">
                    <select name="periodo" class="filter-select" onchange="this.form.submit()">
                        <option value="hoje" <?php echo ($filtroData == 'hoje' || $filtroData == 'dia') ? 'selected' : ''; ?>>Hoje</option>
                        <option value="7dias" <?php echo ($filtroData == '7dias' || $filtroData == 'semana') ? 'selected' : ''; ?>>Últimos 7 dias</option>
                        <option value="30dias" <?php echo ($filtroData == '30dias' || $filtroData == 'mes') ? 'selected' : ''; ?>>Últimos 30 dias</option>
                        <option value="todos" <?php echo $filtroData == 'todos' ? 'selected' : ''; ?>>Tudo</option>
                    </select>

                    <select name="epi" class="filter-select" onchange="this.form.submit()">
                        <option value="">Todos os EPIs</option>
                        <?php foreach ($listaEpis as $epi): ?>
                            <option value="<?php echo $epi['id']; ?>" <?php echo $filtroEpi == $epi['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($epi['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </header>

        <div class="gallery-container">
            <div class="grid-cards" id="cardsContainer">
                <?php if (empty($infracoes)): ?>
                    <p style="padding:20px; color: var(--text-muted);">Nenhuma infração encontrada.</p>
                <?php else: ?>
                    <?php foreach ($infracoes as $item):
                        $nomeArquivo = $item['foto_caminho'];
                        $diretorioUploads = "../uploads/";
                        $caminhoFisico = __DIR__ . "/../uploads/" . $nomeArquivo;

                        if (!empty($nomeArquivo) && file_exists($caminhoFisico)) {
                            $imgSrc = $diretorioUploads . $nomeArquivo;
                        } else {
                            $imgSrc = "https://via.placeholder.com/400x300?text=Sem+Imagem";
                        }

                        $nomeSafe = htmlspecialchars($item['aluno_nome'] ?? 'Desconhecido', ENT_QUOTES);
                        $epiSafe = htmlspecialchars($item['epi_nome'] ?? 'EPI', ENT_QUOTES);
                        $setorSafe = htmlspecialchars($item['aluno_curso'] ?? 'Geral', ENT_QUOTES);
                        $dataObj = new DateTime($item['data_hora']);
                        $horaF = $dataObj->format('H:i');
                        $dataF = $dataObj->format('d/m/Y');
                        ?>

                        <div class="violation-card"
                            onclick="openModal('<?php echo $imgSrc; ?>', '<?php echo $nomeSafe; ?>', '<?php echo $epiSafe; ?>', '<?php echo $horaF; ?>', '<?php echo $dataF; ?>')">
                            <div class="card-image-wrapper">
                                <img src="<?php echo $imgSrc; ?>" class="card-image" loading="lazy">
                                <div class="card-overlay">
                                    <span class="zoom-icon">🔍</span>
                                </div>
                            </div>
                            <div class="card-content">
                                <span class="violation-tag"><?php echo $epiSafe; ?></span>
                                <span class="infrator-name"><?php echo $nomeSafe; ?></span>
                                <div class="timestamp"><?php echo $horaF; ?> • <?php echo $setorSafe; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="imageModal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="close-modal-btn" onclick="forceClose()">✕</button>
            <img src="" id="modalImg" class="full-image">
            <div style="text-align:left; width:100%;">
                <h3 id="modalName" style="margin: 10px 0 0 0; color: var(--text-main);">Nome</h3>
                <p id="modalDesc" style="color: var(--danger); font-weight:bold; margin: 5px 0;">Infração</p>
                <p id="modalTime" style="color: var(--text-muted); font-size:14px; margin:0;">Horário</p>
            </div>
            <button id="btnAssinar" class="btn-assinar">Assinar Ocorrência</button>
        </div>
    </div>

    <div id="notification-container"></div>

    <script>
        lucide.createIcons();

        // Funções do Modal
        function openModal(src, nome, epi, hora, dataCompleta) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImg');
            const modalName = document.getElementById('modalName');
            const modalDesc = document.getElementById('modalDesc');
            const modalTime = document.getElementById('modalTime');
            const btnAssinar = document.getElementById('btnAssinar');

            modalImg.src = src;
            modalName.innerText = nome;
            modalDesc.innerText = "Infração: " + epi;
            modalTime.innerText = "Horário: " + hora + " | Data: " + dataCompleta;

            btnAssinar.onclick = function() {
                const params = new URLSearchParams({
                    aluno: nome,
                    epi: epi,
                    data: dataCompleta
                });
                window.location.href = `ocorrencias.php?${params.toString()}`;
            };

            modal.classList.add('active');
        }

        function closeModal(event) {
            if (event.target.id === 'imageModal') {
                forceClose();
            }
        }

        function forceClose() {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('active');
            document.getElementById('modalImg').src = "";
        }

        // Sistema de Tema
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
            }
            
            const linksEnabled = localStorage.getItem('linksEnabled') === 'true';
            if (linksEnabled) {
                document.querySelectorAll('.violation-card').forEach(c => {
                    c.classList.add('clickable');
                });
            }
        });

        window.toggleTheme = function() {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            showThemeNotification(newTheme);
        }

        function showThemeNotification(theme) {
            const container = document.getElementById('notification-container');
            if (!container) return;
            
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
                    <span class="toast-title">Tema ${theme === 'dark' ? 'escuro' : 'claro'} ativado</span>
                    <span class="toast-message">Aparência alterada com sucesso</span>
                    <span class="toast-time">agora</span>
                </div>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        window.toggleLinkAbility = function() {
            const enabled = localStorage.getItem('linksEnabled') === 'true';
            const newState = !enabled;
            
            document.querySelectorAll('.violation-card').forEach(card => {
                if (newState) {
                    card.classList.add('clickable');
                } else {
                    card.classList.remove('clickable');
                }
            });
            
            localStorage.setItem('linksEnabled', newState);
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
    </script>
</body>
</html>