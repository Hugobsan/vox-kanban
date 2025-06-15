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
            </div>
            <form id="create-board-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="board-name" class="form-label">Nome do Quadro *</label>
                        <input type="text" class="form-control" id="board-name" name="name" 
                               placeholder="Ex: Projeto Website" required maxlength="255">
                    </div>
                    
                    <div class="mb-3">
                        <label for="board-key" class="form-label">Chave do Quadro *</label>
                        <input type="text" class="form-control" id="board-key" name="key" 
                               placeholder="Ex: WEB" required maxlength="10" style="text-transform: uppercase;">
                        <div class="form-text">
                            Chave única para identificar tarefas (ex: WEB-001). Apenas letras maiúsculas.
                        </div>
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
                    <button type="button" class="btn btn-primary" id="create-board-button">
                        <span class="material-icons me-2">add</span>
                        Criar Quadro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-generate key from name
    $('#board-name').on('input', function() {
        const name = $(this).val();
        const key = name
            .toUpperCase()
            .replace(/[^A-Z\s]/g, '')
            .split(' ')
            .map(word => word.substring(0, 3))
            .join('')
            .substring(0, 10);
        $('#board-key').val(key);
    });
    
    // Ensure key is uppercase
    $('#board-key').on('input', function() {
        $(this).val($(this).val().toUpperCase().replace(/[^A-Z]/g, ''));
    });
    
    // Create board form submission
    $('#create-board-button').on('click', function(e) {
        e.preventDefault();
        createBoard();
    });
});

function createBoard() {
    const $form = $('#create-board-form');
    const $submitBtn = $('#create-board-button');
    const token = localStorage.getItem('auth_token');
    
    const formData = {
        name: $('#board-name').val().trim(),
        key: $('#board-key').val().trim()
    };
    
    // Validation
    if (!formData.name) {
        showAlert('Nome do quadro é obrigatório.', 'danger');
        return;
    }
    
    if (!formData.key) {
        showAlert('Chave do quadro é obrigatória.', 'danger');
        return;
    }
    
    if (!/^[A-Z]+$/.test(formData.key)) {
        showAlert('Chave deve conter apenas letras maiúsculas.', 'danger');
        return;
    }
    
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
                
                // Reload boards and select the new one
                loadUserBoards();
                setTimeout(() => {
                    selectBoardById(response.data.id);
                }, 500);
            }
        },
        error: function(xhr) {
            let message = 'Erro ao criar quadro.';
            
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

function setLoading(button, loading = true) {
    const $btn = $(button);
    
    if (loading) {
        $btn.addClass('loading-btn').prop('disabled', true);
        const originalText = $btn.html();
        $btn.data('original-text', originalText);
        $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Carregando...');
    } else {
        $btn.removeClass('loading-btn').prop('disabled', false);
        $btn.html($btn.data('original-text'));
    }
}
</script>
