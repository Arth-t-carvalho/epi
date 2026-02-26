// ==========================================
// 1. VARIÁVEIS GLOBAIS E SELETORES
// ==========================================
let students = [];
const listContainer = document.getElementById('studentList');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const modal = document.getElementById('detailModal');

// ==========================================
// 2. BUSCA DE DADOS (API)
// ==========================================
            lucide.createIcons();
async function fetchStudents() {
    listContainer.innerHTML = '<div style="padding:20px; text-align:center;">🔄 Conectando ao sistema...</div>';
    
    // CAMINHO RELATIVO AUTOMÁTICO
    // "../" sai da pasta js
    // "php/" entra na pasta php
    const url = '../apis/controle.api.php'; 

    console.log("Tentando buscar em: " + url);

    try {
        const response = await fetch(url);

        // Se der erro 404, avisa que o arquivo PHP não existe ou está com nome errado
        if (response.status === 404) {
            throw new Error(`Arquivo API não encontrado. Verifique se o arquivo 'controle.api.php' existe dentro da pasta 'php'.`);
        }

        const text = await response.text();
        console.log("Resposta do Servidor:", text);

        try {
            const data = JSON.parse(text);
            
            if (data.error) {
                listContainer.innerHTML = `<div style="color:red; padding:20px; text-align:center">Erro do Banco: ${data.error}</div>`;
                return;
            }
            
            // SUCESSO!
            students = data;
            renderList();

        } catch (jsonError) {
            console.error("Erro ao ler JSON:", text);
            listContainer.innerHTML = `<div style="color:red; padding:20px;">Erro no PHP (veja o console F12).</div>`;
        }

    } catch (error) {
        console.error('Erro Fatal:', error);
        listContainer.innerHTML = `<div style="color:red; padding:20px; text-align:center;">❌ ${error.message}</div>`;
    }
}

// ==========================================
// 3. LÓGICA DE RENDERIZAÇÃO DA LISTA
// ==========================================

// Define o estado do aluno baseado nos dados do PHP
function getStudentState(student) {
    // O PHP retorna 'missing' como array (ex: ['Óculos']) se tiver risco hoje
    const hasRisk = student.missing && student.missing.length > 0;
    // O PHP retorna 'history' como true/false
    const hasHistory = student.history;

    if (hasRisk) return 'Risk'; // Prioridade: Risco Ativo
    if (hasHistory) return 'History'; // Secundário: Histórico
    return 'Safe'; // Padrão: Regular
}

