<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI GUARD | Gestão de Usuários</title>
    <link rel="stylesheet" href="../css/gestao.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
     <link rel="stylesheet" href="../css/dashboard.css">
     <link rel="stylesheet" href="../css/nav.css">
        <link rel="stylesheet" href="../css/Dark.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/theme-variables.css">
</head> 
<body>
</head> 
<body>

<aside class="sidebar">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3"
                style="filter: drop-shadow(0 2px 4px rgba(227, 6, 19, 0.3));">
                <circle cx="12" cy="12" r="10" />
            </svg>

            &nbsp; EPI <span>GUARD</span>
        </div>

        <nav class="nav-menu">

            <a class="nav-item" href="dashboard.php">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>

            <a class="nav-item " href="infracoes.php">
                <i data-lucide="alert-triangle"></i>
                <span>Infrações</span>
            </a>
            
            <a class="nav-item active" href="gestaoUsu.php">
                <i data-lucide="users"></i>
                <span>Gestão</span>
            </a>

            <a class="nav-item" href="controleSala.php">
                <i data-lucide="school"></i>
                <span>Controle de Sala</span>
            </a>

            <a class="nav-item " href="ocorrencias.php">
                <i data-lucide="file-text"></i>
                <span>Ocorrências</span>
            </a>

            <a class="nav-item" href="configuracoes.php">
                <i data-lucide="settings"></i>
                <span>Configurações</span>
            </a>
   
        </nav>
    </aside>

    <main class="main-content">

        <div class="header">
            <div>
                <h1>Gestão de Usuários</h1>
                <p>Gerencie o acesso de professores e administradores ao sistema.</p>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-icon icon-blue">
                    <i data-lucide="users" size="28"></i>
                </div>
                <div class="summary-info">
                    <h3>Total de Usuários</h3>
                    <p>24</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon icon-green">
                    <i data-lucide="user-check" size="28"></i>
                </div>
                <div class="summary-info">
                    <h3>Professores Ativos</h3>
                    <p>18</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon icon-red">
                    <i data-lucide="shield" size="28"></i>
                </div>
                <div class="summary-info">
                    <h3>Administradores</h3>
                    <p>6</p>
                </div>
            </div>
        </div>

        <div class="data-panel">
            <div class="panel-header">
                <h2>Usuários Cadastrados</h2>
                <button class="btn-primary" onclick="openModal()">
                    <i data-lucide="user-plus"></i>
                    Novo Usuário
                </button>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Usuário / E-mail</th>
                            <th>Login</th>
                            <th>Nível de Acesso</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar" style="background: var(--secondary);">CH</div>
                                    <div>
                                        <div style="font-weight: 600;">Carlos Henrique</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">carlos.h@senai.br</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight: 500;">carlos.h</td>
                            <td><span class="badge admin">Administrador</span></td>
                            <td><span class="badge ativo">Ativo</span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon"><i data-lucide="edit-2"></i></button>
                                    <button class="btn-icon" style="color: var(--danger);"><i data-lucide="trash-2"></i></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar" style="background: var(--primary);">MC</div>
                                    <div>
                                        <div style="font-weight: 600;">Marcos Costa</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">marcos.costa@senai.br</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight: 500;">marcos.meca</td>
                            <td><span class="badge prof">Professor</span></td>
                            <td><span class="badge ativo">Ativo</span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon"><i data-lucide="edit-2"></i></button>
                                    <button class="btn-icon" style="color: var(--danger);"><i data-lucide="trash-2"></i></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar" style="background: #94a3b8;">AS</div>
                                    <div>
                                        <div style="font-weight: 600;">Amanda Santos</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">amanda.santos@senai.br</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight: 500;">amanda.ele</td>
                            <td><span class="badge prof">Professor</span></td>
                            <td><span class="badge inativo">Inativo</span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon"><i data-lucide="edit-2"></i></button>
                                    <button class="btn-icon" style="color: var(--danger);"><i data-lucide="trash-2"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <div class="modal-overlay" id="userModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Cadastrar Novo Usuário</h2>
                <button class="close-modal" onclick="closeModal()">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <form onsubmit="event.preventDefault(); alert('Usuário salvo com sucesso!'); closeModal();">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" class="form-control" placeholder="Ex: João da Silva" required>
                </div>
                
                <div class="form-group">
                    <label>Login de Acesso</label>
                    <input type="text" class="form-control" placeholder="Ex: joao.silva" required>
                </div>
                
                <div class="form-group">
                    <label>Nível de Acesso</label>
                    <select class="form-control" required>
                        <option value="professor">Professor (Visualiza apenas suas turmas)</option>
                        <option value="admin">Administrador (Acesso total ao sistema)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Senha Provisória</label>
                    <input type="password" class="form-control" placeholder="Mínimo de 6 caracteres" required>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-outline" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar Usuário</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../js/chart-manager.js"></script>

    <script src="../js/gestao.js"></script>
</body>
</html> 