@extends('layouts.app')

@section('title', 'Quadro Kanban')

@section('main-class', 'container-fluid p-0')

@section('content')
<div class="kanban-board-container">
    <!-- Header do Quadro -->
    <div class="board-header bg-white shadow-sm border-bottom">
        <div class="container-fluid p-3">
            <div class="row align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center">
                        <a href="/dashboard" class="btn btn-light me-3">
                            <span class="material-icons">arrow_back</span>
                        </a>
                        <div>
                            <h3 class="mb-0" id="board-title">Carregando...</h3>
                            <p class="text-muted mb-0" id="board-description">Carregando descrição...</p>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="showBoardSettings()">
                            <span class="material-icons me-1">settings</span>
                            Configurações
                        </button>
                        <button class="btn btn-primary" onclick="showAddColumn()">
                            <span class="material-icons me-1">add</span>
                            Nova Coluna
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <div class="container-fluid p-3">
        <div id="alerts-container"></div>
    </div>

    <!-- Kanban Board -->
    <div class="kanban-board" id="kanban-board">
        <div class="kanban-columns-container" id="columns-container">
            <!-- Loading state -->
            <div id="board-loading" class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="text-muted">Carregando quadro...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal - Adicionar Coluna -->
<div class="modal fade" id="addColumnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="material-icons me-2">view_column</span>
                    Adicionar Nova Coluna
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="add-column-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="column-name" class="form-label">Nome da Coluna</label>
                        <input type="text" class="form-control" id="column-name" name="name" placeholder="Ex: Em Revisão" required>
                    </div>
                    <div class="mb-3">
                        <label for="column-color" class="form-label">Cor da Coluna</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" class="form-control form-control-color" id="column-color" name="color" value="#6366f1">
                            <span class="text-muted">Opcional</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons me-1">add</span>
                        Adicionar Coluna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal - Adicionar Task -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="material-icons me-2">add_task</span>
                    Adicionar Nova Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="add-task-form">
                <div class="modal-body">
                    <input type="hidden" id="task-column-id" name="column_id">
                    <div class="mb-3">
                        <label for="task-title" class="form-label">Título da Task</label>
                        <input type="text" class="form-control" id="task-title" name="title" placeholder="Ex: Implementar login" required>
                    </div>
                    <div class="mb-3">
                        <label for="task-description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="task-description" name="description" rows="3" placeholder="Descreva os detalhes da task..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="task-due-date" class="form-label">Data de Vencimento</label>
                            <input type="date" class="form-control" id="task-due-date" name="due_date">
                        </div>
                        <div class="col-md-6">
                            <label for="task-assigned-user" class="form-label">Responsável</label>
                            <select class="form-select" id="task-assigned-user" name="assigned_user_id">
                                <option value="">Sem responsável</option>
                                <!-- Usuários serão carregados dinamicamente -->
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons me-1">add</span>
                        Criar Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal - Editar Task -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="material-icons me-2">edit</span>
                    Editar Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="edit-task-form">
                <div class="modal-body">
                    <input type="hidden" id="edit-task-id">
                    <div class="mb-3">
                        <label for="edit-task-title" class="form-label">Título da Task</label>
                        <input type="text" class="form-control" id="edit-task-title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-task-description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="edit-task-description" name="description" rows="4"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="edit-task-due-date" class="form-label">Data de Vencimento</label>
                            <input type="date" class="form-control" id="edit-task-due-date" name="due_date">
                        </div>
                        <div class="col-md-6">
                            <label for="edit-task-assigned-user" class="form-label">Responsável</label>
                            <select class="form-select" id="edit-task-assigned-user" name="assigned_user_id">
                                <option value="">Sem responsável</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Labels</label>
                        <div id="task-labels-container">
                            <!-- Labels serão carregadas dinamicamente -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" onclick="deleteTask()">
                        <span class="material-icons me-1">delete</span>
                        Excluir
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons me-1">save</span>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.kanban-board-container {
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.kanban-board {
    flex: 1;
    overflow: hidden;
}

.kanban-columns-container {
    display: flex;
    gap: 20px;
    padding: 20px;
    height: 100%;
    overflow-x: auto;
    overflow-y: hidden;
}

