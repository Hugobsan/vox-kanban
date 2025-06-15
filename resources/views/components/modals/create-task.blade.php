<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="material-icons me-2">add_task</span>
                    Criar Nova Tarefa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="create-task-form">
                <input type="hidden" id="task-column-id" name="column_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="task-title" class="form-label">Título da Tarefa *</label>
                                <input type="text" class="form-control" id="task-title" name="title" 
                                       placeholder="Ex: Implementar login de usuário" required maxlength="255">
                            </div>
                            
                            <div class="mb-3">
                                <label for="task-description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="task-description" name="description" rows="4" 
                                          placeholder="Descreva os detalhes da tarefa..." maxlength="2000"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="task-labels" class="form-label">Labels</label>
                                <div class="input-group">
                                    <select class="form-select" id="task-labels" multiple>
                                        <!-- Labels will be loaded dynamically -->
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" onclick="showCreateLabelForm()">
                                        <span class="material-icons">add</span>
                                    </button>
                                </div>
                                <div class="form-text">Selecione ou crie labels para categorizar a tarefa.</div>
                            </div>
                            
                            <!-- Quick Label Creation -->
                            <div id="quick-label-form" class="mb-3" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Criar Nova Label</h6>
                                        <div class="row g-2">
                                            <div class="col">
                                                <input type="text" class="form-control" id="new-label-name" 
                                                       placeholder="Nome da label" maxlength="50">
                                            </div>
                                            <div class="col-auto">
                                                <input type="color" class="form-control form-control-color" 
                                                       id="new-label-color" value="#6366f1">
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="btn btn-sm btn-primary" onclick="createQuickLabel()">
                                                    <span class="material-icons">add</span>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-secondary" onclick="hideCreateLabelForm()">
                                                    <span class="material-icons">close</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="task-priority" class="form-label">Prioridade</label>
                                <select class="form-select" id="task-priority" name="priority">
                                    <option value="low">Baixa</option>
                                    <option value="medium" selected>Média</option>
                                    <option value="high">Alta</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="task-due-date" class="form-label">Data de Vencimento</label>
                                <input type="date" class="form-control" id="task-due-date" name="due_date">
                            </div>
                            
                            <div class="mb-3">
                                <label for="task-assignees" class="form-label">Responsáveis</label>
                                <select class="form-select" id="task-assignees" multiple>
                                    <!-- Board users will be loaded dynamically -->
                                </select>
                                <div class="form-text">Selecione os usuários responsáveis pela tarefa.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="task-estimated-hours" class="form-label">Estimativa (horas)</label>
                                <input type="number" class="form-control" id="task-estimated-hours" 
                                       name="estimated_hours" placeholder="Ex: 8" min="0.5" max="1000" step="0.5">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons me-2">add_task</span>
                        Criar Tarefa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Create task form submission
    $('#create-task-form').on('submit', function(e) {
        e.preventDefault();
        createTask();
    });
    
    // Load data when modal is shown
    $('#createTaskModal').on('show.bs.modal', function() {
        loadBoardLabels();
        loadBoardUsers();
    });
    
    // Reset form when modal is hidden
    $('#createTaskModal').on('hidden.bs.modal', function() {
        $('#create-task-form')[0].reset();
        $('#task-labels').empty();
        $('#task-assignees').empty();
        hideCreateLabelForm();
    });
    
    // Set minimum date to today
    $('#task-due-date').attr('min', new Date().toISOString().split('T')[0]);
});

function createTask() {
    const $form = $('#create-task-form');
    const $submitBtn = $form.find('button[type="submit"]');
    const token = localStorage.getItem('auth_token');
    
    const columnId = $('#task-column-id').val();
    if (!columnId) {
        showAlert('Coluna não especificada.', 'warning');
        return;
    }
    
    const formData = {
        title: $('#task-title').val().trim(),
        description: $('#task-description').val().trim(),
        priority: $('#task-priority').val(),
        due_date: $('#task-due-date').val() || null,
        estimated_hours: $('#task-estimated-hours').val() || null,
        column_id: columnId,
        labels: $('#task-labels').val() || [],
        assignees: $('#task-assignees').val() || []
    };
    
    // Validation
    if (!formData.title) {
        showAlert('Título da tarefa é obrigatório.', 'danger');
        return;
    }
    
    setLoading($submitBtn, true);
    
    // Optimistic update - add task to UI immediately
    const tempTask = {
        id: 'temp-' + Date.now(),
        title: formData.title,
        description: formData.description,
        priority: formData.priority,
        due_date: formData.due_date,
        labels: [], // Will be populated after creation
        assignees: [],
        completed: false
    };
    
    const $column = $(`.kanban-column-body[data-column-id="${columnId}"]`);
    const $ghostCard = $column.find('.ghost-card');
    const $newTask = $(createTaskElement(tempTask));
    $ghostCard.before($newTask);
    
    $.ajax({
        url: '/api/tasks',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                $('#createTaskModal').modal('hide');
                $form[0].reset();
                showAlert(response.message || 'Tarefa criada com sucesso!', 'success');
                
                // Replace temp task with real data
                $newTask.attr('data-task-id', response.data.id);
                $newTask.attr('onclick', `showTaskDetails(${response.data.id})`);
                
                // Update task content with real data
                $newTask.replaceWith(createTaskElement(response.data));
                
                // Update task count in column header
                updateColumnTaskCount(columnId);
            }
        },
        error: function(xhr) {
            // Remove optimistic task on error
            $newTask.remove();
            
            let message = 'Erro ao criar tarefa.';
            
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('<br>');
                }
            }
            
            showAlert(message, 'danger');
        },
        complete: function() {
            setLoading($submitBtn, false);
        }
    });
}

function loadBoardLabels() {
    if (!currentBoardId) return;
    
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/boards/${currentBoardId}/labels`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                const $select = $('#task-labels');
                $select.empty();
                
                response.data.forEach(label => {
                    $select.append(`
                        <option value="${label.id}" style="background-color: ${label.color};">
                            ${escapeHtml(label.name)}
                        </option>
                    `);
                });
            }
        },
        error: function() {
            // Silently fail - labels are optional
        }
    });
}

function loadBoardUsers() {
    if (!currentBoardData || !currentBoardData.board_users) return;
    
    const $select = $('#task-assignees');
    $select.empty();
    
    currentBoardData.board_users.forEach(boardUser => {
        if (boardUser.user) {
            $select.append(`
                <option value="${boardUser.user.id}">
                    ${escapeHtml(boardUser.user.name)}
                </option>
            `);
        }
    });
}

function showCreateLabelForm() {
    $('#quick-label-form').show();
    $('#new-label-name').focus();
}

function hideCreateLabelForm() {
    $('#quick-label-form').hide();
    $('#new-label-name').val('');
    $('#new-label-color').val('#6366f1');
}

function createQuickLabel() {
    const name = $('#new-label-name').val().trim();
    const color = $('#new-label-color').val();
    
    if (!name) {
        showAlert('Nome da label é obrigatório.', 'warning');
        return;
    }
    
    const token = localStorage.getItem('auth_token');
    
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
                const label = response.data;
                $('#task-labels').append(`
                    <option value="${label.id}" style="background-color: ${label.color};" selected>
                        ${escapeHtml(label.name)}
                    </option>
                `);
                
                hideCreateLabelForm();
                showAlert('Label criada com sucesso!', 'success');
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

function updateColumnTaskCount(columnId) {
    const $column = $(`.kanban-column[data-column-id="${columnId}"]`);
    const taskCount = $column.find('.kanban-card:not(.ghost-card)').length;
    $column.find('.badge').text(taskCount);
}
</script>
