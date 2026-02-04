        // --- LÓGICA 1: INTERFACE ---
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

        // --- LÓGICA 2: CALENDÁRIO ---
        let currentDay = 2;
        const dbOccurrences = {
            2: [
                { name: "João Silva", time: "13:15", desc: "Sem Óculos" },
                { name: "Pedro Rocha", time: "14:00", desc: "Luva rasgada" },
                { name: "Ana Maria", time: "15:30", desc: "Cabelo solto" }
            ],
            3: [
                { name: "Fernanda C.", time: "13:10", desc: "Calçado" }
            ],
            5: [
                { name: "Roberto S.", time: "14:20", desc: "Sem Óculos" }
            ]
        };

        function renderCalendar() {
            document.getElementById('displayDay').innerText = String(currentDay).padStart(2, '0');
            const list = document.getElementById('occurrenceList');
            list.innerHTML = '';
            const data = dbOccurrences[currentDay];

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
            if (currentDay > 30) currentDay = 30;
            renderCalendar();
        }

        // --- LÓGICA 3: FUNÇÕES DO MODAL E DADOS FICTÍCIOS ---

        function openModal(monthIndex, monthName) {
            const modal = document.getElementById('detailModal');
            const title = document.getElementById('modalMonthTitle');
            const tbody = document.getElementById('modalTableBody');

            // Atualiza título com o mês clicado
            title.innerText = monthName;

            // Gera dados fictícios (simulação de backend)
            const mockData = generateMockData(monthIndex);

            // Limpa tabela anterior
            tbody.innerHTML = '';

            // Preenche tabela com novos dados
            mockData.forEach(row => {
                const statusClass = row.status === 'Pendente' ? 'status-pendente' : 'status-resolvido';
                const htmlRow = `
                    <tr>
                        <td>${row.date}</td>
                        <td style="font-weight:500;">${row.student}</td>
                        <td>${row.type}</td>
                        <td>${row.time}</td>
                        <td><span class="status-badge ${statusClass}">${row.status}</span></td>
                    </tr>
                `;
                tbody.innerHTML += htmlRow;
            });

            // Mostra o modal
            modal.classList.add('open');
        }

        function closeModal() {
            const modal = document.getElementById('detailModal');
            modal.classList.remove('open');
        }

        // Fechar ao clicar fora do container
        document.getElementById('detailModal').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        // Gerador de dados aleatórios para demonstração
        function generateMockData(monthIndex) {
            const names = ["João Silva", "Pedro Rocha", "Ana Maria", "Carlos Eduardo", "Beatriz Lima", "Ruanzin", "Ian"];
            const types = ["Sem Capacete", "Sem Óculos", "Luva Rasgada", "Calçado Inadequado", "Uniforme Incompleto"];
            const statuses = ["Pendente", "Resolvido"];

            let data = [];
            // Cria um número aleatório de ocorrências (3 a 10)
            const count = Math.floor(Math.random() * 8) + 3;

            for (let i = 0; i < count; i++) {
                data.push({
                    date: `${Math.floor(Math.random() * 28) + 1}/${String(monthIndex + 1).padStart(2, '0')}/2024`,
                    student: names[Math.floor(Math.random() * names.length)],
                    type: types[Math.floor(Math.random() * types.length)],
                    time: `${Math.floor(Math.random() * 9) + 8}:${String(Math.floor(Math.random() * 59)).padStart(2, '0')}`,
                    status: statuses[Math.floor(Math.random() * statuses.length)]
                });
            }
            // Ordena por dia
            return data.sort((a, b) => parseInt(a.date) - parseInt(b.date));
        }

        // --- LÓGICA 4: CHART.JS ---
        document.addEventListener("DOMContentLoaded", function () {
            renderCalendar();

            // 1. Gráfico Principal (Barras) com EVENTO DE CLIQUE
            const ctxMain = document.getElementById('mainChart').getContext('2d');
            new Chart(ctxMain, {
                type: 'bar',
                data: {
                    labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                    datasets: [
                        {
                            label: 'Capacete',
                            data: [30, 45, 35, 50, 40, 60, 35, 42, 38, 55, 48, 60],
                            backgroundColor: '#E30613',
                            borderRadius: 4,
                            categoryPercentage: 0.7,
                            barPercentage: 0.9
                        },
                        {
                            label: 'Óculos',
                            data: [20, 30, 25, 40, 30, 45, 25, 28, 26, 35, 32, 40],
                            backgroundColor: '#1F2937',
                            borderRadius: 4,
                            categoryPercentage: 0.7,
                            barPercentage: 0.9
                        }, {
                            label: 'total',
                            data: [50, 75, 60, 90, 70, 105, 60, 70, 64, 90, 80, 100],
                            backgroundColor: 'gray',
                            borderRadius: 4,
                            categoryPercentage: 0.7,
                            barPercentage: 0.9
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top', align: 'end' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    // *** AQUI ESTÁ A LÓGICA DO CLIQUE ***
                    onClick: (evt, activeElements, chart) => {
                        if (activeElements.length > 0) {
                            const firstPoint = activeElements[0];
                            const index = firstPoint.index;
                            const label = chart.data.labels[index];

                            // Chama a função para abrir o modal
                            openModal(index, label);
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: {
                                color: '#94a3b8',
                                lineWidth: 1.5,
                                borderDash: [6, 6]
                            },
                            ticks: { color: '#64748B', font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748B' }
                        }
                    }
                }
            });

            // 2. Gráfico de Rosca (Donut)
            const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Capacete', 'Óculos'],
                    datasets: [{
                        data: [25, 75],
                        backgroundColor: ['#E30613', '#1F2937', '#9CA3AF'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { position: 'right', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 11 } } }
                    }
                }
            });
        });
