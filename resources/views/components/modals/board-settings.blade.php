<!-- Board Settings Modal -->
<div class="modal fade" id="boardSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="material-icons me-2">settings</span>
                    Configurações do Quadro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <!-- Settings Navigation -->
                        <div class="nav flex-column nav-pills" id="settings-tab" role="tablist">
                            <button class="nav-link active d-flex align-items-center text-start" id="general-tab" data-bs-toggle="pill" 
                                    data-bs-target="#general" type="button" role="tab">
                                <span class="material-icons me-2">info</span>
                                Geral
                            </button>
                            <button class="nav-link d-flex align-items-center text-start" id="members-tab" data-bs-toggle="pill" 
                                    data-bs-target="#members" type="button" role="tab">
                                <span class="material-icons me-2">group</span>
                                Membros
                            </button>
                            <button class="nav-link d-flex align-items-center text-start" id="labels-tab" data-bs-toggle="pill" 
                                    data-bs-target="#labels" type="button" role="tab">
                                <span class="material-icons me-2">label</span>
                                Labels
                            </button>
                            <button class="nav-link d-flex align-items-center text-start" id="automation-tab" data-bs-toggle="pill" 
                                    data-bs-target="#automation" type="button" role="tab">
                                <span class="material-icons me-2">smart_toy</span>
                                Automação
                            </button>
                            <button class="nav-link text-danger d-flex align-items-center text-start" id="danger-tab" data-bs-toggle="pill" 
                                    data-bs-target="#danger" type="button" role="tab">
                                <span class="material-icons me-2">warning</span>
                                Zona de Perigo
                            </button>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <!-- Settings Content -->
                        <div class="tab-content" id="settings-content">
                            <!-- General Settings -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <h6 class="mb-3">Informações Gerais</h6>
                                <form id="board-general-form">
                                    <div class="mb-3">
                                        <label for="board-settings-name" class="form-label">Nome do Quadro</label>
                                        <input type="text" class="form-control" id="board-settings-name" 
                                               name="name" maxlength="255" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="board-settings-key" class="form-label">Chave do Quadro</label>
                                        <input type="text" class="form-control" id="board-settings-key" 
                                               name="key" maxlength="10" style="text-transform: uppercase;" required>
                                        <div class="form-text">
                                            Usada para gerar referências de tarefas (ex: WEB-001)
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="material-icons me-2">save</span>
                                        Salvar Alterações
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Members Management -->
                            <div class="tab-pane fade" id="members" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Membros do Quadro</h6>
                                    <button class="btn btn-sm btn-primary" onclick="showInviteMemberForm()">
                                        <span class="material-icons me-2">person_add</span>
                                        Convidar
                                    </button>
                                </div>
                                
                                <!-- Invite Member Form -->
                                <div id="invite-member-form" class="card mb-3" style="display: none;">
                                    <div class="card-body">
                                        <h6 class="card-title">Convidar Novo Membro</h6>
                                        <form id="invite-form">
                                            <div class="row g-2">
                                                <div class="col">
                                                    <input type="email" class="form-control" id="invite-email" 
                                                           placeholder="email@exemplo.com" required>
                                                </div>
                                                <div class="col-auto">
                                                    <select class="form-select" id="invite-role">
                                                        <option value="owner">Proprietário</option>
                                                        <option value="editor">Editor</option>
                                                        <option value="viewer">Visualizador</option>
                                                    </select>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="submit" class="btn btn-primary">Convidar</button>
                                                    <button type="button" class="btn btn-secondary" onclick="hideInviteMemberForm()">
                                                        Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Members List -->
                                <div id="board-members-list">
                                    <!-- Members will be loaded here -->
                                </div>
                            </div>
                            
                            <!-- Labels Management -->
                            <div class="tab-pane fade" id="labels" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Labels do Quadro</h6>
                                    <button class="btn btn-sm btn-primary" onclick="showCreateLabelForm()">
                                        <span class="material-icons me-2">add</span>
                                        Nova Label
                                    </button>
                                </div>
                                
                                <!-- Create Label Form -->
                                <div id="create-label-form" class="card mb-3" style="display: none;">
                                    <div class="card-body">
                                        <h6 class="card-title">Criar Nova Label</h6>
                                        <form id="label-form">
                                            <div class="row g-2">
                                                <div class="col">
                                                    <input type="text" class="form-control" id="label-name" 
                                                           placeholder="Nome da label" maxlength="50" required>
                                                </div>
                                                <div class="col-auto">
                                                    <input type="color" class="form-control form-control-color" 
                                                           id="label-color" value="#6366f1">
                                                </div>
                                                <div class="col-auto">
                                                    <button type="button" class="btn btn-primary" id="btn-criar-label">Criar</button>
                                                    <button type="button" class="btn btn-secondary" onclick="hideCreateLabelForm()">
                                                        Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Labels List -->
                                <div id="board-labels-list">
                                    <!-- Labels will be loaded here -->
                                </div>
                            </div>
                            
                            <!-- Automation Settings -->
                            <div class="tab-pane fade" id="automation" role="tabpanel">
                                <h6 class="mb-3">Regras de Automação</h6>
                                <div class="alert alert-info">
                                    <span class="material-icons me-2">info</span>
                                    Funcionalidade de automação em desenvolvimento.
                                </div>
                                
                                <!-- Future automation rules -->
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Regras Disponíveis (Em Breve)</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <span class="material-icons me-2 text-muted">smart_toy</span>
                                                Auto-completar tarefas ao mover para coluna específica
                                            </li>
                                            <li class="mb-2">
                                                <span class="material-icons me-2 text-muted">smart_toy</span>
                                                Notificar por email quando tarefa vence
                                            </li>
                                            <li class="mb-2">
                                                <span class="material-icons me-2 text-muted">smart_toy</span>
                                                Arquivar tarefas antigas automaticamente
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Danger Zone -->
                            <div class="tab-pane fade" id="danger" role="tabpanel">
                                <h6 class="mb-3 text-danger">Zona de Perigo</h6>
                                <div class="alert alert-warning">
                                    <span class="material-icons me-2">warning</span>
                                    Ações nesta seção são irreversíveis. Proceda com cuidado.
                                </div>
                                
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h6 class="card-title">Arquivar Quadro</h6>
                                        <p class="card-text">
                                            Arquiva o quadro e todas as suas tarefas. O quadro ficará oculto 
                                            mas pode ser restaurado posteriormente.
                                        </p>
                                        <button class="btn btn-outline-warning" onclick="archiveBoard()">
                                            <span class="material-icons me-2">archive</span>
                                            Arquivar Quadro
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="card border-danger mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-danger">Excluir Quadro</h6>
                                        <p class="card-text">
                                            Exclui permanentemente o quadro e todas as suas tarefas, 
                                            colunas e dados relacionados. Esta ação não pode ser desfeita.
                                        </p>
                                        <button class="btn btn-danger" onclick="deleteBoardConfirm()">
                                            <span class="material-icons me-2">delete_forever</span>
                                            Excluir Quadro
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load board settings when modal is shown
    $('#boardSettingsModal').on('show.bs.modal', function() {
        loadBoardSettings();
        loadBoardMembers();
        loadBoardLabels();
    });
    
    // Form submissions
    $('#board-general-form').on('submit', function(e) {
        e.preventDefault();
        saveBoardSettings();
    });
    
    $('#invite-form').on('submit', function(e) {
        e.preventDefault();
        inviteMember();
    });
    
    $('#btn-criar-label').on('click', function(e) {
        e.preventDefault();
        createBoardLabel();
    });
});

