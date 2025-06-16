@extends('layouts.app')

@section('title', 'Dashboard - Vox Kanban')

@section('main-class', 'container-fluid p-0')

@section('content')
<div class="row g-0 vh-100">
    <div class="col-md-3 col-lg-2">
        <div class="sidebar p-3">
            <div class="d-flex align-items-center mb-4">
                <span class="material-icons me-2">dashboard</span>
                <h5 class="mb-0">Dashboard</h5>
            </div>
            
            <!-- Board Selector -->
            <div class="mb-4">
                <label class="form-label text-white-50 small">QUADRO ATUAL</label>
                <select class="form-select" id="board-selector" onchange="selectBoard()">
                    <option value="">Selecione um quadro...</option>
                </select>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link" href="#" onclick="showCreateBoardModal()">
                    <span class="material-icons me-2">add</span>
                    Novo Quadro
                </a>
                <a class="nav-link" href="#" onclick="refreshCurrentBoard()">
                    <span class="material-icons me-2">refresh</span>
                    Atualizar
                </a>
                <hr class="my-3 opacity-25">
                <a class="nav-link" href="#" onclick="showArchivedBoards()">
                    <span class="material-icons me-2">archive</span>
                    Arquivados
                </a>
                <a class="nav-link" href="#" onclick="showBoardSettings()" id="board-settings-link" style="display: none;">
                    <span class="material-icons me-2">settings</span>
                    Configurações
                </a>
            </nav>
        </div>
    </div>
    
    <div class="col-md-9 col-lg-10">
        <div class="h-100 d-flex flex-column">
            <div class="p-4 bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1" id="board-title">Vox Kanban</h2>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary" onclick="toggleBoardView()" id="view-toggle" style="display: none;">
                            <span class="material-icons me-2">view_list</span>
                            Lista
                        </button>
                        <button class="btn btn-light" onclick="showBoardSettings()" id="board-settings-btn" style="display: none;">
                            <span class="material-icons">settings</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="alerts-container" class="px-4 pt-3"></div>
            
            <div class="flex-grow-1 overflow-hidden">
                <div id="empty-state" class="h-100 d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <span class="material-icons mb-3" style="font-size: 4rem; color: #cbd5e1;">view_kanban</span>
                        <h4 class="text-muted mb-3">Nenhum quadro selecionado</h4>
                        <p class="text-muted mb-4">Selecione um quadro existente ou crie um novo para começar.</p>
                        <button class="btn btn-primary" onclick="showCreateBoardModal()">
                            <span class="material-icons me-2">add</span>
                            Criar Primeiro Quadro
                        </button>
                    </div>
                </div>
                
                <div id="kanban-board" class="h-100 p-4" style="display: none;">
                    <div class="kanban-container h-100">
                        <div class="kanban-columns d-flex gap-3 h-100 overflow-x-auto pb-3" id="columns-container">
                        </div>
                    </div>
                </div>
                
                <div id="loading-state" class="h-100 d-flex align-items-center justify-content-center" style="display: none;">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="text-muted">Carregando quadro...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('components.modals.create-board')
@include('components.modals.create-column')
@include('components.modals.create-task')
@include('components.modals.task-details')
@include('components.modals.board-settings')

@endsection

@push('styles')
<style>
.kanban-container {
    overflow-x: auto;
    overflow-y: hidden;
}

.kanban-columns {
    min-width: max-content;
    height: 100%;
}

.kanban-column {
    width: 280px;
    min-width: 280px;
    background-color: #f1f5f9;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    max-height: 100%;
}

.kanban-column-header {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    background: white;
    border-radius: 12px 12px 0 0;
}

.kanban-column-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    min-height: 200px;
}

.kanban-card {
    background: white;
    border-radius: 8px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    cursor: move;
    border: 2px solid transparent;
}

.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.3);
}

.kanban-card.ui-sortable-helper {
    transform: rotate(5deg);
    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
}

.kanban-card.ui-sortable-placeholder {
    background: #e2e8f0;
    border: 2px dashed #cbd5e1;
    height: 80px;
}

.ghost-card {
    border: 2px dashed #cbd5e1;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s ease;
}

.ghost-card:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: rgba(99, 102, 241, 0.05);
    transform: none;
    box-shadow: none;
}

.ghost-column {
    width: 280px;
    min-width: 280px;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #64748b;
    background: transparent;
}

.ghost-column:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: rgba(99, 102, 241, 0.05);
}

.task-labels {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 8px;
}

.task-label {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 12px;
    color: white;
    font-weight: 500;
}