function renderList(filterText = '', filterStatus = 'all') {
    // --- 1. CONFIGURAÇÃO VISUAL (Via JS para não mexer no CSS) ---
    
    // Define o Grid para ser COMPACTO. 
    // minmax(200px, 240px) = O card nunca fica menor que 200px e NUNCA maior que 240px (evita ficar gigante).
    listContainer.style.display = "grid";
    listContainer.style.gridTemplateColumns = "repeat(auto-fill, minmax(200px, 240px))";
    listContainer.style.gap = "12px"; 
    listContainer.style.justifyContent = "start"; // Alinha tudo à esquerda
    listContainer.innerHTML = '';

    // Filtra os alunos
    const filtered = students.filter(s => {
        const state = getStudentState(s);
        const matchesText = s.name.toLowerCase().includes(filterText.toLowerCase());
        
        let matchesStatus = false;
        if (filterStatus === 'all') matchesStatus = true;
        else if (filterStatus === 'Risk' && state === 'Risk') matchesStatus = true;
        else if (filterStatus === 'History' && state === 'History') matchesStatus = true;
        else if (filterStatus === 'Safe' && state === 'Safe') matchesStatus = true;
        
        return matchesText && matchesStatus;
    });

    // Se não achar ninguém
    if (filtered.length === 0) {
        listContainer.style.display = "flex"; // Flex para centralizar a mensagem
        listContainer.style.justifyContent = "center";
        listContainer.innerHTML = `
            <div style="text-align:center; padding: 40px; color: #94a3b8; animation: fadeIn 0.5s;">
                <p style="font-size: 14px;">Nenhum aluno encontrado.</p>
            </div>`;
        return;
    }

    // --- 2. RENDERIZAÇÃO DOS CARDS ---
    filtered.forEach((student, index) => {
        const state = getStudentState(student);
        const initials = student.name.substring(0, 2).toUpperCase();

        // Configuração de cores e ícones minimalistas
        let borderColor = 'transparent';
        let badgeBg = '#F3F4F6';
        let badgeColor = '#6B7280';
        let badgeText = 'Regular';
        let icon = '';

        if (state === 'Risk') {
            borderColor = '#EF4444'; // Vermelho
            badgeBg = '#FEF2F2';
            badgeColor = '#EF4444';
            badgeText = 'Faltante';
            icon = '⚠️';
        } else if (state === 'History') {
            borderColor = '#F59E0B'; // Amarelo
            badgeBg = '#FFFBEB';
            badgeColor = '#D97706';
            badgeText = 'Atenção';
            icon = '🔔';
        }

        const card = document.createElement('div');
        
        // Estilo Card Compacto e Limpo
        card.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            border: 1px solid #E2E8F0;
            border-left: 4px solid ${state === 'Safe' ? '#10B981' : borderColor};
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            
            /* ANIMAÇÃO DE ENTRADA (Pop In) */
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            opacity: 0;
            transform: scale(0.9);
            animation-delay: ${index * 0.05}s; /* Efeito cascata */
        `;

        // Efeito Hover
        card.onmouseenter = () => {
            card.style.transform = "translateY(-2px) scale(1.02)";
            card.style.boxShadow = "0 8px 16px -4px rgba(0,0,0,0.1)";
        };
        card.onmouseleave = () => {
            card.style.transform = "translateY(0) scale(1)";
            card.style.boxShadow = "0 2px 4px rgba(0,0,0,0.02)";
        };

        card.onclick = () => openModal(student);

        // HTML Interno (Minimalista)
        card.innerHTML = `
            <div style="
                width: 38px; height: 38px; 
                background: #F8FAFC; 
                border-radius: 50%; 
                display: flex; align-items: center; justify-content: center;
                font-size: 13px; font-weight: 700; color: #475569;
                border: 1px solid #E2E8F0; flex-shrink: 0;">
                ${initials}
            </div>
            
            <div style="flex: 1; min-width: 0;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 600; color: #1E293B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    ${student.name}
                </h3>
                <p style="margin: 2px 0 0 0; font-size: 11px; color: #94A3B8;">
                    ${student.course}
                </p>
            </div>

            ${state !== 'Safe' ? `
            <div style="
                font-size: 10px; font-weight: 700; 
                color: ${badgeColor}; background: ${badgeBg};
                padding: 4px 8px; border-radius: 6px;">
                ${icon}
            </div>` : ''}
        `;
        
        listContainer.appendChild(card);
    });

    // Injeta a animação no CSS da página (apenas uma vez) se não existir
    if (!document.getElementById('anim-style')) {
        const style = document.createElement('style');
        style.id = 'anim-style';
        style.innerHTML = `
            @keyframes popIn {
                0% { opacity: 0; transform: scale(0.8) translateY(10px); }
                100% { opacity: 1; transform: scale(1) translateY(0); }
            }
        `;
        document.head.appendChild(style);
    }
}
// ==========================================
// 4. LÓGICA DO MODAL
// ==========================================
// ==========================================
// FUNÇÃO DO MODAL (BLINDADA)
// ==========================================
function openModal(student) {
    console.log("Tentando abrir modal para:", student); // Vai aparecer no F12

    // 1. Verifica se o modal existe no HTML
    const modalElement = document.getElementById('detailModal');
    if (!modalElement) {
        alert("Erro: O HTML do modal (id='detailModal') não foi encontrado!");
        return;
    }

    // 2. Garante que 'missing' é um array (para não travar o JS)
    // Se vier nulo do PHP, transformamos em array vazio []
    const missingEpis = Array.isArray(student.missing) ? student.missing : [];

    // 3. Preenche os textos básicos
    const nomeEl = document.getElementById('modalName');
    const cursoEl = document.getElementById('modalCourse');
    
    if(nomeEl) nomeEl.innerText = student.name;
    if(cursoEl) cursoEl.innerText = `${student.course} • ID #${student.id}`;

    // 4. Preenche a lista de EPIs
    const epiContainer = document.getElementById('modalEpiList');
    if (epiContainer) {
        epiContainer.innerHTML = ''; // Limpa lista antiga
        
        // Lista de verificação
        const checkListEpis = ["Capacete", "Óculos"];
        
        checkListEpis.forEach(epi => {
            // Verifica se está na lista de faltantes
            // O 'toLowerCase' evita erro de maiúscula/minúscula
            const isMissing = missingEpis.some(m => 
                typeof m === 'string' && m.toLowerCase().includes(epi.toLowerCase())
            );
            
            const item = document.createElement('div');
            // Estilo direto para garantir que apareça bonito
            item.style.cssText = "display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eee;";
            
            if (isMissing) {
                item.innerHTML = `<span style="font-weight:bold; color:#444">${epi}</span> <span style="color:red; font-weight:bold">❌ Ausente</span>`;
            } else {
                item.innerHTML = `<span style="color:#666">${epi}</span> <span style="color:green">✅ Ok</span>`;
            }
            epiContainer.appendChild(item);
        });
    }

    // 5. Botões do Rodapé
    const footer = document.getElementById('modalFooterActions');
    if (footer) {
        footer.innerHTML = '';
        const btnAction = document.createElement('button');
        btnAction.innerText = '📝 Abrir Ocorrência';
        // Estilo do botão
        btnAction.style.cssText = "width:100%; padding:12px; background:#e11d48; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer; margin-top:15px;";
        
        btnAction.onclick = () => {
            // Redireciona
            window.location.href = `ocorrencias.php?id=${student.id}&nome=${encodeURIComponent(student.name)}`;
        };
        footer.appendChild(btnAction);
    }

    // 6. FINALMENTE: Mostra o modal
    // Tenta as duas formas mais comuns de mostrar modal
    modalElement.style.display = 'flex'; 
    modalElement.classList.add('open'); 
}

// Função para fechar
function closeModal() {
    const modalElement = document.getElementById('detailModal');
    if(modalElement) {
        modalElement.style.display = 'none';
        modalElement.classList.remove('open');
    }
}

// Fecha ao clicar fora (no fundo escuro)
window.onclick = function(event) {
    const modalElement = document.getElementById('detailModal');
    if (event.target == modalElement) {
        closeModal();
    }
}

// Fecha o modal
function closeModal() {
    modal.style.display = 'none';
}

// Fecha ao clicar fora
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

// ==========================================
// 5. INICIALIZAÇÃO E EVENTOS
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    fetchStudents();

    // Eventos de Filtro e Busca
    if(searchInput) {
        searchInput.addEventListener('keyup', (e) => renderList(e.target.value, statusFilter.value));
    }
    
    if(statusFilter) {
        statusFilter.addEventListener('change', (e) => renderList(searchInput.value, e.target.value));
    }
});

