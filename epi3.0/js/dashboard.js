// ===============================
// LÓGICA 1: INTERFACE
// ===============================
function toggleInstructorCard() {
    const card = document.getElementById('instructorCard');
    card.classList.toggle('active');
}

document.addEventListener('click', function (event) {
    const card = document.getElementById('instructorCard');
    const trigger = document.getElementById('profileTrigger');
    if (!card.contains(event.target) && !trigger.contains(event.target)) {
        card.classList.remove('active');
    }
});

function exportData() {
    const btn = document.querySelector('.btn-export');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = 'Exportando...';
    btn.style.borderColor = '#E30613';
    btn.style.color = '#E30613';

    setTimeout(() => {
        alert("Dados exportados (CSV) com sucesso!");
        btn.innerHTML = originalHTML;
        btn.style.borderColor = '';
        btn.style.color = '';
    }, 1000);
}


// ===============================
// LÓGICA 2: CALENDÁRIO (BACKEND)
// ===============================
// ===============================
// LÓGICA 2: CALENDÁRIO & KPIS DINÂMICOS
// ===============================

// Estado Global
let selectedDate = new Date(); // Começa com Hoje
let allOccurrences = []; // Armazena todos os dados vindos do banco

// Função para comparar se duas datas são o mesmo dia
function isSameDay(d1, d2) {
    return d1.getFullYear() === d2.getFullYear() &&
           d1.getMonth() === d2.getMonth() &&
           d1.getDate() === d2.getDate();
}

// Função para verificar se está na mesma semana (começando domingo)
function isSameWeek(d1, d2) {
    const onejan = new Date(d1.getFullYear(), 0, 1);
    const today = new Date(d1.getFullYear(), d1.getMonth(), d1.getDate());
    const dayOfYear = ((today - onejan + 86400000) / 86400000);
    const week1 = Math.ceil(dayOfYear / 7);
    
    const target = new Date(d2.getFullYear(), d2.getMonth(), d2.getDate());
    const dayOfYearTarget = ((target - onejan + 86400000) / 86400000);
    const week2 = Math.ceil(dayOfYearTarget / 7);

    return d1.getFullYear() === d2.getFullYear() && week1 === week2;
}

function renderInterface() {
    // 1. Atualiza Texto da Data no Navegador
    const day = String(selectedDate.getDate()).padStart(2, '0');
    // Obs: O replace remove o ponto que alguns browsers colocam (ex: jan.)
    const month = selectedDate.toLocaleString('pt-BR', { month: 'short' }).replace('.', '');
    document.getElementById('displayDay').innerHTML = `${day} <span style="font-size:0.6em; text-transform:uppercase">${month}</span>`;

    // 2. Filtra Ocorrências para a LISTA LATERAL
    const list = document.getElementById('occurrenceList');
    list.innerHTML = '';

    const dailyData = allOccurrences.filter(item => {
        // O PHP agora retorna 'full_date' ou 'data_hora'. 
        // Vamos garantir que pegamos o campo certo.
        const dbDateString = item.full_date || item.data_hora || item.date;
        // Safari/Firefox as vezes tem problemas com datas SQL padrão, o replace ajuda
        const itemDate = new Date(dbDateString.replace(/-/g, '/')); 
        
        return isSameDay(selectedDate, itemDate);
    });

    if (dailyData.length > 0) {
        dailyData.forEach(item => {
            const initials = item.name ? item.name.substring(0, 2).toUpperCase() : '??';
            list.innerHTML += `
                <div class="occurrence-item">
                    <div class="occ-avatar">${initials}</div>
                    <div class="occ-info">
                        <span class="occ-name">${item.name}</span>
                        <span class="occ-desc">${item.desc}</span>
                    </div>
                    <div class="occ-time">${item.time}</div>
                </div>
            `;
        });
    } else {
        list.innerHTML = `<div class="empty-state" style="padding:20px; text-align:center; color:#94a3b8; font-size:13px;">✅ Nenhuma infração neste dia.</div>`;
    }

    // 3. ATUALIZA OS CARDS (KPIs)
    updateKPICards();
}
function updateKPICards() {
    let countDay = 0;
    let countWeek = 0;
    let countMonth = 0;

    // Precisamos do mês e ano selecionados para comparar
    const selMonth = selectedDate.getMonth();
    const selYear = selectedDate.getFullYear();

    allOccurrences.forEach(item => {
        const dbDateString = item.full_date || item.data_hora || item.date;
        const itemDate = new Date(dbDateString.replace(/-/g, '/'));

        // 1. KPI Dia
        if (isSameDay(selectedDate, itemDate)) {
            countDay++;
        }

        // 2. KPI Semana (Mesma semana do ano da data selecionada)
        if (isSameWeek(selectedDate, itemDate)) {
            countWeek++;
        }

        // 3. KPI Mês (Mesmo mês e ano da data selecionada)
        if (itemDate.getMonth() === selMonth && itemDate.getFullYear() === selYear) {
            countMonth++;
        }
    });

    // Atualiza DOM
    const elDia = document.getElementById('kpiDia');
    const elSemana = document.getElementById('kpiSemana');
    const elMes = document.getElementById('kpiMes');

    if (elDia) elDia.innerText = countDay;
    if (elSemana) elSemana.innerText = countWeek;
    if (elMes) elMes.innerText = countMonth;
    
    // Animação visual opcional (piscar cor)
    if (elDia) {
        elDia.style.color = '#E30613';
        setTimeout(() => elDia.style.color = '', 300);
    }
}
function changeDay(delta) {
    const oldMonth = selectedDate.getMonth();
    
    // Atualiza a data
    selectedDate.setDate(selectedDate.getDate() + delta);
    
    const newMonth = selectedDate.getMonth();

    // Se mudou o mês, precisamos buscar os dados do novo mês na API
    if (oldMonth !== newMonth) {
        loadCalendar(); 
    } else {
        // Se é o mesmo mês, apenas renderiza com os dados que já temos na memória
        renderInterface();
    }
}

