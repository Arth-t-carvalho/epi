<?php
require_once __DIR__ . '/../config/database.php';

// ==========================================
// 1. LÓGICA DE FILTROS (BACK-END)
// ==========================================
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
            ev.imagem AS foto_caminho -- Buscando o campo 'imagem' da tabela 'evidencias'
        FROM ocorrencias o
        JOIN alunos a ON a.id = o.aluno_id
        LEFT JOIN cursos c ON c.id = a.curso_id
        JOIN epis e ON e.id = o.epi_id
        /* Relacionamento com a tabela evidencias pelo ocorrencia_id */
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
    <link rel="stylesheet" href="../css/infracoes.css">
    <style>
        /* MANTIDO SEU CSS ORIGINAL */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: none;
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex !important;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .full-image {
            width: 100%;
            max-height: 55vh;
            object-fit: contain;
            border-radius: 8px;
            background: #000;
        }

        .btn-assinar {
            background-color: #DC2626;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
            margin-top: 10px;
        }

        .btn-assinar:hover {
            background-color: #B91C1C;
        }

        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
            padding: 20px 0;
        }

        .violation-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            border: 1px solid #f0f0f0;
            transition: transform 0.2s;
        }

        .violation-card:hover {
            transform: translateY(-3px);
        }

        .card-image-wrapper {
            height: 140px;
            background: #f3f4f6;
            position: relative;
        }

        .card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-content {
            padding: 12px;
        }

        .violation-tag {
            background: #fee2e2;
            color: #dc2626;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }

        .infrator-name {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-top: 6px;
            color: #1f2937;
        }

        .timestamp {
            color: #6b7280;
            font-size: 11px;
            margin-top: 4px;
        }

        .header-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
        }
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
            <a class="nav-item" href="dashboard.php"> Dashboard</a>
            <a class="nav-item active" href="infracoes.php"> Infrações</a>
            <a class="nav-item" href="controleSala.php"> Controle de Sala</a>
            <a class="nav-item" href="ocorrencias.php">Ocorrencias</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <div>
                <div class="page-title">
                    <h1>Painel Geral</h1>
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
                                Apenas <?php echo htmlspecialchars($epi['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </header>

        <div class="gallery-container">
            <div class="grid-cards" id="cardsContainer">
                <?php if (empty($infracoes)): ?>
                    <p style="padding:20px; color:#666;">Nenhuma infração encontrada.</p>
                <?php else: ?>
                    <?php foreach ($infracoes as $item):
                        // BACK-END: TRATAMENTO DE IMAGEM
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
                            onclick="openModalPHP('<?php echo $imgSrc; ?>', '<?php echo $nomeSafe; ?>', '<?php echo $epiSafe; ?>', '<?php echo $horaF; ?>', '<?php echo $dataF; ?>')">
                            <div class="card-image-wrapper">
                                <img src="<?php echo $imgSrc; ?>" class="card-image" loading="lazy">
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
            <button onclick="forceClose()"
                style="position:absolute; right:10px; top:10px; border:none; background:transparent; font-size:24px; cursor:pointer;">&times;</button>
            <img src="" id="modalImg" class="full-image">
            <div style="text-align:left; width:100%;">
                <h3 id="modalName" style="margin: 5px 0 0 0; color:#1f2937;">Nome</h3>
                <p id="modalDesc" style="color:#dc2626; font-weight:bold; margin: 5px 0;">Infração</p>
                <p id="modalTime" style="color:#666; font-size:14px; margin:0;">Horário</p>
            </div>
            <button id="btnAssinar" class="btn-assinar">Assinar Ocorrência</button>
        </div>
    </div>

    <script>
        function openModalPHP(src, nome, epi, hora, dataCompleta) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImg');
            const modalName = document.getElementById('modalName');
            const modalDesc = document.getElementById('modalDesc');
            const modalTime = document.getElementById('modalTime');

            modalImg.src = src;
            modalName.innerText = nome;
            modalDesc.innerText = "Infração: " + epi;
            modalTime.innerText = "Horário: " + hora + " | Data: " + dataCompleta;

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
    </script>
</body>

</html>