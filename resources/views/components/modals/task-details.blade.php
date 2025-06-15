<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <span class="material-icons me-2">task</span>
                    <div>
                        <h5 class="modal-title mb-0" id="task-detail-title">Carregando...</h5>
                        <small class="text-muted" id="task-detail-reference"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-md-8">
                        <!-- Description -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">
                                    <span class="material-icons me-2">description</span>
                                    Descrição
                                </h6>
                                <button class="btn btn-sm btn-outline-secondary" onclick="editTaskDescription()">
                                    <span class="material-icons">edit</span>
                                </button>
                            </div>
                            <div id="task-description-view">
                                <p class="text-muted" id="task-description-content">Sem descrição</p>
                            </div>
                            <div id="task-description-edit" style="display: none;">
                                <textarea class="form-control mb-2" id="task-description-editor" rows="4"></textarea>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary" onclick="saveTaskDescription()">Salvar</button>
                                    <button class="btn btn-sm btn-secondary" onclick="cancelEditDescription()">Cancelar</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comments Section -->
                        <div class="mb-4">
                            <h6 class="mb-3">
                                <span class="material-icons me-2">comment</span>
                                Comentários <span class="badge bg-secondary" id="comments-count">0</span>
                            </h6>
                            
                            <!-- Add Comment -->
                            <div class="mb-3">
                                <div class="d-flex gap-2">
                                    <div class="flex-grow-1">
                                        <textarea class="form-control" id="new-comment" rows="2" 
                                                  placeholder="Adicione um comentário..."></textarea>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <button class="btn btn-sm btn-primary" onclick="addComment()" id="add-comment-btn">
                                            <span class="material-icons">send</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Comments List -->
                            <div id="comments-list">
                                <!-- Comments will be loaded here -->
                            </div>
                        </div>
                        
                        <!-- Activity Log -->
                        <div>
                            <h6 class="mb-3">
                                <span class="material-icons me-2">history</span>
                                Atividades
                            </h6>
                            <div id="activity-list">
                                <!-- Activities will be loaded here -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="col-md-4">
                        <div class="border-start ps-4">
                            <!-- Status -->
                            <div class="mb-4">
                                <h6 class="mb-2">Status</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary" id="task-status">Em Progresso</span>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editTaskStatus()">
                                        <span class="material-icons">edit</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Assignees -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Responsáveis</h6>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editTaskAssignees()">
                                        <span class="material-icons">edit</span>
                                    </button>
                                </div>
                                <div id="task-assignees-list">
                                    <!-- Assignees will be loaded here -->
                                </div>
                            </div>
                            
                            <!-- Labels -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Labels</h6>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editTaskLabels()">
                                        <span class="material-icons">edit</span>
                                    </button>
                                </div>
                                <div id="task-labels-list">
                                    <!-- Labels will be loaded here -->
                                </div>
                            </div>
                            
                            <!-- Due Date -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Data de Vencimento</h6>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editTaskDueDate()">
                                        <span class="material-icons">edit</span>
                                    </button>
                                </div>
                                <div id="task-due-date-display">Não definida</div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="duplicateTask(currentTaskId)">
                                    <span class="material-icons me-2">content_copy</span>
                                    Duplicar
                                </button>
                                <button class="btn btn-outline-secondary" onclick="moveTaskToColumn()">
                                    <span class="material-icons me-2">swap_horiz</span>
                                    Mover
                                </button>
                                <button class="btn btn-outline-warning" onclick="archiveTask(currentTaskId)">
                                    <span class="material-icons me-2">archive</span>
                                    Arquivar
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteTask(currentTaskId)">
                                    <span class="material-icons me-2">delete</span>
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentTaskId = null;
let currentTaskData = null;

function loadTaskDetails(taskId) {
    currentTaskId = taskId;
    const token = localStorage.getItem('auth_token');
    
    // Show loading state
    $('#task-detail-title').text('Carregando...');
    $('#task-detail-reference').text('');
    
    $.ajax({
        url: `/api/tasks/${taskId}`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                currentTaskData = response.data;
                displayTaskDetails(response.data);
                loadTaskComments(taskId);
                loadTaskActivity(taskId);
            }
        },
        error: function(xhr) {
            showAlert('Erro ao carregar detalhes da tarefa.', 'danger');
            $('#taskDetailsModal').modal('hide');
        }
    });
}

function displayTaskDetails(task) {
    // Header
    $('#task-detail-title').text(task.title);
    $('#task-detail-reference').text(task.reference || `#${task.id}`);
    
    // Description
    $('#task-description-content').text(task.description || 'Sem descrição');
    $('#task-description-editor').val(task.description || '');
    
    // Status
    $('#task-status').text(task.column?.name || 'Indefinido');
    
    // Assignees
    displayTaskAssignees(task.assignees || []);
    
    // Labels
    displayTaskLabels(task.labels || []);
    
    // Due Date
    $('#task-due-date-display').text(
        task.due_date ? formatDate(task.due_date) : 'Não definida'
    );
}

