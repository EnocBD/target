<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión - Target Eyewear</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0D3A63 0%, #253761 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 440px;
            padding: 48px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-section img {
            height: 50px;
            margin-bottom: 16px;
        }

        .logo-section h1 {
            font-size: 24px;
            font-weight: 700;
            color: #253761;
            margin-bottom: 8px;
        }

        .logo-section p {
            color: #6b7280;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #253761;
            box-shadow: 0 0 0 3px rgba(37, 55, 97, 0.1);
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        .text-danger {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-warning {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: #253761;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary:hover {
            background: #1F2E52;
            transform: translateY(-1px);
        }

        .auth-links {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .auth-links p {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .auth-links a {
            color: #253761;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .auth-links a:hover {
            color: #1F2E52;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 32px 24px;
            }

            .logo-section h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo-section">
            <img src="{{ asset('images/logo.png') }}" alt="Target Eyewear" onerror="this.style.display='none'">
            <h1>Bienvenido de nuevo</h1>
            <p>Inicia sesión en tu cuenta</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    autofocus
                    placeholder="tu@email.com"
                    required>
                @error('email')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input
                    id="password"
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    name="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    required>
                @error('password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Credenciales incorrectas.</strong> Por favor, verifica tus datos e intenta nuevamente.
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <strong>Error:</strong> {{ session('error') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning">
                    <strong>Atención:</strong> {{ session('warning') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    <strong>¡Éxito!</strong> {{ session('success') }}
                </div>
            @endif

            <div class="form-group">
                <button type="submit" class="btn-primary">
                    Iniciar Sesión
                </button>
            </div>
        </form>

        <div class="auth-links">
            <p>¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
            <p><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></p>
        </div>
    </div>
</body>
</html>
