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
async function fetchStudents() {
    listContainer.innerHTML = '<div style="padding:20px; text-align:center;">🔄 Conectando ao sistema...</div>';
    
    // CAMINHO RELATIVO AUTOMÁTICO
    // "../" sai da pasta js
    // "php/" entra na pasta php
    const url = '../php/controle.api.php'; 

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
    listContainer.innerHTML = '';

    // Filtra os alunos baseado na busca e no select
    const filtered = students.filter(s => {
        const state = getStudentState(s);
        const matchesText = s.name.toLowerCase().includes(filterText.toLowerCase());
        
        // Lógica do filtro de status
        let matchesStatus = false;
        if (filterStatus === 'all') matchesStatus = true;
        else if (filterStatus === 'Risk' && state === 'Risk') matchesStatus = true;
        else if (filterStatus === 'History' && state === 'History') matchesStatus = true;
        else if (filterStatus === 'Safe' && state === 'Safe') matchesStatus = true;
        
        return matchesText && matchesStatus;
    });

    if (filtered.length === 0) {
        listContainer.innerHTML = `<div style="padding:20px; color:#999; text-align:center;">Nenhum aluno encontrado.</div>`;
        return;
    }

    filtered.forEach((student, index) => {
        const state = getStudentState(student);
        const initials = student.name.substring(0, 2).toUpperCase();

        let cardClass = '';
        let badgeHtml = '';

        // Define estilos visuais baseados no estado
        switch (state) {
            case 'Risk':
                cardClass = 'is-risk'; // Define borda vermelha no CSS
                badgeHtml = '<div class="status-pill status-risk" style="background:#FEF2F2; color:#DC2626; padding:4px 8px; border-radius:12px; font-size:12px; font-weight:bold;">⚠️ SEM EPI</div>';
                break;
            case 'History':
                cardClass = 'is-history'; // Define borda amarela no CSS
                badgeHtml = '<div class="status-pill status-history" style="background:#FFFBEB; color:#D97706; padding:4px 8px; border-radius:12px; font-size:12px; font-weight:bold;">🔔 HISTÓRICO</div>';
                break;
            default:
                badgeHtml = '<div class="status-pill status-ok" style="background:#ECFDF5; color:#059669; padding:4px 8px; border-radius:12px; font-size:12px; font-weight:bold;">REGULAR</div>';
        }

        const card = document.createElement('div');
        // Adicione a classe .student-card no seu CSS
        card.className = `student-card ${cardClass}`;
        card.style.cssText = `
            background: white; 
            padding: 15px; 
            border-radius: 12px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 10px; 
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            animation: fadeIn 0.3s ease forwards;
            animation-delay: ${index * 0.05}s;
        `;
        
        // Efeito hover simples via JS se nao tiver no CSS
        card.onmouseover = () => card.style.transform = 'translateY(-2px)';
        card.onmouseout = () => card.style.transform = 'translateY(0)';
        
        card.onclick = () => openModal(student);

        card.innerHTML = `
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="width:40px; height:40px; background:#F3F4F6; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#4B5563;">
                    ${initials}
                </div>
                <div>
                    <h3 style="margin:0; font-size:16px; color:#1F2937;">${student.name}</h3>
                    <span style="font-size:13px; color:#6B7280;">ID #${student.id} • ${student.course}</span>
                </div>
            </div>
            ${badgeHtml}
        `;
        listContainer.appendChild(card);
    });
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