.column-handle {
    cursor: move;
    color: #64748b;
}

.column-handle:hover {
    color: var(--primary-color);
}

.sortable-placeholder {
    border: 2px dashed var(--primary-color);
    background: rgba(99, 102, 241, 0.1);
    margin: 0 8px;
    border-radius: 8px;
}

.task-dropdown {
    position: absolute;
    top: 8px;
    right: 8px;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.kanban-card:hover .task-dropdown {
    opacity: 1;
}

.kanban-card.creating {
    opacity: 0.7;
    border: 2px dashed var(--primary-color);
    position: relative;
}

.kanban-card.creating::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid var(--primary-color);
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

@media (max-width: 768px) {
    .kanban-column {
        width: 250px;
        min-width: 250px;
    }
    
    .ghost-column {
        width: 250px;
        min-width: 250px;
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentBoardId = null;
let currentBoardData = null;
let isOwner = false;

$(document).ready(function() {
    // Check for board ID in URL first
    const urlParams = new URLSearchParams(window.location.search);
    const boardIdFromUrl = urlParams.get('board');
    
    loadUserBoards().then(() => {
        if (boardIdFromUrl) {
            selectBoardById(boardIdFromUrl);
        }
    });
    
    initializeSortable();
});

function loadUserBoards() {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
        window.location.href = '/login';
        return Promise.reject('No token');
    }
    
    return $.ajax({
        url: '/api/boards',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            // A resposta contém um objeto com a propriedade 'boards'
            if (response && response.boards) {
                const boards = response.boards;
                populateBoardSelector(boards);
                
                // Se houver apenas um board, selecione automaticamente
                if (boards.length === 1) {
                    selectBoardById(boards[0].id);
                }
            } else if (response.success && response.data) {
                // Fallback para caso a resposta venha em outro formato
                const boards = response.data || [];
                populateBoardSelector(boards);
                
                if (boards.length === 1) {
                    selectBoardById(boards[0].id);
                }
            } else {
                showAlert('Formato de resposta inesperado.', 'warning');
                showEmptyState();
            }
        },
        error: function(xhr) {
            if (xhr.status === 401) {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            } else {
                const errorMessage = xhr.responseJSON?.message || 'Erro ao carregar quadros.';
                showAlert(errorMessage, 'danger');
            }
        }
    });
}

function populateBoardSelector(boards) {
    const $selector = $('#board-selector');
    
    if (!$selector.length) {
        return;
    }
    
    $selector.empty().append('<option value="">Selecione um quadro...</option>');
    
    if (!boards || !Array.isArray(boards)) {
        return;
    }
    
    if (boards.length === 0) {
        $selector.append('<option value="" disabled>Nenhum quadro encontrado</option>');
        return;
    }
    
    boards.forEach((board) => {
        if (board.id && board.name) {
            const option = `<option value="${board.id}">${escapeHtml(board.name)}</option>`;
            $selector.append(option);
        }
    });
}

function selectBoard() {
    const boardId = $('#board-selector').val();
    if (boardId) {
        selectBoardById(boardId);
    } else {
        showEmptyState();
    }
}

function selectBoardById(boardId) {
    if (currentBoardId === boardId) {
        return;
    }
    
    currentBoardId = boardId;
    $('#board-selector').val(boardId);
    
    showLoadingState();
    loadBoardData(boardId);
    
    // Conectar ao canal real-time do board
    if (typeof window.connectToBoard === 'function') {
        window.connectToBoard(boardId);
    }
}

function loadBoardData(boardId) {
    const token = localStorage.getItem('auth_token');
    
    $.ajax({
        url: `/api/boards/${boardId}`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            // A resposta contém um objeto com a propriedade 'board'
            if (response && response.board) {
                const boardData = response.board;
                currentBoardData = boardData;
                isOwner = checkIfOwner(boardData);
                displayBoard(boardData);
                updateUrlWithBoard(boardId);
            } else if (response.success && response.data) {
                // Fallback para caso a resposta venha em outro formato
                const boardData = response.data;
                currentBoardData = boardData;
                isOwner = checkIfOwner(boardData);
                displayBoard(boardData);
                updateUrlWithBoard(boardId);
            } else {
                showAlert('Formato de resposta inesperado.', 'warning');
                showEmptyState();
            }
        },
        error: function(xhr) {
            let message = 'Erro ao carregar dados do quadro.';
            
            if (xhr.status === 401) {
                message = 'Sessão expirada. Faça login novamente.';
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
                return;
            }
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            
            showAlert(message, 'danger');
            showEmptyState();
        },
        complete: function() {
            hideLoadingState();
        }
    });
}

