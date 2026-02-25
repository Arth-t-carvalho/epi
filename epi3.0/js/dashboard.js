// =============================================================
// DASHBOARD.JS - VERSÃO DEFINITIVA COM CORES AUTOMÁTICAS
// =============================================================

// --- VARIÁVEIS GLOBAIS ---
let selectedDate = new Date(); 
let currCalYear = new Date().getFullYear(); 
let currCalMonth = new Date().getMonth();   
let allOccurrences = []; 

// Variáveis para armazenar as instâncias dos gráficos (ESSENCIAL PARA ATUALIZAR CORES)
let mainChartInstance = null;
let doughnutChartInstance = null;

const monthsFull = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];

// --- INICIALIZAÇÃO ---
document.addEventListener("DOMContentLoaded", function () {
    loadCalendarData(); 

    // Inicializa os Gráficos
    if (document.getElementById('mainChart')) {
        const savedChartType = localStorage.getItem('preferredChartType') || 'bar';
        loadChartData(savedChartType);
    }

    // Aplica a cor do CSS globalmente caso já exista uma salva
    const savedColor = localStorage.getItem('chartColor');
    if (savedColor) {
        document.documentElement.style.setProperty('--primary', savedColor);
        document.documentElement.style.setProperty('--chart-main-color', savedColor);
    }

    // Listeners do Modal
    const btnPrev = document.getElementById('prevMonth');
    const btnNext = document.getElementById('nextMonth');
    if (btnPrev) btnPrev.addEventListener('click', () => changeCalMonth(-1));
    if (btnNext) btnNext.addEventListener('click', () => changeCalMonth(1));

    const input = document.getElementById('manualDateInput');
    if (input) {
        input.addEventListener('keydown', (e) => { if (e.key === 'Enter') commitManualDate(); });
        input.addEventListener('input', maskDateInput);
    }

    document.addEventListener('click', (e) => {
        const calModal = document.getElementById('calendarModal');
        if (calModal && e.target === calModal) toggleCalendar();

        const detModal = document.getElementById('detailModal');
        if (detModal && e.target === detModal) detModal.classList.remove('open');

        const card = document.getElementById('instructorCard');
        const trigger = document.getElementById('profileTrigger');
        if (card && trigger && !card.contains(e.target) && !trigger.contains(e.target)) {
            card.classList.remove('active');
        }
    });
});

// =============================================================
// NOVO: SISTEMA DE ATUALIZAÇÃO AUTOMÁTICA DE CORES
// =============================================================

// Esta função injeta a nova cor DIRETAMENTE nos gráficos existentes e atualiza a tela
function updateChartColorsDynamically(newColor) {
    // 1. Atualiza a cor do Gráfico Anual (Barra/Linha)
    if (mainChartInstance) {
        // Altera a cor do dataset 'Capacete' (Índice 0)
        mainChartInstance.data.datasets[0].backgroundColor = newColor;
        mainChartInstance.data.datasets[0].borderColor = newColor;
        mainChartInstance.data.datasets[0].pointBackgroundColor = newColor;
        mainChartInstance.update(); // Manda o Chart.js redesenhar instantaneamente
    }

    // 2. Atualiza a cor do Gráfico de Rosca (EPI Menos Usado)
    if (doughnutChartInstance) {
        // Altera a cor do primeiro item do gráfico de rosca
        doughnutChartInstance.data.datasets[0].backgroundColor[0] = newColor;
        doughnutChartInstance.update(); // Redesenha instantaneamente
    }

    // 3. Atualiza as variáveis CSS para mudar o resto da interface do dashboard
    document.documentElement.style.setProperty('--primary', newColor);
    document.documentElement.style.setProperty('--chart-main-color', newColor);
}

// Ouve as mudanças no LocalStorage (Se o usuário mudar na aba de configurações, reflete na hora aqui)
window.addEventListener('storage', function(e) {
    if (e.key === 'chartColor' && e.newValue) {
        updateChartColorsDynamically(e.newValue);
    }
});

