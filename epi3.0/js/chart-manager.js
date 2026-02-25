// =============================================================
// chart-manager.js - Gerenciador de Gráficos para EPI GUARD
// =============================================================

// Variável global para armazenar a instância do gráfico atual
let mainChartInstance = null;

// Array de meses em português
const monthsFull = [
    "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
    "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
];

// Configurações dos gráficos
const chartConfigs = {
    bar: {
        type: 'bar',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            onClick: (evt, active, chart) => {
                if (active.length > 0 && typeof window.openDetailModal === 'function') {
                    window.openDetailModal(active[0].index, chart.data.labels[active[0].index]);
                }
            }
        }
    },
    line: {
        type: 'line',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            tension: 0.4,
            elements: {
                point: {
                    radius: 5,
                    hoverRadius: 8,
                    backgroundColor: 'white',
                    borderWidth: 2,
                    hitRadius: 10
                },
                line: {
                    borderWidth: 3,
                    tension: 0.4
                }
            },
            onClick: (evt, active, chart) => {
                if (active.length > 0 && typeof window.openDetailModal === 'function') {
                    window.openDetailModal(active[0].index, chart.data.labels[active[0].index]);
                }
            }
        }
    }
};

/**
 * Carrega os dados do gráfico via API e cria/atualiza o gráfico
 * @param {string} chartType - Tipo do gráfico ('bar' ou 'line')
 */
function loadChartData(chartType = 'bar') {
    fetch('../apis/api.php?action=charts')
        .then(res => res.json())
        .then(response => {
            createMainChart(response, chartType);
            createDoughnutChart(response);
        })
        .catch(err => {
            console.error('Erro ao carregar gráficos:', err);
            // Dados de exemplo para desenvolvimento
            const mockData = {
                bar: {
                    capacete: [65, 59, 80, 81, 56, 55, 40, 45, 50, 55, 60, 65],
                    oculos: [28, 48, 40, 19, 86, 27, 35, 40, 45, 50, 55, 60],
                    total: [93, 107, 120, 100, 142, 82, 75, 85, 95, 105, 115, 125]
                },
                doughnut: {
                    labels: ['Capacete', 'Óculos', 'Outros'],
                    data: [45, 30, 25]
                }
            };
            createMainChart(mockData, chartType);
            createDoughnutChart(mockData);
        });
}

/**
 * Cria ou atualiza o gráfico principal (barras/linhas)
 * @param {Object} data - Dados do gráfico vindos da API
 * @param {string} chartType - Tipo do gráfico ('bar' ou 'line')
 */
function createMainChart(data, chartType = 'bar') {
    const canvas = document.getElementById('mainChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');

    // Destrói o gráfico anterior se existir
    if (mainChartInstance) {
        mainChartInstance.destroy();
    }

    // Pega a configuração do tipo de gráfico selecionado
    const config = chartConfigs[chartType] || chartConfigs.bar;

    // Prepara os datasets baseado no tipo
    const datasets = [
        { 
            label: 'Capacete', 
            data: data.bar?.capacete || [], 
            borderColor: '#E30613',
            backgroundColor: chartType === 'bar' ? '#E30613' : 'transparent',
            borderWidth: chartType === 'line' ? 3 : 1,
            pointBackgroundColor: '#E30613',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointHoverBackgroundColor: '#E30613',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 2,
            pointHoverRadius: 8,
            tension: 0.4,
            fill: false
        },
        { 
            label: 'Óculos', 
            data: data.bar?.oculos || [], 
            borderColor: '#1F2937',
            backgroundColor: chartType === 'bar' ? '#1F2937' : 'transparent',
            borderWidth: chartType === 'line' ? 3 : 1,
            pointBackgroundColor: '#1F2937',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointHoverBackgroundColor: '#1F2937',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 2,
            pointHoverRadius: 8,
            tension: 0.4,
            fill: false
        },
        { 
            label: 'Total', 
            data: data.bar?.total || [], 
            borderColor: '#9CA3AF',
            backgroundColor: chartType === 'bar' ? '#9CA3AF' : 'transparent',
            borderWidth: chartType === 'line' ? 3 : 1,
            pointBackgroundColor: '#9CA3AF',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointHoverBackgroundColor: '#9CA3AF',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 2,
            pointHoverRadius: 8,
            tension: 0.4,
            fill: false
        }
    ];

    // Cria o novo gráfico
    mainChartInstance = new Chart(ctx, {
        type: config.type,
        data: {
            labels: monthsFull,
            datasets: datasets
        },
        options: {
            ...config.options,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: chartType === 'line',
                        pointStyle: 'circle',
                        padding: 20,
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 13,
                        weight: '600'
                    },
                    bodyFont: {
                        size: 12
                    },
                    padding: 10,
                    cornerRadius: 6,
                    displayColors: true,
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        stepSize: 20,
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            }
        }
    });

    // Salva a preferência do tipo de gráfico
    localStorage.setItem('preferredChartType', chartType);
}

/**
 * Cria o gráfico de rosca (doughnut)
 * @param {Object} data - Dados do gráfico vindos da API
 */
function createDoughnutChart(data) {
    const canvas = document.getElementById('doughnutChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');

    // Verifica se já existe um gráfico de rosca e destrói
    if (window.doughnutChartInstance) {
        window.doughnutChartInstance.destroy();
    }

    window.doughnutChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.doughnut?.labels || ['Capacete', 'Óculos', 'Outros'],
            datasets: [{
                data: data.doughnut?.data || [45, 30, 25],
                backgroundColor: ['#E30613', '#1F2937', '#9CA3AF'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15,
                        font: {
                            size: 11,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 12,
                        weight: '600'
                    },
                    bodyFont: {
                        size: 11
                    },
                    padding: 8,
                    cornerRadius: 4
                }
            }
        }
    });
}

/**
 * Altera o tipo do gráfico principal
 * @param {string} type - Tipo do gráfico ('bar' ou 'line')
 */
function changeMainChartType(type) {
    if (!mainChartInstance) {
        loadChartData(type);
        return;
    }

    // Recarrega os dados e recria o gráfico com o novo tipo
    fetch('../apis/api.php?action=charts')
        .then(res => res.json())
        .then(response => {
            createMainChart(response, type);
        })
        .catch(err => {
            console.error('Erro ao mudar tipo do gráfico:', err);
            // Tenta com dados mock em caso de erro
            const mockData = {
                bar: {
                    capacete: [65, 59, 80, 81, 56, 55, 40, 45, 50, 55, 60, 65],
                    oculos: [28, 48, 40, 19, 86, 27, 35, 40, 45, 50, 55, 60],
                    total: [93, 107, 120, 100, 142, 82, 75, 85, 95, 105, 115, 125]
                }
            };
            createMainChart(mockData, type);
        });
}

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Verifica se está na página do dashboard
    if (document.getElementById('mainChart')) {
        // Carrega a preferência salva ou usa 'bar' como padrão
        const savedChartType = localStorage.getItem('preferredChartType') || 'bar';
        
        // Carrega os dados e cria o gráfico
        loadChartData(savedChartType);
    }
});

// Exporta as funções para uso global
window.loadChartData = loadChartData;
window.createMainChart = createMainChart;
window.changeMainChartType = changeMainChartType;