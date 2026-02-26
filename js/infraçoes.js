// Verifica se o JS carregou
console.log("Infracoes.js carregado com sucesso.");

// Seleciona elementos do Modal
const modal = document.getElementById('imageModal');
const modalImg = document.getElementById('modalImg');
const modalName = document.getElementById('modalName');
const modalDesc = document.getElementById('modalDesc');
const modalTime = document.getElementById('modalTime');
const btnAssinar = document.getElementById('btnAssinar');

// --- FUNÇÃO PRINCIPAL ---
// Chamada pelo onclick do PHP
function openModalPHP(imgUrl, nome, epi, horaTexto, dataCompleta) {
    console.log("Tentando abrir modal:", nome); // Debug

    if (!modal) {
        console.error("Erro: Modal não encontrado no HTML!");
        return;
    }

    // 1. Preenche os dados visuais
    if (modalImg) modalImg.src = imgUrl;
    if (modalName) modalName.innerText = nome;
    if (modalDesc) modalDesc.innerText = "Falta de: " + epi;
    if (modalTime) modalTime.innerText = "Horário: " + horaTexto;

    // 2. Configura o botão vermelho
    if (btnAssinar) {
        // Remove eventos antigos clonando o botão (opcional, mas evita cliques duplos)
        const novoBotao = btnAssinar.cloneNode(true);
        btnAssinar.parentNode.replaceChild(novoBotao, btnAssinar);

        novoBotao.onclick = function () {
            const params = new URLSearchParams({
                aluno: nome,
                epi: epi,
                data: dataCompleta
            });
            window.location.href = `ocorrencias.php?${params.toString()}`;
        };
    }

    // 3. Mostra o modal
    modal.classList.add('active');
}

// Fecha ao clicar fora (no fundo escuro)
function closeModal(e) {
    if (e.target === modal) {
        modal.classList.remove('active');
    }
}

// Fecha ao clicar no X
function forceClose() {
    if (modal) modal.classList.remove('active');
}

// --- OUTROS ---
// Função de Transição de Página (Sidebar)
document.addEventListener("DOMContentLoaded", () => {
    const links = document.querySelectorAll('a.nav-item');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('javascript:')) return;
            e.preventDefault();
            document.body.classList.add('page-exit');
            setTimeout(() => { window.location.href = href; }, 300);
        });
    });
});

function openModalPHP(src, nome, epi, hora, dataCompleta) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');
    const modalName = document.getElementById('modalName');
    const modalDesc = document.getElementById('modalDesc');
    const modalTime = document.getElementById('modalTime');

    // Define os valores no modal
    modalImg.src = src;
    modalName.innerText = nome;
    modalDesc.innerText = "Infração: " + epi;
    modalTime.innerText = "Horário: " + hora + " | Data: " + dataCompleta;

    // Exibe o modal
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
    // Limpa a imagem para não aparecer a anterior ao abrir um novo card
    document.getElementById('modalImg').src = "";
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

        function forceClose() {
    const modal = document.getElementById('imageModal');
    modal.classList.remove('active');
    // Limpa a imagem para não aparecer a anterior ao abrir um novo card
    document.getElementById('modalImg').src = "";
}


function forceClose() {
    const modal = document.getElementById('imageModal');
    modal.classList.remove('active');
    // Limpa a imagem para não aparecer a anterior ao abrir um novo card
    document.getElementById('modalImg').src = "";
}


    // Verificar preferência salva ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
        }
        
        // Verificar estado dos links nos cards (se existirem)
        const linksEnabled = localStorage.getItem('linksEnabled') === 'true';
        if (linksEnabled) {
            document.querySelectorAll('.card, .violation-card, .student-card').forEach(c => {
                c.classList.add('clickable');
            });
        }
    });

        // Função para alternar tema (será chamada pela página de configurações)
    window.toggleTheme = function() {
        const isDark = document.body.getAttribute('data-theme') === 'dark';
        const newTheme = isDark ? 'light' : 'dark';
        
        document.body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Mostrar notificação de mudança de tema
        showThemeNotification(newTheme);
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

                // Aplica o tema imediatamente para evitar que a tela "pisque" em branco
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            // Como fallback para o seu CSS específico:
            document.addEventListener('DOMContentLoaded', () => {
                document.body.setAttribute('data-theme', 'dark');
            });
        }

        // Fica "escutando" mudanças de tema feitas em outras abas
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme' || e.key === 'theme_trigger') {
                const currentTheme = localStorage.getItem('theme');
                if (currentTheme === 'dark') {
                    document.body.setAttribute('data-theme', 'dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.body.removeAttribute('data-theme');
                    document.documentElement.removeAttribute('data-theme');
                }
            }
        });

           lucide.createIcons();

    // --- NOVA LÓGICA: Captura parâmetro da URL ---
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const alunoParaBuscar = urlParams.get('busca');
        const inputBusca = document.getElementById('searchInput');

        if (alunoParaBuscar && inputBusca) {
            inputBusca.value = alunoParaBuscar;
            // Dispara o evento de input para filtrar os cards imediatamente
            inputBusca.dispatchEvent(new Event('input'));
        }
    });

    // Lógica da Busca em Tempo Real (Já existente no seu código)
    document.getElementById('searchInput').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        const cards = document.querySelectorAll('.violation-card');

        cards.forEach(card => {
            const content = card.innerText.toLowerCase();
            if (content.includes(term)) {
                card.style.display = "block";
                setTimeout(() => { card.style.opacity = "1"; card.style.transform = "scale(1)"; }, 10);
            } else {
                card.style.opacity = "0";
                card.style.transform = "scale(0.95)";
                setTimeout(() => { if(card.style.opacity === "0") card.style.display = "none"; }, 300);
            }
        });
    });

    // Funções do Modal (Mantenha as suas)
    function openModalPHP(src, nome, epi, hora, dataCompleta) {
        document.getElementById('modalImg').src = src;
        document.getElementById('modalName').innerText = nome;
        document.getElementById('modalDesc').innerText = "Infração: " + epi;
        document.getElementById('modalTime').innerText = "Horário: " + hora + " | Data: " + dataCompleta;
        document.getElementById('imageModal').classList.add('active');
    }
    function closeModal(event) { if (event.target.id === 'imageModal') forceClose(); }
    function forceClose() {
        document.getElementById('imageModal').classList.remove('active');
        document.getElementById('modalImg').src = "";
    } 

            lucide.createIcons();

        // Lógica da Busca em Tempo Real com Animação
        document.getElementById('searchInput').addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const cards = document.querySelectorAll('.violation-card');

            cards.forEach(card => {
                const content = card.innerText.toLowerCase();
                if (content.includes(term)) {
                    card.style.display = "block";
                    setTimeout(() => { card.style.opacity = "1"; card.style.transform = "scale(1)"; }, 10);
                } else {
                    card.style.opacity = "0";
                    card.style.transform = "scale(0.95)";
                    setTimeout(() => { if(card.style.opacity === "0") card.style.display = "none"; }, 300);
                }
            });
        });

        // Funções do Modal
        function openModalPHP(src, nome, epi, hora, dataCompleta) {
            document.getElementById('modalImg').src = src;
            document.getElementById('modalName').innerText = nome;
            document.getElementById('modalDesc').innerText = "Infração: " + epi;
            document.getElementById('modalTime').innerText = "Horário: " + hora + " | Data: " + dataCompleta;
            document.getElementById('imageModal').classList.add('active');
        }

        function closeModal(event) { if (event.target.id === 'imageModal') forceClose(); }
        function forceClose() {
            document.getElementById('imageModal').classList.remove('active');
            document.getElementById('modalImg').src = "";
        }