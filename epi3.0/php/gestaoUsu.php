<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPI GUARD | Gestão de Usuários</title>
    <!-- Fonte Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/gestao.css">
    <!-- Ícones Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
</head> 
<body>

    <!-- SIDEBAR ADMIN -->
    <aside class="sidebar">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E30613" stroke-width="3">
                <circle cx="12" cy="12" r="10" />
            </svg>
            &nbsp; EPI <span>GUARD</span>
            <span class="admin-badge">ADMIN</span>
        </div>

        <nav class="nav-menu">
            <!-- Item Ativo -->
            <a class="nav-item active" href="#">
                <i data-lucide="users"></i>
                <span>Gestão de Usuários</span>
            </a>
            
            <div style="flex: 1;"></div> <!-- Espaçador para jogar o botão de voltar para baixo -->
            
            <!-- Voltar ao Dashboard Normal -->
            <a class="nav-item" href="dashboard.php" style="color: var(--text-muted);">
                <i data-lucide="arrow-left"></i>
                <span>Voltar ao Dashboard</span>
            </a>
        </nav>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="main-content">

        <div class="header">
            <div>
                <h1>Gestão de Usuários</h1>
                <p>Gerencie o acesso de professores e administradores ao sistema.</p>
            </div>
        </div>

        <!-- KPIs DO ADMIN -->
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
                    <i data-lucide="shield-alert" size="28"></i>
                </div>
                <div class="summary-info">
                    <h3>Administradores</h3>
                    <p>3</p>
                </div>
            </div>
        </div>

        <!-- TABELA DE GESTÃO -->
        <div class="data-panel">
            <div class="panel-header">
                <h2>Lista de Usuários</h2>
                <button class="btn-primary" onclick="openModal()">
                    <i data-lucide="plus" size="18"></i> Novo Usuário
                </button>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Login</th>
                            <th>Nível de Acesso</th>
                            <th>Status</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Linha 1: Admin -->
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar" style="background: #3b82f6;">RS</div>
                                    <div>
                                        <div style="font-weight: 600;">Roberto Silva</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">roberto.silva@senai.br</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight: 500;">roberto.admin</td>
                            <td><span class="badge admin">Administrador</span></td>
                            <td><span class="badge ativo">Ativo</span></td>
                            <td>
                                <div class="actions" style="justify-content: flex-end;">
                                    <button class="btn-icon" title="Editar"><i data-lucide="edit-2" size="18"></i></button>
                                    <button class="btn-icon" title="Bloquear"><i data-lucide="lock" size="18"></i></button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Linha 2: Professor Ativo -->
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar" style="background: var(--secondary);">CO</div>
                                    <div>
                                        <div style="font-weight: 600;">Carlos Oliveira</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">carlos.oli@senai.br</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight: 500;">carlos.mec</td>
                            <td><span class="badge prof">Professor</span></td>
                            <td><span class="badge ativo">Ativo</span></td>
                            <td>
                                <div class="actions" style="justify-content: flex-end;">
                                    <button class="btn-icon" title="Editar"><i data-lucide="edit-2" size="18"></i></button>
                                    <button class="btn-icon" title="Bloquear"><i data-lucide="lock" size="18"></i></button>
                                    <button class="btn-icon" title="Excluir" style="color: var(--danger);"><i data-lucide="trash-2" size="18"></i></button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Linha 3: Professor Inativo -->
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
                                <div class="actions" style="justify-content: flex-end;">
                                    <button class="btn-icon" title="Editar"><i data-lucide="edit-2" size="18"></i></button>
                                    <button class="btn-icon" title="Desbloquear" style="color: var(--success);"><i data-lucide="unlock" size="18"></i></button>
                                    <button class="btn-icon" title="Excluir" style="color: var(--danger);"><i data-lucide="trash-2" size="18"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- MODAL DE NOVO USUÁRIO -->
    <div class="modal-overlay" id="userModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Cadastrar Novo Usuário</h2>
                <button class="close-modal" onclick="closeModal()">
                    <i data-lucide="x"></i>
                </button>
            </div>
            
            <form id="formNovoUsuario">
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