function displayTaskAssignees(assignees) {
    const $container = $('#task-assignees-list');
    $container.empty();
    
    if (assignees.length === 0) {
        $container.html('<span class="text-muted">Nenhum responsável</span>');
        return;
    }
    
    assignees.forEach(assignee => {
        $container.append(`
            <div class="d-flex align-items-center mb-2">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                     style="width: 32px; height: 32px; font-size: 12px;">
                    ${assignee.name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <div class="fw-medium">${escapeHtml(assignee.name)}</div>
                    <small class="text-muted">${escapeHtml(assignee.email)}</small>
                </div>
            </div>
        `);
    });
}

function displayTaskLabels(labels) {
    const $container = $('#task-labels-list');
    $container.empty();
    
    if (labels.length === 0) {
        $container.html('<span class="text-muted">Nenhuma label</span>');
        return;
    }
    
    labels.forEach(label => {
        $container.append(`
            <span class="badge me-1 mb-1" style="background-color: ${label.color};">
                ${escapeHtml(label.name)}
            </span>
        `);
    });
}

function loadTaskComments(taskId) {
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/tasks/${taskId}/comments`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                displayTaskComments(response.data);
                $('#comments-count').text(response.data.length);
            }
        },
        error: function() {
            // Silently fail
        }
    });
}

function displayTaskComments(comments) {
    const $container = $('#comments-list');
    $container.empty();
    
    if (comments.length === 0) {
        $container.html('<p class="text-muted">Nenhum comentário ainda.</p>');
        return;
    }
    
    comments.forEach(comment => {
        $container.append(`
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                             style="width: 32px; height: 32px; font-size: 12px;">
                            ${comment.user.name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="fw-medium">${escapeHtml(comment.user.name)}</div>
                            <small class="text-muted">${formatDateTime(comment.created_at)}</small>
                        </div>
                    </div>
                </div>
                <div>${escapeHtml(comment.content)}</div>
            </div>
        `);
    });
}

function addComment() {
    const content = $('#new-comment').val().trim();
    if (!content) return;
    
    const token = localStorage.getItem('auth_token');
    const $btn = $('#add-comment-btn');
    
    setLoading($btn, true);
    
    $.ajax({
        url: `/api/tasks/${currentTaskId}/comments`,
        method: 'POST',
        data: JSON.stringify({ content: content }),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                $('#new-comment').val('');
                loadTaskComments(currentTaskId);
                showAlert('Comentário adicionado!', 'success');
            }
        },
        error: function() {
            showAlert('Erro ao adicionar comentário.', 'danger');
        },
        complete: function() {
            setLoading($btn, false);
        }
    });
}

function loadTaskActivity(taskId) {
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/tasks/${taskId}/activity`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                displayTaskActivity(response.data);
            }
        },
        error: function() {
            // Silently fail
        }
    });
}

function displayTaskActivity(activities) {
    const $container = $('#activity-list');
    $container.empty();
    
    if (activities.length === 0) {
        $container.html('<p class="text-muted">Nenhuma atividade registrada.</p>');
        return;
    }
    
    activities.forEach(activity => {
        $container.append(`
            <div class="d-flex align-items-start mb-3">
                <span class="material-icons text-muted me-2 mt-1" style="font-size: 16px;">
                    ${getActivityIcon(activity.type)}
                </span>
                <div class="flex-grow-1">
                    <div class="small">${activity.description}</div>
                    <small class="text-muted">${formatDateTime(activity.created_at)}</small>
                </div>
            </div>
        `);
    });
}

function getActivityIcon(type) {
    const icons = {
        'created': 'add',
        'updated': 'edit',
        'moved': 'swap_horiz',
        'completed': 'check_circle',
        'commented': 'comment',
        'assigned': 'person_add',
        'labeled': 'label'
    };
    return icons[type] || 'info';
}

// Edit functions
function editTaskDescription() {
    $('#task-description-view').hide();
    $('#task-description-edit').show();
    $('#task-description-editor').focus();
}

function saveTaskDescription() {
    const newDescription = $('#task-description-editor').val();
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/tasks/${currentTaskId}`,
        method: 'PATCH',
        data: JSON.stringify({ description: newDescription }),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                $('#task-description-content').text(newDescription || 'Sem descrição');
                cancelEditDescription();
                showAlert('Descrição atualizada!', 'success');
            }
        },
        error: function() {
            showAlert('Erro ao atualizar descrição.', 'danger');
        }
    });
}

function cancelEditDescription() {
    $('#task-description-edit').hide();
    $('#task-description-view').show();
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

// Task action functions
function editTask(taskId) {
    showAlert('Funcionalidade de edição em desenvolvimento.', 'info');
}

function toggleTaskComplete(taskId) {
    showAlert('Funcionalidade de marcar como concluído em desenvolvimento.', 'info');
}

function duplicateTask(taskId) {
    showAlert('Funcionalidade de duplicar tarefa em desenvolvimento.', 'info');
}

function deleteTask(taskId) {
    if (!confirm('Tem certeza que deseja excluir esta tarefa?')) return;
    
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/tasks/${taskId}`,
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                $('#taskDetailsModal').modal('hide');
                $(`.kanban-card[data-task-id="${taskId}"]`).remove();
                showAlert('Tarefa excluída com sucesso!', 'success');
            }
        },
        error: function() {
            showAlert('Erro ao excluir tarefa.', 'danger');
        }
    });
}
</script>
