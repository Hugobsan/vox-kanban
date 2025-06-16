/**
 * Real-time board management using Laravel Echo
 */
class BoardRealTime {
    constructor() {
        this.currentChannel = null;
        this.currentBoardId = null;
        this.isConnected = false;
    }

    /**
     * Connect to a board channel
     */
    connectToBoard(boardId) {
        if (this.currentBoardId === boardId && this.isConnected) {
            console.log(`Already connected to board ${boardId}`);
            return;
        }

        // Disconnect from previous channel if exists
        this.disconnect();

        this.currentBoardId = boardId;
        console.log(`Attempting to connect to public channel: board.${boardId}`);
        
        // Use public channel para teste
        this.currentChannel = window.Echo.channel(`board.${boardId}`)
            .subscribed(() => {
                console.log(`✅ Successfully subscribed to public channel: board.${boardId}`);
            })
            .listen('TaskCreated', (e) => {
                console.log('TaskCreated event received:', e);
                this.handleTaskUpdate({ ...e, action: 'created' });
            })
            .listen('TaskUpdated', (e) => {
                console.log('TaskUpdated event received:', e);
                this.handleTaskUpdate({ ...e, action: 'updated' });
            })
            .listen('TaskDeleted', (e) => {
                console.log('TaskDeleted event received:', e);
                this.handleTaskUpdate({ ...e, action: 'deleted' });
            })
            .listen('ColumnCreated', (e) => {
                console.log('ColumnCreated event received:', e);
                this.handleColumnUpdate({ ...e, action: 'created' });
            })
            .listen('ColumnUpdated', (e) => {
                console.log('ColumnUpdated event received:', e);
                this.handleColumnUpdate({ ...e, action: 'updated' });
            })
            .listen('ColumnDeleted', (e) => {
                console.log('ColumnDeleted event received:', e);
                this.handleColumnUpdate({ ...e, action: 'deleted' });
            })
            .listen('BoardUpdated', (e) => {
                console.log('BoardUpdated event received:', e);
                this.handleBoardUpdate(e);
            })
            .listen('LabelCreated', (e) => {
                console.log('LabelCreated event received:', e);
                this.handleLabelUpdate({ ...e, action: 'created' });
            })
            .listen('LabelUpdated', (e) => {
                console.log('LabelUpdated event received:', e);
                this.handleLabelUpdate({ ...e, action: 'updated' });
            })
            .listen('LabelDeleted', (e) => {
                console.log('LabelDeleted event received:', e);
                this.handleLabelUpdate({ ...e, action: 'deleted' });
            })
            .error((error) => {
                console.error('Echo connection error:', error);
            });
        
        this.isConnected = true;
        
        console.log(`Connected to board channel: board.${boardId}`);
    }

    /**
     * Disconnect from current channel
     */
    disconnect() {
        if (this.currentChannel && this.currentBoardId) {
            console.log(`Disconnecting from board.${this.currentBoardId}`);
            window.Echo.leaveChannel(`board.${this.currentBoardId}`);
            this.currentChannel = null;
            this.isConnected = false;
            console.log(`Disconnected from board channel: board.${this.currentBoardId}`);
        }
        this.currentBoardId = null;
    }

    /**
     * Handle board updates
     */
    handleBoardUpdate(event) {
        console.log('Board update received:', event);
        
        switch (event.action) {
            case 'updated':
                this.updateBoardTitle(event.board.name);
                break;
            case 'deleted':
                this.handleBoardDeleted();
                break;
        }
    }

    /**
     * Handle column updates
     */
    handleColumnUpdate(event) {
        console.log('Column update received:', event);
        
        const action = event.action || 'updated';
        const column = event.column;
        
        switch (action) {
            case 'created':
                this.addColumnToBoard(column);
                break;
            case 'updated':
                this.updateColumn(column);
                break;
            case 'deleted':
                this.removeColumn(column.id);
                break;
        }
    }