function loadCalendar() {
    // Pega o mês e ano da data SELECIONADA, não a data de hoje
    const month = selectedDate.getMonth() + 1; // JS conta meses de 0 a 11
    const year = selectedDate.getFullYear();

    fetch(`../apis/api.php?action=calendar&month=${month}&year=${year}`)
        .then(res => res.json())
        .then(data => {
            // Garante que é um array
            allOccurrences = Array.isArray(data) ? data : [];
            renderInterface();
        })
        .catch(err => {
            console.error('Erro calendário:', err);
            allOccurrences = [];
            renderInterface();
        });
}


// ===============================
// LÓGICA 3: MODAL (BACKEND)
// ===============================
function openModal(monthIndex, monthName) {
    const modal = document.getElementById('detailModal');
    const title = document.getElementById('modalMonthTitle');
    const tbody = document.getElementById('modalTableBody');

    // --- CORREÇÃO 1: TRANSFORMA MÊS 0 EM MÊS 1 ---
    // Se não fizer isso, Janeiro (0) nunca vai achar dados no banco
    const realMonth = monthIndex + 1;

    // --- CORREÇÃO 2: DEFINE O ANO ---
    // Sem o ano, o banco pode se perder
    const currentYear = new Date().getFullYear();

    title.innerText = `${monthName} de ${currentYear}`;

    // Limpa e mostra carregando
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center">Carregando...</td></tr>';

    // Abre o modal
    modal.classList.add('open');

    // Faz a chamada corrigida para api_alunos.php (note o realMonth)
    fetch(`../apis/api.php?action=modal_details&month=${realMonth}&year=${currentYear}`)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';

            // Se a lista estiver vazia
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px;">Nenhum registro encontrado neste mês.</td></tr>';
                return;
            }

            // Se tiver dados, monta a tabela
            data.forEach(row => {
                // Ajusta cor do status
                const statusTexto = row.status_formatado || row.status;
                let classeStatus = 'status-resolvido';
                if (statusTexto === 'Pendente') classeStatus = 'status-pendente';

                tbody.innerHTML += `
                    <tr>
                        <td>${row.data}</td>
                        <td style="font-weight:500;">${row.aluno}</td>
                        <td>${row.epis}</td>
                        <td>${row.hora}</td>
                        <td>
                            <span class="status-badge ${classeStatus}">
                                ${statusTexto}
                            </span>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            tbody.innerHTML = '<tr><td colspan="5" style="color:red; text-align:center">Erro na conexão.</td></tr>';
        });
}

function closeModal() {
    document.getElementById('detailModal').classList.remove('open');
}

document.getElementById('detailModal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});


// ===============================
// LÓGICA 4: CHART.JS (BACKEND)
// ===============================
document.addEventListener("DOMContentLoaded", function () {

    loadCalendar();

    fetch('../apis/api.php?action=charts')
        .then(res => res.json())
        .then(response => {


            // -------- GRÁFICO DE BARRAS --------
            const ctxMain = document.getElementById('mainChart').getContext('2d');
            new Chart(ctxMain, {
                type: 'bar',
                data: {
                    labels: [
                        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                    ],
                    datasets: [
                        {
                            label: 'Capacete',
                            data: response.bar.capacete,
                            backgroundColor: '#E30613',
                            borderRadius: 4
                        },
                        {
                            label: 'Óculos',
                            data: response.bar.oculos,
                            backgroundColor: '#1F2937',
                            borderRadius: 4
                        },
                        {
                            label: 'Total',
                            data: response.bar.total,
                            backgroundColor: '#9CA3AF',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: (evt, active, chart) => {
                        if (active.length > 0) {
                            openModal(
                                active[0].index,
                                chart.data.labels[active[0].index]
                            );
                        }
                    }
                }
            });

            // -------- GRÁFICO DE ROSCA --------
            const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: response.doughnut.labels,
                    datasets: [{
                        data: response.doughnut.data,
                        backgroundColor: ['#E30613', '#1F2937', '#9CA3AF'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%'
                }
            });

        })
        .catch(err => console.error('Erro gráficos:', err));
});
