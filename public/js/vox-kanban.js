/**
 * Vox Kanban - Utility Functions
 * Common JavaScript utilities for the Kanban application
 */

// Global state management
window.VoxKanban = {
    currentBoardId: null,
    currentBoardData: null,
    isOwner: false,
    user: null,
    
    init: function() {
        this.user = JSON.parse(localStorage.getItem('user_data') || '{}');
        this.setupAjax();
    },
    
    setupAjax: function() {
        // CSRF Token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
    }
};

// Task Card Factory
class TaskCard {
    constructor(task) {
        this.task = task;
    }
    
    render() {
        const labels = this.task.labels || [];
        const assignees = this.task.assignees || [];
        
        return `
            <div class="kanban-card position-relative" data-task-id="${this.task.id}" onclick="showTaskDetails(${this.task.id})">
                ${this.renderDropdown()}
                <div class="p-3">
                    ${this.renderLabels(labels)}
                    ${this.renderTitle()}
                    ${this.renderDescription()}
                    ${this.renderFooter(assignees)}
                </div>
            </div>
        `;
    }
    
    renderDropdown() {
        return `
            <div class="task-dropdown">
                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation()">
                    <span class="material-icons" style="font-size: 16px;">more_vert</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="editTask(${this.task.id}); event.stopPropagation();">
                        <span class="material-icons me-2">edit</span>Editar
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="toggleTaskComplete(${this.task.id}); event.stopPropagation();">
                        <span class="material-icons me-2">${this.task.completed ? 'radio_button_unchecked' : 'check_circle'}</span>
                        ${this.task.completed ? 'Marcar pendente' : 'Marcar concluído'}
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="duplicateTask(${this.task.id}); event.stopPropagation();">
                        <span class="material-icons me-2">content_copy</span>Duplicar
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteTask(${this.task.id}); event.stopPropagation();">
                        <span class="material-icons me-2">delete</span>Excluir
                    </a></li>
                </ul>
            </div>
        `;
    }
    
    renderLabels(labels) {
        if (labels.length === 0) return '';
        
        return `
            <div class="task-labels">
                ${labels.map(label => `
                    <span class="task-label" style="background-color: ${label.color}">${escapeHtml(label.name)}</span>
                `).join('')}
            </div>
        `;
    }
    
    renderTitle() {
        return `<h6 class="mb-2">${escapeHtml(this.task.title)}</h6>`;
    }
    
    renderDescription() {
        if (!this.task.description) return '';
        
        const truncated = this.task.description.length > 100 
            ? this.task.description.substring(0, 100) + '...' 
            : this.task.description;
            
        return `<p class="text-muted small mb-2">${escapeHtml(truncated)}</p>`;
    }
    
    renderFooter(assignees) {
        return `
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    ${this.task.due_date ? `<small class="text-muted">${formatDate(this.task.due_date)}</small>` : ''}
                </div>
                ${this.renderAssignees(assignees)}
            </div>
        `;
    }
    
    renderAssignees(assignees) {
        if (assignees.length === 0) return '';
        
        return `
            <div class="d-flex">
                ${assignees.slice(0, 3).map(assignee => `
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                         style="width: 24px; height: 24px; font-size: 10px; margin-left: -4px;"
                         title="${escapeHtml(assignee.name)}">
                        ${assignee.name.charAt(0).toUpperCase()}
                    </div>
                `).join('')}
                ${assignees.length > 3 ? `<span class="small text-muted">+${assignees.length - 3}</span>` : ''}
            </div>
        `;
    }
}

// Column Factory
class KanbanColumn {
    constructor(column) {
        this.column = column;
        this.tasks = (column.tasks || []).sort((a, b) => (a.order || 0) - (b.order || 0));
    }
    
    render() {
        return `
            <div class="kanban-column" data-column-id="${this.column.id}">
                ${this.renderHeader()}
                ${this.renderBody()}
            </div>
        `;
    }
    
    renderHeader() {
        return `
            <div class="kanban-column-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">${escapeHtml(this.column.name)}</h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">${this.tasks.length}</span>
                        <span class="material-icons column-handle">drag_indicator</span>
                    </div>
                </div>
            </div>
        `;
    }
    
    renderBody() {
        return `
            <div class="kanban-column-body" data-column-id="${this.column.id}">
                ${this.tasks.map(task => new TaskCard(task).render()).join('')}
                ${this.renderGhostTask()}
            </div>
        `;
    }
    
    renderGhostTask() {
        return `
            <div class="kanban-card ghost-card d-flex align-items-center justify-content-center" 
                 style="min-height: 60px;" onclick="showCreateTaskModal(${this.column.id})">
                <div class="text-center">
                    <span class="material-icons mb-1">add</span>
                    <div class="small">Adicionar tarefa</div>
                </div>
            </div>
        `;
    }
}

// API Service
class ApiService {
    constructor() {
        this.baseUrl = '/api';
        this.token = localStorage.getItem('auth_token');
    }
    
    setToken(token) {
        this.token = token;
        localStorage.setItem('auth_token', token);
    }
    
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        };
        
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        return headers;
    }
    
    async request(method, endpoint, data = null) {
        const options = {
            method: method,
            headers: this.getHeaders()
        };
        
        if (data && ['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Request failed');
            }
            
            return result;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }
    
    // Board methods
    async getBoards() {
        return this.request('GET', '/boards');
    }
    
    async getBoard(id) {
        return this.request('GET', `/boards/${id}`);
    }
    
    async createBoard(data) {
        return this.request('POST', '/boards', data);
    }
    
    async updateBoard(id, data) {
        return this.request('PATCH', `/boards/${id}`, data);
    }
    
    async deleteBoard(id) {
        return this.request('DELETE', `/boards/${id}`);
    }
    
    // Task methods
    async getTasks(boardId) {
        return this.request('GET', `/boards/${boardId}/tasks`);
    }
    
    async getTask(id) {
        return this.request('GET', `/tasks/${id}`);
    }
    
    async createTask(data) {
        return this.request('POST', '/tasks', data);
    }
    
    async updateTask(id, data) {
        return this.request('PATCH', `/tasks/${id}`, data);
    }
    
    async deleteTask(id) {
        return this.request('DELETE', `/tasks/${id}`);
    }
    
    // Column methods
    async createColumn(data) {
        return this.request('POST', '/columns', data);
    }
    
    async updateColumn(id, data) {
        return this.request('PATCH', `/columns/${id}`, data);
    }
    
    async deleteColumn(id) {
        return this.request('DELETE', `/columns/${id}`);
    }
}

// Notification Service
class NotificationService {
    static show(message, type = 'info', duration = 5000) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#alerts-container').html(alertHtml);
        
        if (duration > 0) {
            setTimeout(() => {
                $('.alert').alert('close');
            }, duration);
        }
    }
    
    static success(message, duration = 5000) {
        this.show(message, 'success', duration);
    }
    
    static error(message, duration = 0) {
        this.show(message, 'danger', duration);
    }
    
    static warning(message, duration = 5000) {
        this.show(message, 'warning', duration);
    }
    
    static info(message, duration = 5000) {
        this.show(message, 'info', duration);
    }
}

// Utility Functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function formatDateTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { 
        hour: '2-digit', 
        minute: '2-digit' 
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

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export for use in other scripts
window.TaskCard = TaskCard;
window.KanbanColumn = KanbanColumn;
window.ApiService = ApiService;
window.NotificationService = NotificationService;

// Initialize when DOM is ready
$(document).ready(function() {
    window.VoxKanban.init();
});
