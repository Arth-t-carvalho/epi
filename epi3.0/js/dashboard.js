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
let currentDay = new Date().getDate();
let calendarData = {};

function renderCalendar() {
    document.getElementById('displayDay').innerText = String(currentDay).padStart(2, '0');
    const list = document.getElementById('occurrenceList');
    list.innerHTML = '';

    const data = calendarData[currentDay];

    if (data && data.length > 0) {
        data.forEach(item => {
            const initials = item.name.split(' ').map(n => n[0]).join('').substring(0, 2);
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
        list.innerHTML = `<div class="empty-state">✅ Nenhuma infração.</div>`;
    }
}

function changeDay(delta) {
    currentDay += delta;
    if (currentDay < 1) currentDay = 1;
    if (currentDay > 31) currentDay = 31;
    renderCalendar();
}

function loadCalendar() {
    fetch('../apis/api.php?action=calendar')
        .then(res => res.json())
        .then(data => {
            calendarData = data;
            renderCalendar();
        })
        .catch(err => console.error('Erro calendário:', err));
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
                if(statusTexto === 'Pendente') classeStatus = 'status-pendente';

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
                        'Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                        'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'
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
console.log('Charts:', response);
