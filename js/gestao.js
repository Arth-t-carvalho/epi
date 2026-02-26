document.addEventListener("DOMContentLoaded", function () {

    // ==============================
    // INICIALIZA ÍCONES
    // ==============================
    lucide.createIcons();


    // ==============================
    // MODAL
    // ==============================
    const modal = document.getElementById('userModal');
    const form = document.getElementById('formNovoUsuario');

    window.openModal = function () {
        modal.classList.add('active');
    }

    window.closeModal = function () {
        modal.classList.remove('active');
        if (form) form.reset();
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            alert('Usuário salvo com sucesso! (Simulação visual)');
            closeModal();
        });
    }


    // ==============================
    // DARK MODE
    // ==============================

    const html = document.documentElement;
    const savedTheme = localStorage.getItem("theme");

    // Aplica tema salvo ao carregar
    if (savedTheme) {
        html.setAttribute("data-theme", savedTheme);
    }

    // Função para alternar tema
    window.toggleTheme = function () {
        const currentTheme = html.getAttribute("data-theme");

        if (currentTheme === "dark") {
            html.setAttribute("data-theme", "light");
            localStorage.setItem("theme", "light");
        } else {
            html.setAttribute("data-theme", "dark");
            localStorage.setItem("theme", "dark");
        }
    };


    // ==============================
    // COR PERSONALIZADA DOS GRÁFICOS
    // ==============================
    const corDestaque = localStorage.getItem('chartColor');
    if (corDestaque) {
        html.style.setProperty('--primary', corDestaque);
    }

});