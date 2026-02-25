<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI GUARD | Configurações</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../css/configuracoes.css">
    <link rel="stylesheet" href="../css/dashboard.css">
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

            <a class="nav-item" href="ocorrencias.php">
                <i data-lucide="file-text"></i>
                <span>Ocorrências</span>
            </a>

            <a class="nav-item active" href="configuracoes.php">
                <i data-lucide="settings"></i>
                <span>Configurações</span>
            </a>
        </nav>
    </aside>

    <main>
        <div id="view-config" class="view-section active">
            <header>
                <div class="page-title">
                    <h1>Configurações do Sistema</h1>
                    <p>Personalize a aparência e o comportamento da dashboard</p>
                </div>
            </header>

            <div class="config-grid">
                <div class="config-card">
                    <div class="config-header"><i data-lucide="monitor"></i> Interface</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Modo Noturno</span>
                            <small>Alternar tema escuro/claro</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-darkmode" onchange="toggleTheme()">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Exibir Porcentagem</span>
                            <small>Mostrar % nos cards do topo</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked id="toggle-percent"
                                onchange="toggleVisibility('.percent-wrapper')">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Exibir Status (Badges)</span>
                            <small>Mostrar/Ocultar fundo colorido</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked id="toggle-status" onchange="toggleStatus()">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-header"><i data-lucide="pie-chart"></i> Gráfico de EPIs</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Tipo de Gráfico</span>
                            <small>Altera o tipo do gráfico principal</small>
                        </div>
                        <select class="form-select" id="chartTypeSelect" onchange="handleChartTypeChange(this.value)">
                            <option value="bar">Barras</option>
                            <option value="line">Linha</option>
                            <option value="donut">Rosca (Fieldset)</option>
                        </select>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-header"><i data-lucide="mouse-pointer"></i> Interatividade</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Link nos Cards</span>
                            <small>Permitir clique para detalhes</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-link" onchange="toggleLinkAbility()">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-header"><i data-lucide="refresh-cw"></i> Atualização de Dados</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Auto-Refresh</span>
                            <small>Atualizar dados automaticamente</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="autoRefresh" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Intervalo</span>
                            <small>Frequência de busca</small>
                        </div>
                        <select class="form-select" id="refreshInterval" style="width: 140px;">
                            <option value="real">Tempo Real</option>
                            <option value="30">30 Segundos</option>
                            <option value="60">1 Minuto</option>
                            <option value="300">5 Minutos</option>
                        </select>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-header"><i data-lucide="bell"></i> Alertas e Sons</div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Alerta Sonoro</span>
                            <small>Tocar bip ao detectar infração</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="soundAlert">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="control-row">
                        <div class="control-label">
                            <span>Notificação</span>
                            <small>Enviar e-mail se infração crítica</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="emailAlert" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../js/chart-manager.js"></script>
    <script>
        // Inicializa ícones do Lucide
        lucide.createIcons();

        // Variável de estado para link habilitado
        let linksEnabled = false;

        // Função para manipular mudança no tipo de gráfico
        function handleChartTypeChange(value) {
            // Se for 'donut', mantém o comportamento original do fieldset
            if (value === 'donut') {
                console.log('Tipo donut selecionado (fieldset)');
                
                // Tenta encontrar os elementos do fieldset (se existirem)
                const chartDonut = document.getElementById('chart-donut');
                const chartBar = document.getElementById('chart-bar');
                const chartLine = document.getElementById('chart-line');
                
                if (chartDonut && chartBar && chartLine) {
                    chartDonut.style.display = 'flex';
                    chartBar.style.display = 'none';
                    chartLine.style.display = 'none';
                }
            } 
            // Se for 'bar' ou 'line', altera o gráfico principal do dashboard
            else if (value === 'bar' || value === 'line') {
                // Chama a função do chart-manager para mudar o tipo
                if (typeof window.changeMainChartType === 'function') {
                    window.changeMainChartType(value);
                    
                    // Tenta aplicar a mudança em outras abas do dashboard
                    try {
                        if (window.opener && !window.opener.closed) {
                            window.opener.changeMainChartType(value);
                        }
                    } catch(e) {
                        console.log('Não foi possível acessar outras abas');
                    }
                }
            }
            
            // Salva a preferência
            localStorage.setItem('preferredChartType', value);
        }

        // 1. Lógica do Clique no Card
        function toggleLinkAbility() {
            linksEnabled = document.getElementById('toggle-link').checked;
            localStorage.setItem('linksEnabled', linksEnabled);

            // Adiciona feedback visual (cursor pointer)
            try {
                if (window.opener && !window.opener.closed) {
                    const cards = window.opener.document.querySelectorAll('.card, .violation-card, .student-card');
                    cards.forEach(card => {
                        if (linksEnabled) card.classList.add('clickable');
                        else card.classList.remove('clickable');
                    });
                }
            } catch(e) {
                console.log('Não foi possível acessar outras abas');
            }
        }

        function handleCardClick(cardId) {
            if (linksEnabled) {
                alert(`Redirecionando para detalhes de: ${cardId}`);
            }
        }

        // 2. Dark Mode - FUNÇÃO CORRIGIDA
        function toggleTheme() {
            const isDark = document.getElementById('toggle-darkmode').checked;
            
            // Aplicar tema na página atual
            if (isDark) {
                document.body.setAttribute('data-theme', 'dark');
            } else {
                document.body.removeAttribute('data-theme');
            }
            
            // Salvar preferência
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            
            // Tentar aplicar em outras abas do dashboard
            try {
                if (window.opener && !window.opener.closed) {
                    if (isDark) {
                        window.opener.document.body.setAttribute('data-theme', 'dark');
                    } else {
                        window.opener.document.body.removeAttribute('data-theme');
                    }
                }
            } catch(e) {
                console.log('Não foi possível acessar outras abas');
            }
            
            // Disparar evento de storage para sincronizar outras abas
            localStorage.setItem('theme_trigger', Date.now());
        }

        // 3. Visibilidade de Porcentagem
        function toggleVisibility(selector) {
            const isChecked = document.getElementById('toggle-percent').checked;
            document.querySelectorAll(selector).forEach(el => {
                el.style.display = isChecked ? 'inline' : 'none';
            });
            
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.document.querySelectorAll(selector).forEach(el => {
                        el.style.display = isChecked ? 'inline' : 'none';
                    });
                }
            } catch(e) {}
        }

        // 4. Visibilidade de Status
        function toggleStatus() {
            const isChecked = document.getElementById('toggle-status').checked;
            document.querySelectorAll('.status-wrapper').forEach(el => {
                if (!isChecked) {
                    el.style.background = 'transparent';
                    el.style.border = 'none';
                    el.style.color = 'var(--text-muted)';
                    const svg = el.querySelector('svg');
                    if (svg) svg.style.display = 'none';
                } else {
                    el.style.background = '';
                    el.style.border = '';
                    el.style.color = '';
                    const svg = el.querySelector('svg');
                    if (svg) svg.style.display = 'inline';
                }
            });
            
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.document.querySelectorAll('.status-wrapper').forEach(el => {
                        if (!isChecked) {
                            el.style.background = 'transparent';
                            el.style.border = 'none';
                            el.style.color = 'var(--text-muted)';
                            const svg = el.querySelector('svg');
                            if (svg) svg.style.display = 'none';
                        } else {
                            el.style.background = '';
                            el.style.border = '';
                            el.style.color = '';
                            const svg = el.querySelector('svg');
                            if (svg) svg.style.display = 'inline';
                        }
                    });
                }
            } catch(e) {}
        }

        // 5. Troca de Cor Dinâmica
        function changeChartColor(color) {
            document.documentElement.style.setProperty('--chart-main-color', color);
            
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.document.documentElement.style.setProperty('--chart-main-color', color);
                }
            } catch(e) {}
            
            localStorage.setItem('chartColor', color);
        }

        // Carregar preferências salvas ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            // Tema
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                document.getElementById('toggle-darkmode').checked = true;
            }
            
            // Links nos cards
            const savedLinks = localStorage.getItem('linksEnabled') === 'true';
            document.getElementById('toggle-link').checked = savedLinks;
            
            // Tipo de gráfico preferido
            const preferredChartType = localStorage.getItem('preferredChartType') || 'bar';
            const chartSelect = document.getElementById('chartTypeSelect');
            if (chartSelect) {
                chartSelect.value = preferredChartType;
            }
            
            // Cor do gráfico
            const savedColor = localStorage.getItem('chartColor') || '#E30613';
            const colorPicker = document.getElementById('chartColorPicker');
            if (colorPicker) {
                colorPicker.value = savedColor;
                document.documentElement.style.setProperty('--chart-main-color', savedColor);
            }
            
            // Salvar cor quando mudar
            colorPicker.addEventListener('change', function(e) {
                localStorage.setItem('chartColor', e.target.value);
            });
        });
    </script>
</body>

</html>