// Dropdown do usuário (Header)
function toggleInstructorCard() {
    const card = document.getElementById('instructorCard');
    if(card) {
        card.style.display = (card.style.display === 'block') ? 'none' : 'block';
    }
}
    function toggleInstructorCard() {
            const card = document.getElementById('instructorCard');
            card.style.display = (card.style.display === 'block') ? 'none' : 'block';
        }
        
        // Fechar modal
        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

             lucide.createIcons();

        // Função que popula o modal com os dados do aluno e os botões
        function exibirDetalhesAluno(aluno) {
            document.getElementById('modalName').innerText = aluno.nome;
            document.getElementById('modalCourse').innerText = aluno.curso;

            const footer = document.getElementById('modalFooterActions');

            // Inserindo os dois botões dinamicamente
            footer.innerHTML = `
                <button class="btn-view-infracoes" onclick="irParaInfracoes('${aluno.nome}')">
                    <i data-lucide="history"></i> Ver Infrações
                </button>
                <button class="btn-open-occurrence" onclick="abrirOcorrencia(${aluno.id})">
                    <i data-lucide="plus-circle"></i> Abrir Ocorrência
                </button>
            `;


            
            // Abre o modal
            const modal = document.getElementById('detailModal');
            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        // Redireciona para infrações passando o nome na URL
        function irParaInfracoes(nomeAluno) {
            const nomeCodificado = encodeURIComponent(nomeAluno);
            window.location.href = `infracoes.php?periodo=todos&busca=${nomeCodificado}`;
        }

        function abrirOcorrencia(id) {
            console.log("Iniciando ocorrência para o ID:", id);
            // window.location.href = `ocorrencias.php?novo=true&aluno_id=${id}`;
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

       // Função para alternar tema (será chamada pela página de configurações)
    window.toggleTheme = function() {
        const isDark = document.body.getAttribute('data-theme') === 'dark';
        const newTheme = isDark ? 'light' : 'dark';
        
        document.body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Mostrar notificação de mudança de tema
        showThemeNotification(newTheme);
    }

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
