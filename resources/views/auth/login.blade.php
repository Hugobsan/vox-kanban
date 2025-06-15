@extends('layouts.auth')

@section('title', 'Login - Vox Kanban')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div class="mb-3">
            <span class="material-icons" style="font-size: 3rem;">dashboard</span>
        </div>
        <h1>Bem-vindo de volta!</h1>
        <p>Faça login para acessar seus quadros Kanban</p>
    </div>
    
    <div class="auth-body">
        <div id="alerts-container"></div>
        
        <form id="login-form">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" required>
                <label for="email">
                    <span class="material-icons me-2">email</span>
                    E-mail
                </label>
            </div>
            
            <div class="form-floating mb-3 position-relative">
                <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required>
                <label for="password">
                    <span class="material-icons me-2">lock</span>
                    Senha
                </label>
                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                    <span class="material-icons">visibility</span>
                </button>
            </div>
            
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Manter-me conectado
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-3">
                <span class="material-icons me-2">login</span>
                Entrar
            </button>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100 ">
                <span class="material-icons me-2">person_add</span>
                Criar nova conta
            </a>
        </form>
    </div>
    
    <div class="auth-footer">
        <p class="mb-0">
            Esqueceu sua senha? 
            <a href="#" onclick="showForgotPassword()">Clique aqui</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        
        // Get form data
        const formData = {
            email: $('#email').val(),
            password: $('#password').val()
        };
        
        // Show loading state
        setLoading($submitBtn, true);
        
        // Make API call
        $.ajax({
            url: '/api/login',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success && response.data.token) {
                    localStorage.setItem('auth_token', response.data.token);
                    localStorage.setItem('user_data', JSON.stringify(response.data.user));
                    
                    showAlert(response.message || 'Login realizado com sucesso!', 'success');
                    window.location.href = '/dashboard';
                } else {
                    showAlert('Resposta inválida do servidor.', 'danger');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        showAlert(errors.join('<br>'), 'danger');
                    } else {
                        showAlert('Credenciais inválidas.', 'danger');
                    }
                } else {
                    handleApiError(xhr);
                }
            },
            complete: function() {
                setLoading($submitBtn, false);
            }
        });
    });
});

function showForgotPassword() {
    showAlert('Funcionalidade de recuperação de senha em desenvolvimento.', 'info');
}
</script>
@endpush