// Substitui a função do input color (caso a view seja na mesma página/SPA)
document.addEventListener('input', function(e) {
    if (e.target && e.target.id === 'chartColorPicker') {
        const newColor = e.target.value;
        localStorage.setItem('chartColor', newColor);
        updateChartColorsDynamically(newColor);
    }
});


// ===============================
// LÓGICA DE DADOS E INTERFACE GERAL
// ===============================

function loadCalendarData() {
    const month = selectedDate.getMonth() + 1;
    const year = selectedDate.getFullYear();

    fetch(`../apis/api.php?action=calendar&month=${month}&year=${year}`)
        .then(res => res.json())
        .then(data => {
            allOccurrences = Array.isArray(data) ? data : [];
            renderInterface(); 
        })
        .catch(err => {
            console.error('Erro calendário:', err);
            allOccurrences = [];
            renderInterface();
        });
}

function renderInterface() {
    const day = String(selectedDate.getDate()).padStart(2, '0');
    const monthFullStr = monthsFull[selectedDate.getMonth()];
    const yearStr = selectedDate.getFullYear();

    const elNum = document.getElementById('displayDayNum');
    const elStr = document.getElementById('displayMonthStr');

    if (elNum) elNum.innerText = day;
    if (elStr) elStr.innerText = `${monthFullStr} ${yearStr}`;

    const list = document.getElementById('occurrenceList');
    if (list) {
        list.innerHTML = '';
        const dailyData = allOccurrences.filter(item => {
            const dbDateString = item.full_date || item.data_hora || item.date;
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
                    </div>`;
            });
        } else {
            list.innerHTML = `<div class="empty-state" style="padding:20px; text-align:center; color:#94a3b8; font-size:13px;">✅ Nenhuma infração neste dia.</div>`;
        }
    }

    updateKPICards();
    updatePercentagesDinamicamente();
}

function changeDay(delta) {
    const oldMonth = selectedDate.getMonth();
    selectedDate.setDate(selectedDate.getDate() + delta);
    const newMonth = selectedDate.getMonth();

    if (oldMonth !== newMonth) {
        loadCalendarData();
    } else {
        renderInterface();
    }
}

function isSameDay(d1, d2) {
    return d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate();
}

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

function updateKPICards() {
    let countDay = 0, countWeek = 0, countMonth = 0;
    const selMonth = selectedDate.getMonth();
    const selYear = selectedDate.getFullYear();

    allOccurrences.forEach(item => {
        const dbDateString = item.full_date || item.data_hora || item.date;
        const itemDate = new Date(dbDateString.replace(/-/g, '/'));

        if (isSameDay(selectedDate, itemDate)) countDay++;
        if (isSameWeek(selectedDate, itemDate)) countWeek++;
        if (itemDate.getMonth() === selMonth && itemDate.getFullYear() === selYear) countMonth++;
    });

    const elDia = document.getElementById('kpiDia');
    const elSemana = document.getElementById('kpiSemana');
    const elMes = document.getElementById('kpiMes');

    if (elDia) elDia.innerText = countDay;
    if (elSemana) elSemana.innerText = countWeek;
    if (elMes) elMes.innerText = countMonth;
}

function toggleCalendar() {
    const modal = document.getElementById('calendarModal');
    if (!modal) return;

    if (!modal.classList.contains('active')) {
        currCalYear = selectedDate.getFullYear();
        currCalMonth = selectedDate.getMonth();
        renderCalendarGrid();
        modal.classList.add('active');
    } else {
        modal.classList.remove('active');
    }
}

function renderCalendarGrid() {
    const daysTag = document.getElementById("calendarDays");
    const monthTxt = document.getElementById("calMonthDisplay");
    const yearTxt = document.getElementById("calYearDisplay");

    if (!daysTag) return;

    let firstDayofMonth = new Date(currCalYear, currCalMonth, 1).getDay();
    let lastDateofMonth = new Date(currCalYear, currCalMonth + 1, 0).getDate();
    let lastDayofMonthIndex = new Date(currCalYear, currCalMonth, lastDateofMonth).getDay();
    let liTag = "";

    for (let i = firstDayofMonth; i > 0; i--) {
        liTag += `<li class="inactive">${new Date(currCalYear, currCalMonth, 0).getDate() - i + 1}</li>`;
    }
    for (let i = 1; i <= lastDateofMonth; i++) {
        let isToday = i === new Date().getDate() && currCalMonth === new Date().getMonth() && currCalYear === new Date().getFullYear() ? "today" : "";
        let isSelected = i === selectedDate.getDate() && currCalMonth === selectedDate.getMonth() && currCalYear === selectedDate.getFullYear() ? "active" : "";
        if (isSelected) isToday = "";

        liTag += `<li class="${isToday} ${isSelected}" onclick="selectDayAndClose(${i})">${i}</li>`;
    }
    for (let i = lastDayofMonthIndex; i < 6; i++) {
        liTag += `<li class="inactive">${i - lastDayofMonthIndex + 1}</li>`;
    }

    if (monthTxt) monthTxt.innerText = monthsFull[currCalMonth];
    if (yearTxt) yearTxt.innerText = currCalYear;
    daysTag.innerHTML = liTag;
}

function changeCalMonth(delta) {
    currCalMonth += delta;
    if (currCalMonth < 0 || currCalMonth > 11) {
        const d = new Date(currCalYear, currCalMonth, 1);
        currCalMonth = d.getMonth();
        currCalYear = d.getFullYear();
    }
    renderCalendarGrid();
}

function selectDayAndClose(day) {
    selectedDate = new Date(currCalYear, currCalMonth, day);
    loadCalendarData();
    toggleCalendar();
}

function maskDateInput(e) {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length > 2) v = v.slice(0, 2) + '/' + v.slice(2);
    if (v.length > 5) v = v.slice(0, 5) + '/' + v.slice(5);
    e.target.value = v;
}

function commitManualDate() {
    const input = document.getElementById('manualDateInput');
    const v = input.value;

    if (v.length < 10) { triggerInputError(); return; }

    const day = parseInt(v.slice(0, 2), 10);
    const monthIndex = parseInt(v.slice(3, 5), 10) - 1;
    const year = parseInt(v.slice(6, 10), 10);

    if (monthIndex < 0 || monthIndex > 11 || isNaN(monthIndex)) { triggerInputError(); return; }

    const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();

    if (day < 1 || day > daysInMonth || isNaN(day)) { triggerInputError(); return; }

    currCalMonth = monthIndex;
    currCalYear = year;
    selectDayAndClose(day);
    input.value = "";
}

function triggerInputError() {
    const wrapper = document.querySelector('.input-wrapper');
    wrapper.classList.add('error-shake');
    setTimeout(() => { wrapper.classList.remove('error-shake'); }, 400);
}

function toggleMonthList() {
    const drop = document.getElementById('monthDropdown');
    const yearDrop = document.getElementById('yearDropdown');
    if (yearDrop) yearDrop.classList.remove('active');

    if (!drop.classList.contains('active')) {
        let html = '';
        monthsFull.forEach((m, index) => {
            const isSelected = index === currCalMonth ? 'selected' : '';
            html += `<div class="dropdown-item ${isSelected}" onclick="selectMonth(${index})">${m}</div>`;
        });
        drop.innerHTML = html;
        drop.classList.add('active');
    } else {
        drop.classList.remove('active');
    }
}

function toggleYearList() {
    const drop = document.getElementById('yearDropdown');
    const monthDrop = document.getElementById('monthDropdown');
    if (monthDrop) monthDrop.classList.remove('active');

    if (!drop.classList.contains('active')) {
        let html = '';
        const currentYear = new Date().getFullYear();
        for (let i = currentYear - 5; i <= currentYear + 5; i++) {
            const isSelected = i === currCalYear ? 'selected' : '';
            html += `<div class="dropdown-item ${isSelected}" onclick="selectYear(${i})">${i}</div>`;
        }
        drop.innerHTML = html;
        drop.classList.add('active');
    } else {
        drop.classList.remove('active');
    }
}

function selectMonth(index) {
    currCalMonth = index;
    renderCalendarGrid();
    document.getElementById('monthDropdown').classList.remove('active');
}

function selectYear(year) {
    currCalYear = year;
    renderCalendarGrid();
    document.getElementById('yearDropdown').classList.remove('active');
}

window.addEventListener('click', function (e) {
    const monthContainer = document.getElementById('monthSelector');
    const yearContainer = document.getElementById('yearSelector');
    if (monthContainer && !monthContainer.contains(e.target)) {
        const drop = document.getElementById('monthDropdown');
        if (drop) drop.classList.remove('active');
    }
    if (yearContainer && !yearContainer.contains(e.target)) {
        const drop = document.getElementById('yearDropdown');
        if (drop) drop.classList.remove('active');
    }
});

function highlightDaily(periodo) {
    window.location.href = 'infracoes.php?filtro=' + periodo;
}

function refreshBadgesJS(currentVal, previousVal, elementId) {
    const badge = document.getElementById(elementId);
    if (!badge) return;

    let percent = 0;
    if (previousVal > 0) {
        percent = Math.round(((currentVal - previousVal) / previousVal) * 100);
    } else {
        percent = currentVal * 100;
    }

    const isUp = percent >= 0;
    badge.className = `badge ${isUp ? 'up' : 'down'}`;
    badge.innerHTML = `${isUp ? '↗' : '↘'} ${Math.abs(percent)}%`;
}

function updatePercentagesDinamicamente() {
    const datePrevDay = new Date(selectedDate);
    datePrevDay.setDate(datePrevDay.getDate() - 1);

    const startOfSelectedWeek = new Date(selectedDate);
    startOfSelectedWeek.setDate(selectedDate.getDate() - selectedDate.getDay());
    const datePrevWeek = new Date(startOfSelectedWeek);
    datePrevWeek.setDate(datePrevWeek.getDate() - 7);

    let totalOntem = 0;
    let totalSemanaPassada = 0;

    allOccurrences.forEach(item => {
        const itemDate = new Date((item.full_date || item.data_hora || item.date).replace(/-/g, '/'));
        if (isSameDay(datePrevDay, itemDate)) totalOntem++;
        if (isSameWeek(datePrevWeek, itemDate)) totalSemanaPassada++;
    });

    const totalHoje = parseInt(document.getElementById('kpiDia')?.innerText) || 0;
    const totalSemana = parseInt(document.getElementById('kpiSemana')?.innerText) || 0;

    refreshBadgesJS(totalHoje, totalOntem, 'badgeDia');
    refreshBadgesJS(totalSemana, totalSemanaPassada, 'badgeSemana');
}

// =========================================================
// SISTEMA DE NOTIFICAÇÕES EM TEMPO REAL
// =========================================================

let ultimoIdNotificacao = 0;

function mostrarNotificacao(aluno, epi) {
    const container = document.getElementById('notification-container');
    if (!container) return;

    const agora = new Date();
    const horario = agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

    const toast = document.createElement('div');
    toast.className = 'toast';

    toast.innerHTML = `
        <div class="toast-icon">
            <i data-lucide="alert-triangle"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">Infração Detectada</div>
            <div class="toast-message"><b>${aluno}</b> • Sem ${epi}</div>
            <span class="toast-time">${horario}</span>
        </div>
    `;

    container.appendChild(toast);
    if (typeof lucide !== 'undefined') lucide.createIcons({ root: toast });
    setTimeout(() => { 
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 350);
    }, 5000);
}

function verificarNovasOcorrencias() {
    fetch(`../php/check_notificacoes.php?last_id=${ultimoIdNotificacao}`, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'init') {
                ultimoIdNotificacao = data.last_id;
                return;
            }
            if (data.status === 'success' && data.dados.length > 0) {
                data.dados.forEach(ocorrencia => {
                    mostrarNotificacao(ocorrencia.aluno, ocorrencia.epi_nome);
                    ultimoIdNotificacao = ocorrencia.id;
                });
            }
        })
        .catch(err => console.error(err));
}

setInterval(verificarNovasOcorrencias, 5000);
verificarNovasOcorrencias();

/// =============================================================
/// GERENCIADOR DE GRÁFICOS (Chart.js) - CORRIGIDO
// =============================================================

function loadChartData(chartType = 'bar') {
    fetch('../apis/api.php?action=charts')
        .then(res => res.json())
        .then(response => {
            createMainChart(response, chartType);
            createDoughnutChart(response);
        })
        .catch(err => console.error('Erro ao carregar gráficos:', err));
}

function createMainChart(data, chartType = 'bar') {
    const ctx = document.getElementById('mainChart')?.getContext('2d');
    if (!ctx) return;

    // Pega a cor salva no localStorage
    const corDestaque = localStorage.getItem('chartColor') || '#E30613';

    if (mainChartInstance) mainChartInstance.destroy();

    mainChartInstance = new Chart(ctx, {
        type: chartType,
        data: {
            labels: monthsFull,
            datasets: [
                { 
                    label: 'Capacete', 
                    data: data.bar?.capacete || [], 
                    borderColor: corDestaque,
                    backgroundColor: chartType === 'bar' ? corDestaque : 'transparent',
                    borderWidth: chartType === 'line' ? 3 : 1,
                    pointBackgroundColor: corDestaque,
                    pointBorderColor: 'white',
                    tension: 0.4,
                    fill: false,
                    borderRadius: 4
                },
                { 
                    label: 'Óculos', 
                    data: data.bar?.oculos || [], 
                    borderColor: '#1F2937',
                    backgroundColor: chartType === 'bar' ? '#1F2937' : 'transparent',
                    borderWidth: chartType === 'line' ? 3 : 1,
                    pointBackgroundColor: '#1F2937',
                    pointBorderColor: 'white',
                    tension: 0.4,
                    fill: false,
                    borderRadius: 4
                },
                { 
                    label: 'Total', 
                    data: data.bar?.total || [], 
                    borderColor: '#9CA3AF',
                    backgroundColor: chartType === 'bar' ? '#9CA3AF' : 'transparent',
                    borderWidth: chartType === 'line' ? 3 : 1,
                    pointBackgroundColor: '#9CA3AF',
                    pointBorderColor: 'white',
                    tension: 0.4,
                    fill: false,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', labels: { usePointStyle: chartType === 'line', pointStyle: 'circle' } },
                tooltip: { mode: 'index', intersect: false }
            }
        }
    });
}

function createDoughnutChart(data) {
    const ctx = document.getElementById('doughnutChart')?.getContext('2d');
    if (!ctx) return;

    // Pega a cor salva no localStorage
    const corDestaque = localStorage.getItem('chartColor') || '#E30613';

    if (doughnutChartInstance) doughnutChartInstance.destroy();

    doughnutChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.doughnut?.labels || ['Capacete', 'Óculos', 'Outros'],
            datasets: [{
                data: data.doughnut?.data || [0, 0, 0],
                backgroundColor: [corDestaque, '#1F2937', '#9CA3AF'],
                borderWidth: 2,
                borderColor: 'white'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle' } }
            }
        }
    });
}

// Função para atualizar a cor dos gráficos sem recarregar
function updateChartsColor(newColor) {
    // Atualiza gráfico principal
    if (mainChartInstance) {
        mainChartInstance.data.datasets[0].borderColor = newColor;
        mainChartInstance.data.datasets[0].pointBackgroundColor = newColor;
        
        if (mainChartInstance.config.type === 'bar') {
            mainChartInstance.data.datasets[0].backgroundColor = newColor;
        }
        
        mainChartInstance.update();
    }
    
    // Atualiza gráfico de rosca
    if (doughnutChartInstance) {
        doughnutChartInstance.data.datasets[0].backgroundColor[0] = newColor;
        doughnutChartInstance.update();
    }
}

// Monitora mudanças no localStorage
window.addEventListener('storage', function(e) {
    if (e.key === 'chartColor') {
        updateChartsColor(e.newValue);
    }
});

// Exporta as funções necessárias
window.loadChartData = loadChartData;
window.createMainChart = createMainChart;
window.createDoughnutChart = createDoughnutChart;
window.updateChartsColor = updateChartsColor;
// =============================================================
// DASHBOARD.JS - VERSÃO FINAL COM COR DINÂMICA TOTAL
// =============================================================
// =============================================================
// INICIALIZAÇÃO
// =============================================================
document.addEventListener("DOMContentLoaded", function () {

    loadCalendarData();

    if (document.getElementById('mainChart')) {
        const savedChartType = localStorage.getItem('preferredChartType') || 'bar';
        loadChartData(savedChartType);
    }

    applySavedColor();
});

// =============================================================
// APLICA COR SALVA GLOBALMENTE
// =============================================================
function applySavedColor() {
    const savedColor = localStorage.getItem('chartColor') || '#E30613';
    document.documentElement.style.setProperty('--primary', savedColor);
}

// =============================================================
// CARREGAMENTO DOS GRÁFICOS
// =============================================================
function loadChartData(chartType = 'bar') {
    fetch('./apis/api.php?action=charts')
        .then(res => res.json())
        .then(response => {
            createMainChart(response, chartType);
            createDoughnutChart(response);
        })
        .catch(err => console.error(err));
}

function getChartColor() {
    return localStorage.getItem('chartColor') || '#E30613';
}

function createMainChart(data, chartType = 'bar') {

    const ctx = document.getElementById('mainChart')?.getContext('2d');
    if (!ctx) return;

    const cor = getChartColor();

    if (mainChartInstance) mainChartInstance.destroy();

    mainChartInstance = new Chart(ctx, {
        type: chartType,
        data: {
            labels: monthsFull,
            datasets: [
                {
                    label: 'Capacete',
                    data: data.bar?.capacete || [],
                    borderColor: cor,
                    backgroundColor: chartType === 'bar' ? cor : 'transparent',
                    borderWidth: chartType === 'line' ? 3 : 1,
                    pointBackgroundColor: cor,
                    tension: 0.4,
                    fill: false,
                    borderRadius: 4
                },
                {
                    label: 'Óculos',
                    data: data.bar?.oculos || [],
                    borderColor: '#1F2937',
                    backgroundColor: chartType === 'bar' ? '#1F2937' : 'transparent',
                    borderWidth: chartType === 'line' ? 3 : 1,
                    tension: 0.4,
                    fill: false,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            }
        }
    });
}

function createDoughnutChart(data) {

    const ctx = document.getElementById('doughnutChart')?.getContext('2d');
    if (!ctx) return;

    const cor = getChartColor();

    if (doughnutChartInstance) doughnutChartInstance.destroy();

    doughnutChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.doughnut?.labels || ['Capacete','Óculos','Outros'],
            datasets: [{
                data: data.doughnut?.data || [0,0,0],
                backgroundColor: [cor, '#1F2937', '#9CA3AF'],
                borderWidth: 2,
                borderColor: 'white'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%'
        }
    });
}

// =============================================================
// ATUALIZAÇÃO DINÂMICA DE COR
// =============================================================
function updateChartsColor(newColor) {

    document.documentElement.style.setProperty('--primary', newColor);

    if (mainChartInstance) {
        mainChartInstance.data.datasets[0].borderColor = newColor;
        mainChartInstance.data.datasets[0].pointBackgroundColor = newColor;
        mainChartInstance.data.datasets[0].backgroundColor =
            mainChartInstance.config.type === 'bar' ? newColor : 'transparent';
        mainChartInstance.update();
    }

    if (doughnutChartInstance) {
        doughnutChartInstance.data.datasets[0].backgroundColor[0] = newColor;
        doughnutChartInstance.update();
    }
}

// Escuta alteração vinda da página de configurações
window.addEventListener('storage', function(e) {
    if (e.key === 'chartColor' && e.newValue) {
        updateChartsColor(e.newValue);
    }
});

// Caso esteja na mesma página
document.addEventListener('input', function(e) {
    if (e.target && e.target.id === 'chartColorPicker') {
        const newColor = e.target.value;
        localStorage.setItem('chartColor', newColor);
        updateChartsColor(newColor);
    }
});