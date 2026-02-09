<?php 
require_once "auth.php"; 
require_once "database.php"; 

// ==========================================
// 1. BUSCAR DADOS DO BANCO
// ==========================================
try {
    // Busca as últimas 50 ocorrências para não travar a tela
    $sql = "
        SELECT 
            o.id,
            o.data_hora,
            a.nome AS aluno_nome,
            a.curso AS aluno_curso,
            e.nome AS epi_nome,
            -- Tenta pegar a foto se existir, senão deixa NULL
            -- (Ajuste 'o.foto' para o nome real da sua coluna de imagem se for diferente)
            NULL AS foto_caminho 
        FROM ocorrencias o
        JOIN alunos a ON a.id = o.aluno_id
        JOIN epis e ON e.id = o.epi_id
        ORDER BY o.data_hora DESC
        LIMIT 50
    ";
    
    $stmt = $pdo->query($sql);
    $infracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Se der erro, cria um array vazio para não quebrar a tela
    $infracoes = [];
    $erro = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI Guard | Galeria de Infrações</title>
    <link rel="stylesheet" href="../css/infracoes.css">
    
    <style>
        /* Ajustes rápidos para os Cards */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
            border: 1px solid #f1f5f9;
        }
        .card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        .card-img-top {
            width: 100%;
            height: 160px;
            object-fit: cover;
            background: #f8fafc;
        }
        .card-body { padding: 12px; }
        .card-title { font-weight: 600; font-size: 14px; margin: 0 0 4px 0; color: #1e293b; }
        .card-text { font-size: 12px; color: #64748b; margin: 0; }
        .badge-epi {
            background-color: #fee2e2; color: #991b1b;
            padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: bold;
            display: inline-block; margin-top: 6px;
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
            <a class="nav-item" href="dashboard.php"> Dashboard</a>
            <a class="nav-item active" href="infracoes.php"> Infrações</a>
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
                <button class="btn-export" onclick="alert('Funcionalidade de exportar em desenvolvimento')">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                        <path d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z" />
                    </svg>
                    &nbsp;Exportar
                </button>

                <div class="user-profile-trigger" id="profileTrigger" onclick="toggleInstructorCard()">
                    <div class="user-info-mini">
                        <span class="user-name">João Silva</span>
                        <span class="user-role">Téc. Segurança</span>
                    </div>
                    <div class="user-avatar">JS</div>
                </div>
            </div>

            <div class="instructor-card" id="instructorCard" style="display: none;">
                <div style="margin-bottom: 20px;">
                    <h3>João Silva</h3>
                    <p style="color: #64748B; font-size: 13px;">ID: 9821-BR</p>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cargo</span>
                    <span class="detail-value">Supervisor</span>
                </div>
                <button class="btn-close-card" onclick="toggleInstructorCard()" style="margin-top:10px; width:100%;">Fechar</button>
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
                
                <?php if (empty($infracoes)): ?>
                    <p style="padding: 20px; color: #666;">Nenhuma infração registrada recentemente.</p>
                <?php else: ?>
                    
                    <?php foreach ($infracoes as $item): 
                        // Formatações
                        $dataObj = new DateTime($item['data_hora']);
                        $dataF = $dataObj->format('d/m/Y');
                        $horaF = $dataObj->format('H:i');
                        
                        // Imagem (Placeholder se não tiver)
                        // Você pode mudar essa URL para uma imagem padrão sua
                        $imgSrc = "https://via.placeholder.com/300x200/e2e8f0/94a3b8?text=Sem+Imagem";
                    ?>
                        <div class="card" onclick="openModalDetails(
                            '<?php echo addslashes($item['aluno_nome']); ?>', 
                            '<?php echo addslashes($item['epi_nome']); ?>', 
                            '<?php echo $horaF; ?>', 
                            '<?php echo $imgSrc; ?>'
                        )">
                            <img src="<?php echo $imgSrc; ?>" class="card-img-top" alt="Infração">
                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars($item['aluno_nome']); ?></h3>
                                <p class="card-text"><?php echo htmlspecialchars($item['aluno_curso']); ?></p>
                                <span class="badge-epi">Faltou: <?php echo htmlspecialchars($item['epi_nome']); ?></span>
                                <div style="margin-top: 8px; font-size: 11px; color: #94a3b8; text-align: right;">
                                    <?php echo $dataF . ' às ' . $horaF; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </div>
    </main>

    <div class="modal-overlay" id="imageModal" onclick="closeModal(event)" style="display: none;">
        <div class="modal-content">
            <button class="close-modal-btn" onclick="forceClose()">✕</button>
            <img src="" id="modalImg" class="full-image" style="max-width: 100%; border-radius: 8px;">
            <div class="modal-info-bar">
                <div>
                    <h3 id="modalName" style="color: #1F2937; margin-bottom: 4px;">Nome do Infrator</h3>
                    <p id="modalDesc" style="color: #E30613; font-weight: 600;">Infração: ...</p>
                </div>
                <div style="text-align: right;">
                    <p id="modalTime" style="color: #64748B; font-size: 14px;">Horário</p>
                    <p id="modalCam" style="color: #64748B; font-size: 12px;">Camera 01</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- Lógica do Modal ---
        function openModalDetails(nome, epi, hora, imgUrl) {
            const modal = document.getElementById('imageModal');
            
            // Preenche os dados
            document.getElementById('modalImg').src = imgUrl;
            document.getElementById('modalName').innerText = nome;
            document.getElementById('modalDesc').innerText = "Infração: Falta de " + epi;
            document.getElementById('modalTime').innerText = hora;
            
            // Abre o modal
            modal.style.display = 'flex';
        }

        function forceClose() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function closeModal(event) {
            // Fecha se clicar fora do conteúdo (no fundo escuro)
            if (event.target.id === 'imageModal') {
                forceClose();
            }
        }

        // --- Lógica do Dropdown do Usuário ---
        function toggleInstructorCard() {
            const card = document.getElementById('instructorCard');
            if (card.style.display === 'none' || card.style.display === '') {
                card.style.display = 'block';
                card.style.position = 'absolute';
                card.style.right = '20px';
                card.style.top = '70px';
                card.style.background = 'white';
                card.style.padding = '20px';
                card.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
                card.style.borderRadius = '10px';
                card.style.zIndex = '1000';
            } else {
                card.style.display = 'none';
            }
        }
    </script>
</body>
</html>