.kanban-column {
    min-width: 320px;
    width: 320px;
    background-color: #f8fafc;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    height: fit-content;
    max-height: calc(100vh - 200px);
}

.column-header {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    background: white;
    border-radius: 12px 12px 0 0;
}

.column-title {
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.column-tasks {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    min-height: 200px;
}

.task-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    cursor: move;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.task-card.ui-sortable-helper {
    transform: rotate(5deg);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.task-title {
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 14px;
    line-height: 1.4;
}

.task-description {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 8px;
    line-height: 1.4;
}

.task-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    color: #94a3b8;
}

.task-reference {
    font-family: monospace;
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
}

.task-due-date {
    display: flex;
    align-items: center;
    gap: 4px;
}

.task-due-date.overdue {
    color: #ef4444;
}

.task-due-date.due-soon {
    color: #f59e0b;
}

.task-labels {
    display: flex;
    gap: 4px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.task-label {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 12px;
    color: white;
    font-weight: 500;
}

.task-assignee {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #6366f1;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 600;
}

.add-task-btn {
    margin: 16px;
    border: 2px dashed #cbd5e1;
    background: transparent;
    color: #64748b;
    border-radius: 8px;
    padding: 12px;
    transition: all 0.2s ease;
}

.add-task-btn:hover {
    border-color: #6366f1;
    color: #6366f1;
    background: rgba(99, 102, 241, 0.05);
}

.column-color-indicator {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    margin-right: 8px;
}

.empty-column {
    text-align: center;
    color: #94a3b8;
    font-style: italic;
    padding: 40px 20px;
}

/* Sortable styles */
.ui-sortable-placeholder {
    background: #e2e8f0;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    height: 80px;
    margin-bottom: 12px;
}

.ui-sortable-helper {
    z-index: 1000;
}

/* Responsive */
@media (max-width: 768px) {
    .kanban-columns-container {
        padding: 10px;
        gap: 10px;
    }
    
    .kanban-column {
        min-width: 280px;
        width: 280px;
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentBoard = null;
let currentBoardId = {{ $id }};

$(document).ready(function() {
    loadBoard();
    
    // Form submissions
    $('#add-column-form').on('submit', function(e) {
        e.preventDefault();
        addColumn();
    });
    
    $('#add-task-form').on('submit', function(e) {
        e.preventDefault();
        addTask();
    });
    
    $('#edit-task-form').on('submit', function(e) {
        e.preventDefault();
        updateTask();
    });
});

function loadBoard() {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
        window.location.href = '/login';
        return;
    }
    
    $.ajax({
        url: `/api/boards/${currentBoardId}`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                currentBoard = response.data;
                renderBoard(response.data);
            } else {
                showAlert('Erro ao carregar quadro.', 'danger');
            }
        },
        error: function(xhr) {
            if (xhr.status === 401) {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            } else if (xhr.status === 403) {
                showAlert('Você não tem permissão para acessar este quadro.', 'danger');
                setTimeout(() => window.location.href = '/dashboard', 2000);
            } else {
                handleApiError(xhr);
            }
        },
        complete: function() {
            $('#board-loading').hide();
        }
    });
}

function renderBoard(board) {
    // Update header
    $('#board-title').text(board.name);
    $('#board-description').text(board.description || 'Sem descrição');
    
    // Render columns
    const $container = $('#columns-container');
    $container.empty();
    
    if (board.columns && board.columns.length > 0) {
        board.columns.forEach(column => {
            renderColumn(column);
        });
    }
    
    // Make columns sortable
    makeColumnsSortable();
}

function renderColumn(column) {
    const colorIndicator = column.color ? 
        `<div class="column-color-indicator" style="background-color: ${column.color};"></div>` : '';
    
    const columnHtml = `
        <div class="kanban-column" data-column-id="${column.id}">
            <div class="column-header">
                <div class="column-title">
                    <div class="d-flex align-items-center">
                        ${colorIndicator}
                        <span>${escapeHtml(column.name)}</span>
                        <span class="badge bg-secondary ms-2">${column.tasks ? column.tasks.length : 0}</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                            <span class="material-icons">more_vert</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="editColumn(${column.id})">
                                <span class="material-icons me-2">edit</span>Editar
                            </a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteColumn(${column.id})">
                                <span class="material-icons me-2">delete</span>Excluir
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="column-tasks" data-column-id="${column.id}">
                ${renderTasks(column.tasks || [])}
            </div>
            <button class="add-task-btn" onclick="showAddTask(${column.id})">
                <span class="material-icons me-1">add</span>
                Adicionar task
            </button>
        </div>
    `;
    
    $('#columns-container').append(columnHtml);
}

function renderTasks(tasks) {
    if (!tasks || tasks.length === 0) {
        return '<div class="empty-column">Nenhuma task ainda</div>';
    }
    
    return tasks.map(task => renderTaskCard(task)).join('');
}

function renderTaskCard(task) {
    const dueDate = task.due_date ? new Date(task.due_date) : null;
    const now = new Date();
    let dueDateClass = '';
    let dueDateText = '';
    
    if (dueDate) {
        const timeDiff = dueDate.getTime() - now.getTime();
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        if (daysDiff < 0) {
            dueDateClass = 'overdue';
            dueDateText = 'Atrasada';
        } else if (daysDiff <= 2) {
            dueDateClass = 'due-soon';
            dueDateText = daysDiff === 0 ? 'Hoje' : `${daysDiff} dia${daysDiff > 1 ? 's' : ''}`;
        } else {
            dueDateText = dueDate.toLocaleDateString('pt-BR');
        }
    }
    
    const labels = task.labels ? task.labels.map(label => 
        `<span class="task-label" style="background-color: ${label.color || '#6366f1'}">${escapeHtml(label.name)}</span>`
    ).join('') : '';
    
    const assignee = task.assigned_user ? 
        `<div class="task-assignee" title="${escapeHtml(task.assigned_user.name)}">
            ${task.assigned_user.name.charAt(0).toUpperCase()}
        </div>` : '';
    
    return `
        <div class="task-card" data-task-id="${task.id}" onclick="showEditTask(${task.id})">
            ${labels ? `<div class="task-labels">${labels}</div>` : ''}
            <div class="task-title">${escapeHtml(task.title)}</div>
            ${task.description ? `<div class="task-description">${escapeHtml(task.description)}</div>` : ''}
            <div class="task-meta">
                <span class="task-reference">${escapeHtml(task.reference || '')}</span>
                <div class="d-flex align-items-center gap-2">
                    ${dueDate ? `<div class="task-due-date ${dueDateClass}">
                        <span class="material-icons" style="font-size: 14px;">schedule</span>
                        ${dueDateText}
                    </div>` : ''}
                    ${assignee}
                </div>
            </div>
        </div>
    `;
}

function makeColumnsSortable() {
    $('.column-tasks').sortable({
        connectWith: '.column-tasks',
        placeholder: 'ui-sortable-placeholder',
        helper: 'clone',
        tolerance: 'pointer',
        start: function(event, ui) {
            ui.helper.addClass('ui-sortable-helper');
        },
        update: function(event, ui) {
            const taskId = ui.item.data('task-id');
            const newColumnId = ui.item.closest('.column-tasks').data('column-id');
            const newOrder = ui.item.index() + 1;
            
            moveTask(taskId, newColumnId, newOrder);
        }
    });
}

function moveTask(taskId, columnId, order) {
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/tasks/${taskId}`,
        method: 'PATCH',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        data: JSON.stringify({
            column_id: columnId,
            order: order
        }),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                // Update task counters
                updateColumnCounters();
            }
        },
        error: function(xhr) {
            handleApiError(xhr);
            // Reload board on error to revert changes
            loadBoard();
        }
    });
}

function updateColumnCounters() {
    $('.kanban-column').each(function() {
        const $column = $(this);
        const taskCount = $column.find('.task-card').length;
        $column.find('.badge').text(taskCount);
    });
}

function showAddColumn() {
    $('#addColumnModal').modal('show');
}

function addColumn() {
    const $form = $('#add-column-form');
    const $submitBtn = $form.find('button[type="submit"]');
    const token = localStorage.getItem('auth_token');
    
    const formData = {
        board_id: currentBoardId,
        name: $('#column-name').val(),
        color: $('#column-color').val()
    };
    
    setLoading($submitBtn, true);
    
    $.ajax({
        url: '/api/columns',
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        data: JSON.stringify(formData),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                $('#addColumnModal').modal('hide');
                $form[0].reset();
                showAlert('Coluna adicionada com sucesso!', 'success');
                loadBoard(); // Reload to show new column
            }
        },
        error: function(xhr) {
            handleApiError(xhr);
        },
        complete: function() {
            setLoading($submitBtn, false);
        }
    });
}

function showAddTask(columnId) {
    $('#task-column-id').val(columnId);
    $('#addTaskModal').modal('show');
}

function addTask() {
    const $form = $('#add-task-form');
    const $submitBtn = $form.find('button[type="submit"]');
    const token = localStorage.getItem('auth_token');
    
    const formData = {
        column_id: parseInt($('#task-column-id').val()),
        title: $('#task-title').val(),
        description: $('#task-description').val(),
        due_date: $('#task-due-date').val() || null,
        assigned_user_id: $('#task-assigned-user').val() || null
    };
    
    setLoading($submitBtn, true);
    
    $.ajax({
        url: '/api/tasks',
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        data: JSON.stringify(formData),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                $('#addTaskModal').modal('hide');
                $form[0].reset();
                showAlert('Task criada com sucesso!', 'success');
                loadBoard(); // Reload to show new task
            }
        },
        error: function(xhr) {
            handleApiError(xhr);
        },
        complete: function() {
            setLoading($submitBtn, false);
        }
    });
}

function showEditTask(taskId) {
    const task = findTaskById(taskId);
    if (!task) return;
    
    $('#edit-task-id').val(task.id);
    $('#edit-task-title').val(task.title);
    $('#edit-task-description').val(task.description || '');
    $('#edit-task-due-date').val(task.due_date || '');
    $('#edit-task-assigned-user').val(task.assigned_user_id || '');
    
    $('#editTaskModal').modal('show');
}

function updateTask() {
    const $form = $('#edit-task-form');
    const $submitBtn = $form.find('button[type="submit"]');
    const token = localStorage.getItem('auth_token');
    const taskId = $('#edit-task-id').val();
    
    const formData = {
        title: $('#edit-task-title').val(),
        description: $('#edit-task-description').val(),
        due_date: $('#edit-task-due-date').val() || null,
        assigned_user_id: $('#edit-task-assigned-user').val() || null
    };
    
    setLoading($submitBtn, true);
    
    $.ajax({
        url: `/api/tasks/${taskId}`,
        method: 'PATCH',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        data: JSON.stringify(formData),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                $('#editTaskModal').modal('hide');
                showAlert('Task atualizada com sucesso!', 'success');
                loadBoard(); // Reload to show changes
            }
        },
        error: function(xhr) {
            handleApiError(xhr);
        },
        complete: function() {
            setLoading($submitBtn, false);
        }
    });
}

function deleteTask() {
    const taskId = $('#edit-task-id').val();
    
    if (confirm('Tem certeza que deseja excluir esta task?')) {
        const token = localStorage.getItem('auth_token');
        
        $.ajax({
            url: `/api/tasks/${taskId}`,
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    $('#editTaskModal').modal('hide');
                    showAlert('Task excluída com sucesso!', 'success');
                    loadBoard(); // Reload to remove task
                }
            },
            error: function(xhr) {
                handleApiError(xhr);
            }
        });
    }
}

function editColumn(columnId) {
    showAlert('Funcionalidade de edição de coluna em desenvolvimento.', 'info');
}

function deleteColumn(columnId) {
    if (confirm('Tem certeza que deseja excluir esta coluna? Todas as tasks serão movidas para a primeira coluna.')) {
        const token = localStorage.getItem('auth_token');
        
        $.ajax({
            url: `/api/columns/${columnId}`,
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Coluna excluída com sucesso!', 'success');
                    loadBoard(); // Reload board
                }
            },
            error: function(xhr) {
                handleApiError(xhr);
            }
        });
    }
}

function showBoardSettings() {
    showAlert('Configurações do quadro em desenvolvimento.', 'info');
}

function findTaskById(taskId) {
    if (!currentBoard || !currentBoard.columns) return null;
    
    for (let column of currentBoard.columns) {
        if (column.tasks) {
            const task = column.tasks.find(t => t.id == taskId);
            if (task) return task;
        }
    }
    return null;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
