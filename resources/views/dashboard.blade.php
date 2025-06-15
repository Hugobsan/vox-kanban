@extends('layouts.app')

@section('title', 'Dashboard - Vox Kanban')

@section('main-class', 'container-fluid p-0')

@section('content')
<div class="row g-0">
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2">
        <div class="sidebar p-3">
            <div class="d-flex align-items-center mb-4">
                <span class="material-icons me-2">dashboard</span>
                <h5 class="mb-0">Dashboard</h5>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="#" onclick="showBoards()">
                    <span class="material-icons me-2">view_kanban</span>
                    Meus Quadros
                </a>
                <a class="nav-link" href="#" onclick="showSharedBoards()">
                    <span class="material-icons me-2">group</span>
                    Quadros Compartilhados
                </a>
                <a class="nav-link" href="#" onclick="showArchived()">
                    <span class="material-icons me-2">archive</span>
                    Arquivados
                </a>
                <hr class="my-3 opacity-25">
                <a class="nav-link" href="#" onclick="showCreateBoard()">
                    <span class="material-icons me-2">add</span>
                    Novo Quadro
                </a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="col-md-9 col-lg-10">
        <div class="p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Meus Quadros Kanban</h2>
                    <p class="text-muted mb-0">Organize e gerencie suas tarefas de forma eficiente</p>
                </div>
                <button class="btn btn-primary" onclick="showCreateBoard()">
                    <span class="material-icons me-2">add</span>
                    Novo Quadro
                </button>
            </div>
            
            <!-- Alerts -->
            <div id="alerts-container"></div>
            
            <!-- Boards Grid -->
            <div id="boards-container">
                <div class="row" id="boards-grid">
                    <!-- Boards will be loaded here -->
                </div>
                
                <!-- Empty State -->
                <div id="empty-state" class="text-center py-5" style="display: none;">
                    <span class="material-icons mb-3" style="font-size: 4rem; color: #cbd5e1;">view_kanban</span>
                    <h4 class="text-muted mb-3">Nenhum quadro encontrado</h4>
                    <p class="text-muted mb-4">Crie seu primeiro quadro Kanban para começar a organizar suas tarefas.</p>
                    <button class="btn btn-primary" onclick="showCreateBoard()">
                        <span class="material-icons me-2">add</span>
                        Criar Primeiro Quadro
                    </button>
                </div>
                
                <!-- Loading State -->
                <div id="loading-state" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="text-muted">Carregando quadros...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Board Modal -->
<div class="modal fade" id="createBoardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="material-icons me-2">add</span>
                    Criar Novo Quadro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>                <form id="create-board-form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="board-name" class="form-label">Nome do Quadro</label>
                            <input type="text" class="form-control" id="board-name" name="name" placeholder="Ex: Projeto Website" required>
                        </div>
                        <div class="mb-3">
                            <label for="board-key" class="form-label">Chave do Quadro</label>
                            <input type="text" class="form-control" id="board-key" name="key" placeholder="Ex: PROJ" maxlength="10" pattern="[A-Z]+" required>
                            <small class="text-muted">Apenas letras maiúsculas, máximo 10 caracteres. Será usado como prefixo das tarefas.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Colunas Iniciais</label>
                            <div id="initial-columns">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" value="A Fazer" readonly>
                                    <span class="input-group-text">
                                        <span class="material-icons">lock</span>
                                    </span>
                                </div>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" value="Em Progresso" readonly>
                                    <span class="input-group-text">
                                        <span class="material-icons">lock</span>
                                    </span>
                                </div>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" value="Concluído" readonly>
                                    <span class="input-group-text">
                                        <span class="material-icons">lock</span>
                                    </span>
                                </div>
                            </div>
                            <small class="text-muted">Você poderá adicionar mais colunas após criar o quadro.</small>
                        </div>
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons me-2">add</span>
                        Criar Quadro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadBoards();
    
    // Create board form submission
    $('#create-board-form').on('submit', function(e) {
        e.preventDefault();
        createBoard();
    });
});

function loadBoards() {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
        window.location.href = '/login';
        return;
    }
    
    $.ajax({
        url: '/api/boards',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                displayBoards(response.data);
            } else {
                showEmptyState();
            }
        },
        error: function(xhr) {
            if (xhr.status === 401) {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            } else {
                showAlert('Erro ao carregar quadros.', 'danger');
                showEmptyState();
            }
        },
        complete: function() {
            $('#loading-state').hide();
        }
    });
}

function displayBoards(boards) {
    const $grid = $('#boards-grid');
    $grid.empty();
    
    if (boards.length === 0) {
        showEmptyState();
        return;
    }
    
    boards.forEach(board => {
        const boardCard = `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 board-card" style="cursor: pointer;" onclick="openBoard(${board.id})">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">${escapeHtml(board.name)}</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation()">
                                    <span class="material-icons">more_vert</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="editBoard(${board.id}); event.stopPropagation();">
                                        <span class="material-icons me-2">edit</span>Editar
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="shareBoard(${board.id}); event.stopPropagation();">
                                        <span class="material-icons me-2">share</span>Compartilhar
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteBoard(${board.id}); event.stopPropagation();">
                                        <span class="material-icons me-2">delete</span>Excluir
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        <p class="card-text text-muted">${escapeHtml(board.description || 'Sem descrição')}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <span class="material-icons me-1" style="font-size: 16px;">schedule</span>
                                ${formatDate(board.created_at)}
                            </small>
                            <span class="badge bg-primary">${board.columns_count || 0} colunas</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $grid.append(boardCard);
    });
}

function showEmptyState() {
    $('#empty-state').show();
}

function showCreateBoard() {
    $('#createBoardModal').modal('show');
}

function createBoard() {
    const $form = $('#create-board-form');
    const $submitBtn = $form.find('button[type="submit"]');
    const token = localStorage.getItem('auth_token');
    
    const formData = {
        name: $('#board-name').val(),
        description: $('#board-description').val()
    };
    
    setLoading($submitBtn, true);
    
    $.ajax({
        url: '/api/boards',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                $('#createBoardModal').modal('hide');
                $form[0].reset();
                showAlert(response.message || 'Quadro criado com sucesso!', 'success');
                loadBoards(); // Reload boards
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

function openBoard(boardId) {
    window.location.href = `/boards/${boardId}`;
}

function editBoard(boardId) {
    showAlert('Funcionalidade de edição em desenvolvimento.', 'info');
}

function shareBoard(boardId) {
    showAlert('Funcionalidade de compartilhamento em desenvolvimento.', 'info');
}

function deleteBoard(boardId) {
    if (confirm('Tem certeza que deseja excluir este quadro? Esta ação não pode ser desfeita.')) {
        const token = localStorage.getItem('auth_token');
        
        $.ajax({
            url: `/api/boards/${boardId}`,
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message || 'Quadro excluído com sucesso!', 'success');
                    loadBoards();
                }
            },
            error: function(xhr) {
                handleApiError(xhr);
            }
        });
    }
}

function showBoards() {
    $('.sidebar .nav-link').removeClass('active');
    $(event.target).addClass('active');
    loadBoards();
}

function showSharedBoards() {
    $('.sidebar .nav-link').removeClass('active');
    $(event.target).addClass('active');
    showAlert('Funcionalidade de quadros compartilhados em desenvolvimento.', 'info');
}

function showArchived() {
    $('.sidebar .nav-link').removeClass('active');
    $(event.target).addClass('active');
    showAlert('Funcionalidade de quadros arquivados em desenvolvimento.', 'info');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}
</script>

<style>
.board-card {
    transition: all 0.3s ease;
}

.board-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.3);
}
</style>
@endpush
