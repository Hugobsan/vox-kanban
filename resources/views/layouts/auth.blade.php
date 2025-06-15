<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vox Kanban - Autenticação')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/auth.css'])
    
    @stack('styles')
</head>

<body>
    <div class="auth-container">
        @yield('content')
    </div>

    <!-- jQuery  -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Base JavaScript -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function showAlert(message, type = 'info', container = '#alerts-container') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            $(container).html(alertHtml);

            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }

        function handleApiError(xhr) {
            let message = 'Ocorreu um erro inesperado.';

            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('<br>');
                }
            }

            showAlert(message, 'danger');
        }

        function togglePassword(button) {
            const input = $(button).siblings('input');
            const icon = $(button).find('.material-icons');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.text('visibility_off');
            } else {
                input.attr('type', 'password');
                icon.text('visibility');
            }
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

        // Check if user is already authenticated
        $(document).ready(function() {
            const token = localStorage.getItem('auth_token');
            if (token && window.location.pathname !== '/dashboard') {
                // Verify token is still valid
                $.ajax({
                    url: '/api/me',
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    success: function() {
                        window.location.href = '/dashboard';
                    },
                    error: function() {
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user_data');
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
