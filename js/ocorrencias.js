        // Funções do Header que faltavam
        function toggleInstructorCard() {
            const card = document.getElementById('instructorCard');
            card.classList.toggle('active');
        }

        function exportData() {
            alert("Exportando dados...");
        }

        document.addEventListener("DOMContentLoaded", () => {
            // --- 1. POPULAR DADOS FAKE (Simulando o que vem do Dashboard) ---
            const urlParams = new URLSearchParams(window.location.search);

            const studentName = urlParams.get('name') || "Arthur (Mecânica)";
            const epiMissing = urlParams.get('epi') || "Óculos de Proteção"; // Padrão se não vier nada

            // Preencher Aluno
            document.getElementById('studentNameInput').value = studentName;

            // Preencher Motivo (Já travado)
            document.getElementById('reasonInput').value = `Ausência de EPI: ${epiMissing}`;

            // Preencher Data/Hora Formatada
            const now = new Date();
            const formatted = now.toLocaleDateString('pt-BR') + ' às ' + now.toLocaleTimeString('pt-BR').substring(0, 5);
            document.getElementById('dateTimeInput').value = formatted;
        });

        // --- 2. LÓGICA DE FOTOS ADICIONAIS ---
        const fileInput = document.getElementById('fileInput');
        const gallery = document.getElementById('photoGallery');

        fileInput.addEventListener('change', function () {
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const div = document.createElement('div');
                        div.className = 'photo-wrapper-new'; // Classe sem borda vermelha

                        const img = document.createElement('img');
                        img.src = e.target.result;

                        div.appendChild(img);

                        // Inserir antes do botão "+"
                        const addBtn = gallery.lastElementChild;
                        gallery.insertBefore(div, addBtn);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });

        // Submit Mock
        document.getElementById('incidentForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-submit');
            btn.innerHTML = 'Salvando...';
            setTimeout(() => {
                alert('Ocorrência registrada com sucesso!');
                window.location.href = 'dashboard.html';
            }, 800);
        });


        function sair() {
            window.location.href = "index.html";
        }


        // Sistema de Tema
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
            }
        });

          window.toggleTheme = function() {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
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

    
    