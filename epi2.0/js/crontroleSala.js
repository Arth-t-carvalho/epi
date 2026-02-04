        // --- DADOS COM A LÓGICA DE DUPLA INFRAÇÃO ---
        // missing: [] -> Sem infração atual
        // history: true -> Tem infração passada
        // missing: [...] + history: true -> REINCIDENTE
        const students = [
            { id: 101, name: "Arthur", course: "Mecânica", missing: ["óculos"], history: true }, // OK
            { id: 102, name: "Beatriz", course: "Mecânica", missing: ["Óculos"], history: true }, // REINCIDENTE (Roxo)
            { id: 103, name: "Ian", course: "Mecânica", missing: [], history: true }, // HISTÓRICO (Laranja)
            { id: 104, name: "Gideão", course: "Mecânica", missing: [], history: false }, // OK
            { id: 105, name: "Heitor", course: "Mecânica", missing: ["Capacete"], history: false }, // RISCO (Vermelho)
            { id: 106, name: "Ruanzin", course: "Mecânica", missing: [], history: false },
            { id: 107, name: "Matheus", course: "Mecânica", missing: [], history: true }, // HISTÓRICO
            { id: 108, name: "Kauan", course: "Mecânica", missing: [], history: false },
            { id: 109, name: "João Black", course: "Mecânica", missing: ["Luvas"], history: true }, // REINCIDENTE
            { id: 110, name: "Pirra", course: "Mecânica", missing: [], history: false },
            { id: 111, name: "Ruan Gomes", course: "Mecânica", missing: [], history: true },
            { id: 112, name: "Pereira", course: "Mecânica", missing: [], history: false },
            { id: 113, name: "Vitor", course: "Mecânica", missing: [], history: false },
            { id: 114, name: "Lais", course: "Mecânica", missing: ["Botas"], history: false }, // RISCO
            { id: 115, name: "Guedes", course: "Mecânica", missing: [], history: false },
            { id: 116, name: "Parapirra", course: "Mecânica", missing: [], history: true },
            { id: 117, name: "Nahyron", course: "Mecânica", missing: ["Capacete", "Luvas"], history: true }, // REINCIDENTE
            { id: 118, name: "Gabriel", course: "Mecânica", missing: [], history: false },
            { id: 119, name: "Julia", course: "Mecânica", missing: [], history: false },
            { id: 120, name: "Rafael", course: "Mecânica", missing: ["Óculos"], history: false }
        ];

        const listContainer = document.getElementById('studentList');

        // Helper para descobrir o estado
        function getStudentState(student) {
            const hasRisk = student.missing.length > 0;
            const hasHistory = student.history;

            if (hasRisk && hasHistory) return 'Repeat'; // Novo estado
            if (hasRisk) return 'Risk';
            if (hasHistory) return 'History';
            return 'Safe';
        }

        function renderList(filterText = '', filterStatus = 'all') {
            listContainer.innerHTML = '';

            const filtered = students.filter(s => {
                const state = getStudentState(s);
                const matchesText = s.name.toLowerCase().includes(filterText.toLowerCase());
                const matchesStatus = filterStatus === 'all' || state === filterStatus;
                return matchesText && matchesStatus;
            });

            if (filtered.length === 0) {
                listContainer.innerHTML = `<div style="padding:20px; color:#999;">Nenhum aluno encontrado.</div>`;
                return;
            }


            filtered.forEach((student, index) => {
                const state = getStudentState(student);
                const initials = student.name.substring(0, 2).toUpperCase();

                let cardClass = '';
                let badgeHtml = '';

                switch (state) {
                    case 'Repeat':
                        cardClass = 'is-repeat';
                        badgeHtml = '<div class="status-pill status-repeat">⚠️ REINCIDENTE</div>';
                        break;
                    case 'Risk':
                        cardClass = 'is-risk';
                        badgeHtml = '<div class="status-pill status-risk">⚠️ SEM EPI</div>';
                        break;
                    case 'History':
                        cardClass = 'is-history';
                        badgeHtml = '<div class="status-pill status-history">🔔 ALERTA</div>';
                        break;
                    default:
                        badgeHtml = '<div class="status-pill status-ok">REGULAR</div>';
                }

                const card = document.createElement('div');
                card.className = `student-card ${cardClass}`;
                card.style.animationDelay = `${index * 0.03}s`;
                card.onclick = () => openModal(student);

                card.innerHTML = `
                    <div class="card-info">
                        <div class="avatar-placeholder">${initials}</div>
                        <div class="info-text">
                            <h3>${student.name}</h3>
                            <span>ID #${student.id} • ${student.course}</span>
                        </div>
                    </div>
                    ${badgeHtml}
                `;
                listContainer.appendChild(card);
            });
        }

        // --- LÓGICA DO MODAL ---
        const modal = document.getElementById('detailModal');

        function openModal(student) {
            const hasRisk = student.missing.length > 0;
            const hasHistory = student.history;

            document.getElementById('modalName').innerText = student.name;
            document.getElementById('modalCourse').innerText = `${student.course} • ID #${student.id}`;

            // Lista EPI
            const epiContainer = document.getElementById('modalEpiList');
            epiContainer.innerHTML = '';
            const allEpis = ["Capacete", "Óculos", "Luvas", "Botas"];
            allEpis.forEach(epi => {
                const isMissing = student.missing.includes(epi);
                const item = document.createElement('div');
                item.className = `epi-item ${isMissing ? 'missing' : 'ok'}`;
                item.innerHTML = `<span>${epi}</span> <span>${isMissing ? '❌ Ausente' : '✅ Ok'}</span>`;
                epiContainer.appendChild(item);
            });

            // --- GERAÇÃO INTELIGENTE DE BOTÕES ---
            const footer = document.getElementById('modalFooterActions');
            let buttonsHtml = '';

            // Botão de ver infração atual (se houver risco)
            if (hasRisk) {
                buttonsHtml += `
                    <a href="ver-infracao.html?id=${student.id}&type=active" class="btn btn-danger">
                        📸 Ver Infração Atual
                    </a>`;
            }

            // Botão de ver histórico (se houver histórico)
            if (hasHistory) {
                buttonsHtml += `
                    <a href="ver-infracao.html?id=${student.id}&type=history" class="btn btn-warning">
                        🕒 Ver Infração Passada
                    </a>`;
            }

            // Botão de abrir nova ocorrência (sempre aparece)
            buttonsHtml += `<button class="btn btn-primary" onclick="alert('Nova ocorrência registrada para ${student.name}')">📝 Abrir Ocorrência</button>`;

            footer.innerHTML = buttonsHtml;
            modal.classList.add('open');
        }

        function closeModal() {
            modal.classList.remove('open');
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        function toggleInstructorCard() {
            document.getElementById('instructorCard').classList.toggle('active');
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        document.getElementById('searchInput').addEventListener('keyup', (e) => renderList(e.target.value, document.getElementById('statusFilter').value));
        document.getElementById('statusFilter').addEventListener('change', (e) => renderList(document.getElementById('searchInput').value, e.target.value));
        renderList();

        // --- SCRIPT DE TRANSIÇÃO DE PÁGINA ---
        document.addEventListener("DOMContentLoaded", () => {
            const links = document.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                    e.preventDefault();
                    document.body.classList.add('page-exit');
                    setTimeout(() => {
                        window.location.href = href;
                    }, 400);
                });
            });
        });