function checkIfOwner(boardData) {
    const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
    const userId = userData.id;
    
    return boardData.board_users?.some(bu => 
        bu.user_id === userId && bu.role_in_board === 'owner'
    ) || false;
}

function displayBoard(boardData) {
    // Update header
    $('#board-title').text(boardData.name);
    
    // Show board elements
    $('#board-settings-btn, #view-toggle, #board-settings-link').toggle(isOwner);
    
    // Display columns
    displayColumns(boardData.columns || []);
}

function displayColumns(columns) {
    const $container = $('#columns-container');
    $container.empty();
    
    if (!columns || !Array.isArray(columns)) {
        return;
    }
    
    // Sort columns by order
    columns.sort((a, b) => (a.order || 0) - (b.order || 0));
    
    // Add existing columns
    columns.forEach(column => {
        const columnElement = createColumnElement(column);
        $container.append(columnElement);
    });
    
    // Add ghost column for creating new columns (apenas se for owner)
    if (isOwner) {
        $container.append(createGhostColumn());
    }
    
    // Force show kanban board
    $('#empty-state').remove(); // Remove completamente do DOM temporariamente
    $('#kanban-board').show().css({
        'display': 'block !important',
        'visibility': 'visible',
        'opacity': '1'
    });
    
    // Show kanban board
    showBoardState();
    
    // Reinitialize sortable
    initializeSortable();
}

function createColumnElement(column) {
    const tasks = column.tasks || [];
    const tasksHtml = tasks.map(task => createTaskElement(task)).join('');
    
    const columnHtml = `
        <div class="kanban-column" data-column-id="${column.id}">
            <div class="kanban-column-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="column-handle material-icons me-2" style="cursor: move;">drag_indicator</span>
                    <h6 class="mb-0 fw-bold">${escapeHtml(column.name)}</h6>
                    <span class="badge bg-secondary ms-2">${tasks.length}</span>
                </div>
                ${isOwner ? `
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                        <span class="material-icons">more_vert</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="editColumn(${column.id})">Editar</a></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteColumn(${column.id})">Excluir</a></li>
                    </ul>
                </div>
                ` : ''}
            </div>
            <div class="kanban-column-body" data-column-id="${column.id}">
                ${tasksHtml}
                ${createGhostTask(column.id)}
            </div>
        </div>
    `;
    
    return columnHtml;
}

function createTaskElement(task) {
    if (!task) return '';
    
    const labels = task.labels || [];
    const labelsHtml = labels.map(label => 
        `<span class="task-label" style="background-color: ${label.color}">${escapeHtml(label.name)}</span>`
    ).join('');
    
    return `
        <div class="kanban-card" data-task-id="${task.id}" onclick="showTaskDetails(${task.id})">
            <div class="card-body p-3">
                ${labelsHtml ? `<div class="task-labels mb-2">${labelsHtml}</div>` : ''}
                <h6 class="card-title mb-2">${escapeHtml(task.title)}</h6>
                ${task.description ? `<p class="card-text small text-muted mb-2">${escapeHtml(task.description.substring(0, 100))}${task.description.length > 100 ? '...' : ''}</p>` : ''}
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <small class="text-muted">#${task.reference}</small>
                    </div>
                    ${task.due_date ? `<small class="text-muted">${formatDate(task.due_date)}</small>` : ''}
                </div>
            </div>
        </div>
    `;
}

function createGhostTask(columnId) {
    return `
        <div class="kanban-card ghost-card d-flex align-items-center justify-content-center" 
             style="min-height: 60px;" onclick="showCreateTaskModal(${columnId})">
            <div class="text-center">
                <span class="material-icons mb-1">add</span>
                <div class="small">Adicionar tarefa</div>
            </div>
        </div>
    `;
}

function createGhostColumn() {
    return `
        <div class="ghost-column" onclick="showCreateColumnModal()">
            <div class="text-center">
                <span class="material-icons mb-2" style="font-size: 2rem;">add</span>
                <div>Adicionar coluna</div>
            </div>
        </div>
    `;
}

function showEmptyState() {
    $('#empty-state').css('display', 'flex');
    $('#kanban-board, #loading-state').css('display', 'none');
    $('#board-title').text('Vox Kanban');
    $('#board-settings-btn, #view-toggle, #board-settings-link').hide();
    currentBoardId = null;
    currentBoardData = null;
}

