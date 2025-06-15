<!-- Create Column Modal -->
<div class="modal fade" id="createColumnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="material-icons me-2">view_week</span>
                    Criar Nova Coluna
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="create-column-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="column-name" class="form-label">Nome da Coluna *</label>
                        <input type="text" class="form-control" id="column-name" name="name" 
                               placeholder="Ex: Em Revisão" required maxlength="255">
                    </div>
                    
                    <div class="mb-3">
                        <label for="column-color" class="form-label">Cor da Coluna (opcional)</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="color" class="form-control form-control-color" 
                                       id="column-color" name="color" value="#6366f1">
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetColumnColor()">
                                    <span class="material-icons">refresh</span>
                                </button>
                            </div>
                        </div>
                        <div class="form-text">Cor para destacar a coluna no quadro.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="column-limit" class="form-label">Limite de Tarefas (opcional)</label>
                        <input type="number" class="form-control" id="column-limit" name="task_limit" 
                               placeholder="Ex: 5" min="1" max="100">
                        <div class="form-text">
                            Limite máximo de tarefas que podem estar nesta coluna ao mesmo tempo.
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="column-auto-complete" name="auto_complete">
                        <label class="form-check-label" for="column-auto-complete">
                            Marcar tarefas como concluídas automaticamente
                        </label>
                        <div class="form-text">
                            Tarefas movidas para esta coluna serão automaticamente marcadas como concluídas.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons me-2">add</span>
                        Criar Coluna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Create column form submission
    $('#create-column-form').on('submit', function(e) {
        e.preventDefault();
        createColumn();
    });
    
    // Reset form when modal is hidden
    $('#createColumnModal').on('hidden.bs.modal', function() {
        $('#create-column-form')[0].reset();
        $('#column-color').val('#6366f1');
    });
});

function createColumn() {
    const $form = $('#create-column-form');
    const $submitBtn = $form.find('button[type="submit"]');
    const token = localStorage.getItem('auth_token');
    
    if (!currentBoardId) {
        showAlert('Selecione um quadro primeiro.', 'warning');
        return;
    }
    
    const formData = {
        name: $('#column-name').val().trim(),
        color: $('#column-color').val(),
        task_limit: $('#column-limit').val() || null,
        auto_complete: $('#column-auto-complete').is(':checked'),
        board_id: currentBoardId
    };
    
    // Validation
    if (!formData.name) {
        showAlert('Nome da coluna é obrigatório.', 'danger');
        return;
    }
    
    setLoading($submitBtn, true);
    
    // Optimistic update - add column to UI immediately
    const tempColumn = {
        id: 'temp-' + Date.now(),
        name: formData.name,
        color: formData.color,
        tasks: [],
        order: $('.kanban-column').length
    };
    
    const $newColumn = $(createColumnElement(tempColumn));
    $('.ghost-column').before($newColumn);
    initializeSortable();
    
    $.ajax({
        url: '/api/columns',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                $('#createColumnModal').modal('hide');
                $form[0].reset();
                showAlert(response.message || 'Coluna criada com sucesso!', 'success');
                
                // Replace temp column with real data
                $newColumn.attr('data-column-id', response.data.id);
                $newColumn.find('.kanban-column-body').attr('data-column-id', response.data.id);
                
                // Update ghost task onclick
                $newColumn.find('.ghost-card').attr('onclick', `showCreateTaskModal(${response.data.id})`);
            }
        },
        error: function(xhr) {
            // Remove optimistic column on error
            $newColumn.remove();
            
            let message = 'Erro ao criar coluna.';
            
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

function resetColumnColor() {
    $('#column-color').val('#6366f1');
}
</script>