    /**
     * Handle task updates
     */
    handleTaskUpdate(event) {
        console.log('Task update received:', event);
        
        // Determine action from event type or data
        const action = event.action || 'updated';
        const task = event.task;
        
        switch (action) {
            case 'created':
                this.addTaskToColumn(task);
                break;
            case 'updated':
                this.updateTask(task);
                break;
            case 'deleted':
                this.removeTask(task.id);
                break;
        }
    }

    /**
     * Handle label updates
     */
    handleLabelUpdate(event) {
        console.log('Label update received:', event);
        
        const action = event.action || 'updated';
        const label = event.label;
        
        switch (action) {
            case 'created':
                // Labels are usually handled at task level
                this.showNotification(`Nova label "${label.name}" foi criada`, 'success');
                break;
            case 'attached':
                this.attachLabelToTask(label, event.data.task_id);
                break;
            case 'detached':
                this.detachLabelFromTask(label, event.data.task_id);
                break;
            case 'updated':
                this.updateTasksWithLabel(label);
                break;
            case 'deleted':
                this.removeLabelFromAllTasks(label.id);
                break;
        }
    }

    /**
     * Update board title
     */
    updateBoardTitle(newTitle) {
        $('#board-title').text(newTitle);
        
        // Update board selector option
        $(`#board-selector option[value="${this.currentBoardId}"]`).text(newTitle);
    }

    /**
     * Handle board deletion
     */
    handleBoardDeleted() {
        showAlert('Este quadro foi excluído.', 'warning');
        
        // Remove from selector
        $(`#board-selector option[value="${this.currentBoardId}"]`).remove();
        
        // Show empty state
        showEmptyState();
        this.disconnect();
    }

    /**
     * Add new column to board
     */
    addColumnToBoard(column) {
        const $container = $('#columns-container');
        const $ghostColumn = $container.find('.ghost-column');
        
        // Create column element
        const columnElement = createColumnElement(column);
        
        // Insert before ghost column
        if ($ghostColumn.length) {
            $ghostColumn.before(columnElement);
        } else {
            $container.append(columnElement);
        }
        
        // Reinitialize sortable
        initializeSortable();
        
        // Show notification
        this.showNotification(`Nova coluna "${column.name}" foi adicionada`, 'success');
    }

    /**
     * Update existing column
     */
    updateColumn(column) {
        const $column = $(`.kanban-column[data-column-id="${column.id}"]`);
        if ($column.length) {
            // Update column name
            $column.find('h6').first().text(column.name);
            
            // Update task count
            const taskCount = column.tasks ? column.tasks.length : 0;
            $column.find('.badge').text(taskCount);
            
            this.showNotification(`Coluna "${column.name}" foi atualizada`, 'info');
        }
    }

    /**
     * Remove column from board
     */
    removeColumn(columnId) {
        const $column = $(`.kanban-column[data-column-id="${columnId}"]`);
        if ($column.length) {
            const columnName = $column.find('h6').first().text();
            $column.fadeOut(300, function() {
                $(this).remove();
            });
            
            this.showNotification(`Coluna "${columnName}" foi removida`, 'warning');
        }
    }

    /**
     * Add task to column
     */
    addTaskToColumn(task) {
        const $columnBody = $(`.kanban-column-body[data-column-id="${task.column_id}"]`);
        if ($columnBody.length) {
            const taskElement = createTaskElement(task);
            const $ghostTask = $columnBody.find('.ghost-card');
            
            // Insert before ghost task
            $ghostTask.before(taskElement);
            
            // Update task count
            this.updateColumnTaskCount(task.column_id);
            
            // Highlight new task
            const $newTask = $columnBody.find(`[data-task-id="${task.id}"]`);
            $newTask.addClass('border-success border-2');
            setTimeout(() => {
                $newTask.removeClass('border-success border-2');
            }, 2000);
            
            this.showNotification(`Nova tarefa "${task.title}" foi criada`, 'success');
        }
    }