function loadBoardSettings() {
    if (!currentBoardData) return;
    
    $('#board-settings-name').val(currentBoardData.name);
    $('#board-settings-key').val(currentBoardData.key);
}

function saveBoardSettings() {
    const $form = $('#board-general-form');
    const $submitBtn = $form.find('button[type="submit"]');
    const token = localStorage.getItem('auth_token');
    
    const formData = {
        name: $('#board-settings-name').val().trim(),
        key: $('#board-settings-key').val().trim()
    };
    
    setLoading($submitBtn, true);
    
    $.ajax({
        url: `/api/boards/${currentBoardId}`,
        method: 'PATCH',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                showAlert('Configurações salvas com sucesso!', 'success');
                currentBoardData = { ...currentBoardData, ...formData };
                $('#board-title').text(formData.name);
                
                // Update board selector
                $(`#board-selector option[value="${currentBoardId}"]`).text(formData.name);
            }
        },
        error: function(xhr) {
            let message = 'Erro ao salvar configurações.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert(message, 'danger');
        },
        complete: function() {
            setLoading($submitBtn, false);
        }
    });
}

function loadBoardMembers() {
    if (!currentBoardData || !currentBoardData.board_users) return;
    
    const $container = $('#board-members-list');
    $container.empty();
    
    currentBoardData.board_users.forEach(boardUser => {
        if (boardUser.user) {
            const user = boardUser.user;
            const role = boardUser.role_in_board;
            const isCurrentUser = JSON.parse(localStorage.getItem('user_data')).id === user.id;
            
            $container.append(`
                <div class="card mb-2">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    ${user.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <div class="fw-medium">
                                        ${escapeHtml(user.name)}
                                        ${isCurrentUser ? '<span class="badge bg-secondary ms-2">Você</span>' : ''}
                                    </div>
                                    <small class="text-muted">${escapeHtml(user.email)}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-${role === 'owner' ? 'primary' : role === 'admin' ? 'warning' : 'secondary'}">
                                    ${role === 'owner' ? 'Proprietário' : role === 'admin' ? 'Admin' : 'Membro'}
                                </span>
                                ${!isCurrentUser && role !== 'owner' ? `
                                    <button class="btn btn-sm btn-outline-danger" onclick="removeMember(${user.id})">
                                        <span class="material-icons" style="font-size: 16px;">remove</span>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
    });
}

function showInviteMemberForm() {
    $('#invite-member-form').show();
    $('#invite-email').focus();
}

function hideInviteMemberForm() {
    $('#invite-member-form').hide();
    $('#invite-form')[0].reset();
}

function inviteMember() {
    const email = $('#invite-email').val().trim();
    const role = $('#invite-role').val();
    const token = localStorage.getItem('auth_token');
    
    if (!email) {
        showAlert('Email é obrigatório.', 'warning');
        return;
    }
    
    $.ajax({
        url: `/api/board-users`,
        method: 'POST',
        data: JSON.stringify({ 
            email: email, 
            role_in_board: role,
            board_id: currentBoardId
        }),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                hideInviteMemberForm();
                showAlert(response.message || 'Convite enviado com sucesso!', 'success');
                loadBoardData(currentBoardId); // Reload board data
            }
        },
        error: function(xhr) {
            let message = 'Erro ao enviar convite.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert(message, 'danger');
        }
    });
}

