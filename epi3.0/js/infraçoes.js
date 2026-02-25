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
        return;;
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

    // Função para mostrar notificação de tema
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

    // Função para alternar links nos cards
    window.toggleLinkAbility = function() {
        const enabled = localStorage.getItem('linksEnabled') === 'true';
        const newState = !enabled;
        
        document.querySelectorAll('.card, .violation-card, .student-card').forEach(card => {
            if (newState) {
                card.classList.add('clickable');
            } else {
                card.classList.remove('clickable');
            }
        });
        
        localStorage.setItem('linksEnabled', newState);
    }
