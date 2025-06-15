@extends('layouts.auth')

@section('title', 'Cadastro - Vox Kanban')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div class="mb-3">
            <span class="material-icons" style="font-size: 3rem;">person_add</span>
        </div>
        <h1>Criar Conta</h1>
        <p>Junte-se ao Vox Kanban e organize suas tarefas</p>
    </div>
    
    <div class="auth-body">
        <div id="alerts-container"></div>
        
        <form id="register-form">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name" placeholder="Seu nome completo" required>
                <label for="name">
                    <span class="material-icons me-2">person</span>
                    Nome completo
                </label>
            </div>
            
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
            
            <div class="form-floating mb-3 position-relative">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirme sua senha" required>
                <label for="password_confirmation">
                    <span class="material-icons me-2">lock_outline</span>
                    Confirmar senha
                </label>
                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                    <span class="material-icons">visibility</span>
                </button>
            </div>
            
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                <label class="form-check-label" for="terms">
                    Concordo com os <a href="#" onclick="showTerms()">termos de uso</a> e 
                    <a href="#" onclick="showPrivacy()">política de privacidade</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-3">
                <span class="material-icons me-2">person_add</span>
                Criar conta
            </button>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">
                <span class="material-icons me-2">login</span>
                Já tenho uma conta
            </a>
        </form>
    </div>
    
    <div class="auth-footer">
        <p class="mb-0">
            Ao criar uma conta, você concorda com nossos 
            <a href="#" onclick="showTerms()">termos de serviço</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Password strength indicator (optional enhancement)
    $('#password').on('input', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        // You can add visual feedback here
    });
    
    // Real-time password confirmation validation
    $('#password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmation = $(this).val();
        
        if (confirmation && password !== confirmation) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        
        // Validate passwords match
        const password = $('#password').val();
        const passwordConfirmation = $('#password_confirmation').val();
        
        if (password !== passwordConfirmation) {
            showAlert('As senhas não coincidem.', 'danger');
            return;
        }
        
        // Get form data
        const formData = {
            name: $('#name').val(),
            email: $('#email').val(),
            password: password,
            password_confirmation: passwordConfirmation
        };
        
        // Show loading state
        setLoading($submitBtn, true);
        
        // Make API call
        $.ajax({
            url: '/api/register',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message || 'Conta criada com sucesso! Redirecionando para o login...', 'success');
                    
                    // Redirect to login after success
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 2000);
                } else {
                    showAlert('Erro ao criar conta. Tente novamente.', 'danger');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        showAlert(errors.join('<br>'), 'danger');
                    } else {
                        showAlert('Dados inválidos. Verifique os campos e tente novamente.', 'danger');
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

function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

function showTerms() {
    showAlert('Termos de uso: Você concorda em usar esta aplicação de forma responsável e não compartilhar credenciais.', 'info');
}

function showPrivacy() {
    showAlert('Política de privacidade: Seus dados são protegidos e não serão compartilhados com terceiros.', 'info');
}
</script>
@endpush
