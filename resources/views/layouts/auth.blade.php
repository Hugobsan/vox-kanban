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

    <!-- CSS Personalizado de /resources/css/auth.css -->
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1),
                0 10px 10px -5px rgb(0 0 0 / 0.04);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .auth-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .auth-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        .auth-body {
            padding: 2rem;
        }

        .form-floating .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            height: 60px;
            font-size: 16px;
        }

        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }

        .form-floating label {
            color: var(--secondary-color);
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 12px;
            height: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #5856eb;
            border-color: #5856eb;
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            border-color: #e2e8f0;
            color: var(--secondary-color);
            border-radius: 12px;
            height: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-1px);
        }

        .auth-footer {
            text-align: center;
            padding: 1.5rem 2rem;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .material-icons {
            vertical-align: middle;
        }

        .alert {
            border: none;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            color: var(--secondary-color);
            font-size: 14px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--secondary-color);
            cursor: pointer;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .loading-btn .spinner-border {
            width: 1rem;
            height: 1rem;
        }

        @media (max-width: 576px) {
            .auth-header {
                padding: 1.5rem;
            }

            .auth-header h1 {
                font-size: 1.5rem;
            }

            .auth-body {
                padding: 1.5rem;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="auth-container">
        @yield('content')
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

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