function showBoardState() {
    $('#empty-state').css('display', 'none');
    $('#loading-state').css('display', 'none');
    $('#kanban-board').css('display', 'block');
}

function showLoadingState() {
    $('#loading-state').css('display', 'flex');
    $('#empty-state, #kanban-board').css('display', 'none');
}

function hideLoadingState() {
    $('#loading-state').hide();
}

function updateUrlWithBoard(boardId) {
    const url = new URL(window.location);
    url.searchParams.set('board', boardId);
    history.replaceState(null, '', url);
}

function refreshCurrentBoard() {
    if (currentBoardId) {
        loadBoardData(currentBoardId);
    } else {
        loadUserBoards();
    }
}

// Modal functions
function showCreateBoardModal() {
    $('#createBoardModal').modal('show');
}

function showCreateColumnModal() {
    if (!currentBoardId) {
        showAlert('Selecione um quadro primeiro.', 'warning');
        return;
    }
    $('#createColumnModal').modal('show');
}

function showCreateTaskModal(columnId) {
    $('#createTaskModal').modal('show');
    $('#task-column-id').val(columnId);
}

function showTaskDetails(taskId) {
    // Implementation will be in task-details modal
    $('#taskDetailsModal').modal('show');
    loadTaskDetails(taskId);
}

function showBoardSettings() {
    if (!isOwner) {
        showAlert('Apenas o proprietário pode acessar as configurações.', 'warning');
        return;
    }
    $('#boardSettingsModal').modal('show');
}

// Utility functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function showAlert(message, type = 'info') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#alerts-container').html(alertHtml);
    
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
}

// Initialize sortable functionality
function initializeSortable() {
    // Column sorting
    $('#columns-container').sortable({
        items: '.kanban-column',
        handle: '.column-handle',
        placeholder: 'sortable-placeholder',
        tolerance: 'pointer',
        update: function(event, ui) {
            updateColumnOrder();
        }
    });
    
    // Task sorting within columns
    $('.kanban-column-body').sortable({
        connectWith: '.kanban-column-body',
        items: '.kanban-card:not(.ghost-card)',
        placeholder: 'kanban-card ui-sortable-placeholder',
        tolerance: 'pointer',
        update: function(event, ui) {
            // Get current column ID and position
            const columnId = ui.item.closest('.kanban-column-body').data('column-id');
            const newPosition = ui.item.index() + 1;
            updateTaskPositionAndColumn(ui.item, columnId, newPosition);
        },
        receive: function(event, ui) {
            // When task is moved to another column, update is also called
            // so we don't need to duplicate the logic here
        }
    });
}

function updateColumnOrder() {
    const columnIds = [];
    $('.kanban-column').each(function(index) {
        const columnId = $(this).data('column-id');
        if (columnId) {
            columnIds.push({ id: columnId, order: index + 1 });
        }
    });
    
    // Send individual update requests for each column
    const token = localStorage.getItem('auth_token');
    const updatePromises = columnIds.map(column => {
        return $.ajax({
            url: `/api/boards/${currentBoardId}/columns/${column.id}`,
            method: 'PATCH',
            data: JSON.stringify({ order: column.order }),
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        });
    });
    
    // Handle all requests
    $.when(...updatePromises).fail(function() {
        NotificationService.error('Erro ao reordenar colunas.');
        refreshCurrentBoard();
    });
}

function updateTaskPositionAndColumn(taskItem, columnId, position) {
    const taskId = taskItem.data('task-id');
    
    console.log('Updating task:', { taskId, columnId, position });
    
    const token = localStorage.getItem('auth_token');
    $.ajax({
        url: `/api/tasks/${taskId}`,
        method: 'PATCH',
        data: JSON.stringify({ 
            column_id: columnId,
            order: position 
        }),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            console.log('Task updated successfully:', response);
        },
        error: function(xhr) {
            console.error('Error updating task:', xhr.responseJSON);
            NotificationService.error('Erro ao mover tarefa.');
            refreshCurrentBoard();
        }
    });
}

// Legacy functions - keeping for backward compatibility if needed elsewhere
function updateTaskPosition(taskItem) {
    const taskId = taskItem.data('task-id');
    const newPosition = taskItem.index() + 1;
    const columnId = taskItem.closest('.kanban-column-body').data('column-id');
    
    updateTaskPositionAndColumn(taskItem, columnId, newPosition);
}

function updateTaskColumn(taskItem, newColumnId) {
    const newPosition = taskItem.index() + 1;
    updateTaskPositionAndColumn(taskItem, newColumnId, newPosition);
}
</script>
@endpush