    /**
     * Update existing task
     */
    updateTask(task) {
        const $task = $(`.kanban-card[data-task-id="${task.id}"]`);
        
        if ($task.length) {
            // Check if task moved to different column
            const currentColumnId = $task.closest('.kanban-column-body').data('column-id');
            
            if (currentColumnId != task.column_id) {
                // Move task to new column
                this.moveTaskToColumn(task, currentColumnId);
            } else {
                // Update task content
                this.updateTaskContent($task, task);
            }
            
            this.showNotification(`Tarefa "${task.title}" foi atualizada`, 'info');
        }
    }

    /**
     * Move task to different column
     */
    moveTaskToColumn(task, oldColumnId) {
        const $oldTask = $(`.kanban-card[data-task-id="${task.id}"]`);
        const $newColumnBody = $(`.kanban-column-body[data-column-id="${task.column_id}"]`);
        
        if ($oldTask.length && $newColumnBody.length) {
            // Remove from old position
            $oldTask.remove();
            
            // Add to new position
            const taskElement = createTaskElement(task);
            const $ghostTask = $newColumnBody.find('.ghost-card');
            $ghostTask.before(taskElement);
            
            // Update task counts
            this.updateColumnTaskCount(oldColumnId);
            this.updateColumnTaskCount(task.column_id);
            
            // Highlight moved task
            const $movedTask = $newColumnBody.find(`[data-task-id="${task.id}"]`);
            $movedTask.addClass('border-primary border-2');
            setTimeout(() => {
                $movedTask.removeClass('border-primary border-2');
            }, 2000);
        }
    }

    /**
     * Update task content
     */
    updateTaskContent($taskElement, task) {
        // Update task title
        $taskElement.find('.card-title').text(task.title);
        
        // Update task description
        const $description = $taskElement.find('.card-text');
        if (task.description) {
            const truncatedDescription = task.description.substring(0, 100);
            const finalDescription = task.description.length > 100 ? truncatedDescription + '...' : truncatedDescription;
            $description.text(finalDescription).show();
        } else {
            $description.hide();
        }
        
        // Update labels
        this.updateTaskLabels($taskElement, task.labels || []);
        
        // Update due date
        const $dueDateElement = $taskElement.find('.text-muted').last();
        if (task.due_date) {
            $dueDateElement.text(formatDate(task.due_date)).show();
        } else {
            $dueDateElement.hide();
        }
    }

    /**
     * Update task labels
     */
    updateTaskLabels($taskElement, labels) {
        const $labelsContainer = $taskElement.find('.task-labels');
        
        if (labels.length > 0) {
            const labelsHtml = labels.map(label => 
                `<span class="task-label" style="background-color: ${label.color}">${escapeHtml(label.name)}</span>`
            ).join('');
            $labelsContainer.html(labelsHtml).show();
        } else {
            $labelsContainer.hide();
        }
    }

    /**
     * Remove task from board
     */
    removeTask(taskId) {
        const $task = $(`.kanban-card[data-task-id="${taskId}"]`);
        if ($task.length) {
            const taskTitle = $task.find('.card-title').text();
            const columnId = $task.closest('.kanban-column-body').data('column-id');
            
            $task.fadeOut(300, function() {
                $(this).remove();
            });
            
            // Update task count
            setTimeout(() => {
                this.updateColumnTaskCount(columnId);
            }, 300);
            
            this.showNotification(`Tarefa "${taskTitle}" foi removida`, 'warning');
        }
    }

    /**
     * Update column task count
     */
    updateColumnTaskCount(columnId) {
        const $column = $(`.kanban-column[data-column-id="${columnId}"]`);
        const taskCount = $column.find('.kanban-card:not(.ghost-card)').length;
        $column.find('.badge').text(taskCount);
    }

