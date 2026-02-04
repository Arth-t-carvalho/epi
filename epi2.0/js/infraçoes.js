        // --- 1. LÓGICA DO NOVO HEADER (Exportar e Perfil) ---
        function toggleInstructorCard() {
            const card = document.getElementById('instructorCard');
            card.classList.toggle('active');
        }

        // Fecha o card do instrutor se clicar fora dele
        document.addEventListener('click', function (event) {
            const card = document.getElementById('instructorCard');
            const trigger = document.getElementById('profileTrigger');
            // Verifica se o elemento clicado não é o card nem o botão que abre o card
            if (card && trigger && !card.contains(event.target) && !trigger.contains(event.target)) {
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

        // --- 2. DADOS DA GALERIA (MOCKUP) ---
        const ocorrencias = [
            { id: 1, nome: "", infra: "Sem Óculos de Proteção", setor: "Torno CNC", time: "10:42", cam: "CAM-01", img: "https://images.unsplash.com/photo-1581092921461-eab62e97a782?q=80&w=400&auto=format&fit=crop" },
            { id: 2, nome: "", infra: "Sem Capacete", setor: "Obras", time: "11:15", cam: "CAM-04", img: "https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?q=80&w=400&auto=format&fit=crop" },
            { id: 3, nome: "", infra: "Luva Inadequada", setor: "Solda", time: "13:30", cam: "CAM-02", img: "https://images.unsplash.com/photo-1616428753232-475470d172e2?q=80&w=400&auto=format&fit=crop" },
            { id: 4, nome: "", infra: "Sem Protetor Auricular", setor: "Prensas", time: "14:10", cam: "CAM-03", img: "https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?q=80&w=400&auto=format&fit=crop" },
            { id: 5, nome: "", infra: "Área Restrita", setor: "Almoxarifado", time: "15:45", cam: "CAM-01", img: "https://images.unsplash.com/photo-1535955575914-72274d4c5edb?q=80&w=400&auto=format&fit=crop" },
            { id: 6, nome: "", infra: "Sem Óculos de Proteção", setor: "Torno CNC", time: "16:22", cam: "CAM-01", img: "https://images.unsplash.com/photo-1581092921461-eab62e97a782?q=80&w=400&auto=format&fit=crop" },
        ];

        // --- 3. RENDERIZAÇÃO DOS CARDS ---
        const container = document.getElementById('cardsContainer');

        ocorrencias.forEach(item => {
            const card = document.createElement('div');
            card.className = 'violation-card';
            card.onclick = () => openModal(item);

            card.innerHTML = `
                <div class="card-image-wrapper">
                    <img src="${item.img}" class="card-image" alt="Foto da infração">
                    <div class="card-overlay">
                        <span class="zoom-icon">🔍</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-header-row">
                        <span class="violation-tag">${item.infra}</span>
                        <span class="camera-id">${item.cam}</span>
                    </div>
                    <span class="infrator-name">${item.nome}</span>
                    <div class="timestamp">
                        <span>🕒</span> ${item.time} • ${item.setor}
                    </div>
                </div>
            `;
            container.appendChild(card);
        });

        // --- 4. LÓGICA DO MODAL ---
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImg');
        const modalName = document.getElementById('modalName');
        const modalDesc = document.getElementById('modalDesc');
        const modalTime = document.getElementById('modalTime');
        const modalCam = document.getElementById('modalCam');

        function openModal(data) {
            modalImg.src = data.img;
            modalName.innerText = data.nome;
            modalDesc.innerText = `Infração: ${data.infra}`;
            modalTime.innerText = `Horário: ${data.time}`;
            modalCam.innerText = `Câmera: ${data.cam}`;
            modal.classList.add('active');
        }

        function closeModal(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        }

        function forceClose() {
            modal.classList.remove('active');
        }

        // --- 5. SCRIPT DE TRANSIÇÃO DE PÁGINA ---
        document.addEventListener("DOMContentLoaded", () => {
            const links = document.querySelectorAll('a.nav-item');

            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('javascript:')) return;

                    e.preventDefault();
                    document.body.classList.add('page-exit');

                    setTimeout(() => {
                        window.location.href = href;
                    }, 400);
                });
            });
        });