function removeMember(userId) {
    if (!confirm('Tem certeza que deseja remover este membro?')) return;
    
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/boards/${currentBoardId}/members/${userId}`,
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                showAlert('Membro removido com sucesso!', 'success');
                loadBoardData(currentBoardId); // Reload board data
            }
        },
        error: function(xhr) {
            let message = 'Erro ao remover membro.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert(message, 'danger');
        }
    });
}

function loadBoardLabels() {
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/boards/${currentBoardId}/labels`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                displayBoardLabels(response.data);
            }
        },
        error: function() {
            // Silently fail
        }
    });
}

function displayBoardLabels(labels) {
    const $container = $('#board-labels-list');
    $container.empty();
    
    if (labels.length === 0) {
        $container.html('<p class="text-muted">Nenhuma label criada ainda.</p>');
        return;
    }
    
    labels.forEach(label => {
        $container.append(`
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="rounded" style="width: 20px; height: 20px; background-color: ${label.color}; margin-right: 12px;"></div>
                            <span class="fw-medium">${escapeHtml(label.name)}</span>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLabel(${label.id})">
                            <span class="material-icons" style="font-size: 16px;">delete</span>
                        </button>
                    </div>
                </div>
            </div>
        `);
    });
}

function showCreateLabelForm() {
    $('#create-label-form').show();
    $('#label-name').focus();
}

function hideCreateLabelForm() {
    $('#create-label-form').hide();
    $('#label-form')[0].reset();
    $('#label-color').val('#6366f1');
}

function createBoardLabel() {
    const name = $('#label-name').val().trim();
    const color = $('#label-color').val();
    const token = localStorage.getItem('auth_token');
    
    if (!name) {
        showAlert('Nome da label é obrigatório.', 'warning');
        return;
    }
    
    $.ajax({
        url: '/api/labels',
        method: 'POST',
        data: JSON.stringify({
            name: name,
            color: color,
            board_id: currentBoardId
        }),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                hideCreateLabelForm();
                showAlert('Label criada com sucesso!', 'success');
                loadBoardLabels();
            }
        },
        error: function(xhr) {
            let message = 'Erro ao criar label.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert(message, 'danger');
        }
    });
}

function deleteLabel(labelId) {
    if (!confirm('Tem certeza que deseja excluir esta label?')) return;
    
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/labels/${labelId}`,
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                showAlert('Label excluída com sucesso!', 'success');
                loadBoardLabels();
            }
        },
        error: function(xhr) {
            let message = 'Erro ao excluir label.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert(message, 'danger');
        }
    });
}

function archiveBoard() {
    if (!confirm('Tem certeza que deseja arquivar este quadro?')) return;
    
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/boards/${currentBoardId}/archive`,
        method: 'PATCH',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                $('#boardSettingsModal').modal('hide');
                showAlert('Quadro arquivado com sucesso!', 'success');
                loadUserBoards();
                showEmptyState();
            }
        },
        error: function(xhr) {
            let message = 'Erro ao arquivar quadro.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert(message, 'danger');
        }
    });
}

function deleteBoardConfirm() {
    const boardName = currentBoardData?.name || 'o quadro';
    
    if (!confirm(`Tem certeza que deseja EXCLUIR PERMANENTEMENTE "${boardName}"?\n\nEsta ação não pode ser desfeita e todos os dados serão perdidos.`)) {
        return;
    }
    
    const confirmation = prompt(`Para confirmar, digite o nome do quadro: "${boardName}"`);
    if (confirmation !== boardName) {
        showAlert('Nome do quadro não confere. Operação cancelada.', 'warning');
        return;
    }
    
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/boards/${currentBoardId}`,
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                $('#boardSettingsModal').modal('hide');
                showAlert('Quadro excluído permanentemente.', 'info');
                loadUserBoards();
                showEmptyState();
            }
        },
        error: function(xhr) {
            let message = 'Erro ao excluir quadro.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert(message, 'danger');
        }
    });
}
</script>
