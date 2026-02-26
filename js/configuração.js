        function toggleDarkMode() {
            const body = document.documentElement; // ou document.body

            if (body.getAttribute("data-theme") === "dark") {
                body.removeAttribute("data-theme");
            } else {
                body.setAttribute("data-theme", "dark");
            }
        }
        // Inicializa ícones do Lucide
        lucide.createIcons();

        // Variável de estado para link habilitado
        let linksEnabled = false;

        // 1. Lógica do Clique no Card (Com trava)
        function toggleLinkAbility() {
            linksEnabled = document.getElementById('toggle-link').checked;

            // Adiciona feedback visual (cursor pointer)
            const cards = document.querySelectorAll('.card');
            cards.forEach(c => {
                if (linksEnabled) c.classList.add('clickable');
                else c.classList.remove('clickable');
            });
        }

        function handleCardClick(cardId) {
            if (linksEnabled) {
                // Simula ir para outra página
                alert(`Redirecionando para detalhes de: ${cardId}`);
                // window.location.href = 'infracoes.php?filtro=' + cardId;
            }
        }

        // 2. Dark Mode
        function toggleTheme() {
            const isDark = document.getElementById('toggle-darkmode').checked;
            document.body.setAttribute('data-theme', isDark ? 'dark' : 'light');
        }

        // 3. Visibilidade de Porcentagem
        function toggleVisibility(selector) {
            const isChecked = document.getElementById('toggle-percent').checked;
            document.querySelectorAll(selector).forEach(el => {
                el.style.display = isChecked ? 'inline' : 'none';
            });
        }

        // 4. Visibilidade de Status (Badges inteiros)
        function toggleStatus() {
            const isChecked = document.getElementById('toggle-status').checked;
            document.querySelectorAll('.status-wrapper').forEach(el => {
                if (!isChecked) {
                    el.style.background = 'transparent';
                    el.style.border = 'none';
                    el.style.color = 'var(--text-muted)';
                    el.querySelector('svg').style.display = 'none';
                } else {
                    el.style.background = '';
                    el.style.border = '';
                    el.style.color = '';
                    el.querySelector('svg').style.display = 'inline';
                }
            });
        }

        // 5. Troca de Tipo de Gráfico (Fieldset)
        function changeChartType(type) {
            document.getElementById('chart-donut').style.display = 'none';
            document.getElementById('chart-bar').style.display = 'none';
            document.getElementById('chart-line').style.display = 'none';

            if (type === 'donut') document.getElementById('chart-donut').style.display = 'flex';
            if (type === 'bar') document.getElementById('chart-bar').style.display = 'flex';
            if (type === 'line') document.getElementById('chart-line').style.display = 'block';
        }

        // 6. Troca de Cor Dinâmica
        function changeChartColor(color) {
            document.documentElement.style.setProperty('--chart-main-color', color);
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
            } catch (e) {
                console.log('Não foi possível acessar outras abas');
            }

            // Disparar evento de storage para sincronizar outras abas
            localStorage.setItem('theme_trigger', Date.now());
        }

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
            document.getElementById('toggle-darkmode').checked = true;
        }