    /**
     * Attach label to task
     */
    attachLabelToTask(label, taskId) {
        const $task = $(`.kanban-card[data-task-id="${taskId}"]`);
        if ($task.length) {
            let $labelsContainer = $task.find('.task-labels');
            
            // Create labels container if it doesn't exist
            if (!$labelsContainer.length) {
                $task.find('.card-body').prepend('<div class="task-labels mb-2"></div>');
                $labelsContainer = $task.find('.task-labels');
            }
            
            // Add new label
            const labelHtml = `<span class="task-label" style="background-color: ${label.color}">${escapeHtml(label.name)}</span>`;
            $labelsContainer.append(labelHtml).show();
        }
    }

    /**
     * Detach label from task
     */
    detachLabelFromTask(label, taskId) {
        const $task = $(`.kanban-card[data-task-id="${taskId}"]`);
        if ($task.length) {
            const $labelsContainer = $task.find('.task-labels');
            $labelsContainer.find(`.task-label:contains("${label.name}")`).remove();
            
            // Hide container if no labels
            if ($labelsContainer.children().length === 0) {
                $labelsContainer.hide();
            }
        }
    }

    /**
     * Update all tasks that have a specific label
     */
    updateTasksWithLabel(label) {
        $(`.task-label:contains("${label.name}")`).each(function() {
            $(this).css('background-color', label.color);
        });
    }

    /**
     * Remove label from all tasks
     */
    removeLabelFromAllTasks(labelId) {
        // This is a simplified approach - in a real scenario you'd want to track label IDs
        // For now, we'll refresh the board to ensure consistency
        if (typeof refreshCurrentBoard === 'function') {
            refreshCurrentBoard();
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Use existing showAlert function if available
        if (typeof showAlert === 'function') {
            showAlert(message, type);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }
}

// Initialize global instance
window.boardRealTime = new BoardRealTime();

/**
 * Auto-initialize board connection when page loads
 */
document.addEventListener('DOMContentLoaded', function() {
    // Try to get board ID from various sources
    const boardId = getBoardIdFromPage();
    
    if (boardId) {
        window.boardRealTime.connectToBoard(boardId);
    }
    
    // Listen for board changes
    setupBoardChangeListeners();
});

/**
 * Get board ID from the current page
 */
function getBoardIdFromPage() {
    // Try to get from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    let boardId = urlParams.get('board');
    
    // Try to get from data attribute
    if (!boardId) {
        const boardElement = document.querySelector('[data-board-id]');
        boardId = boardElement ? boardElement.dataset.boardId : null;
    }
    
    // Try to get from global variable
    if (!boardId && typeof window.currentBoardId !== 'undefined') {
        boardId = window.currentBoardId;
    }
    
    // Try to get from board selector
    if (!boardId) {
        const boardSelector = document.querySelector('#board-selector');
        boardId = boardSelector ? boardSelector.value : null;
    }
    
    return boardId;
}

/**
 * Setup listeners for board changes
 */
function setupBoardChangeListeners() {
    // Listen for board selector changes
    const boardSelector = document.querySelector('#board-selector');
    if (boardSelector) {
        boardSelector.addEventListener('change', function() {
            const newBoardId = this.value;
            if (newBoardId) {
                window.boardRealTime.connectToBoard(newBoardId);
            } else {
                window.boardRealTime.disconnect();
            }
        });
    }
    
    // Listen for programmatic board changes
    window.addEventListener('board-changed', function(event) {
        const boardId = event.detail.boardId;
        if (boardId) {
            window.boardRealTime.connectToBoard(boardId);
        } else {
            window.boardRealTime.disconnect();
        }
    });
}

/**
 * Global function to manually connect to a board
 */
window.connectToBoard = function(boardId) {
    window.boardRealTime.connectToBoard(boardId);
    
    // Dispatch event for other components
    window.dispatchEvent(new CustomEvent('board-connected', { 
        detail: { boardId: boardId } 
    }));
};

/**
 * Global function to disconnect from current board
 */
window.disconnectFromBoard = function() {
    window.boardRealTime.disconnect();
    
    // Dispatch event for other components
    window.dispatchEvent(new CustomEvent('board-disconnected'));
};

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BoardRealTime;
}
