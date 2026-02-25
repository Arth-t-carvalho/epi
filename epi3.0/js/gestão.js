        // Inicializa os ícones do Lucide
        lucide.createIcons();

        // Lógica do Modal
        const modal = document.getElementById('userModal');
        const form = document.getElementById('formNovoUsuario');

        function openModal() {
            modal.classList.add('active');
        }

        function closeModal() {
            modal.classList.remove('active');
            form.reset(); // Limpa o formulário ao fechar
        }

        // Fechar modal ao clicar fora da caixa branca
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Simulação do envio do formulário
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Aqui você colocaria o código AJAX/Fetch para enviar para o PHP
            // fetch('seu_arquivo_php.php', { method: 'POST', body: new FormData(this) }) ...
            
            alert('Usuário salvo com sucesso! (Esta é uma simulação visual)');
            closeModal();
        });