<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en Vista Previa</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .error-icon {
            font-size: 64px;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 24px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 12px;
        }

        .error-message {
            color: #6c757d;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .error-details {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            text-align: left;
            margin-top: 20px;
        }

        .retry-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .retry-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Error en la Vista Previa</h1>
        <p class="error-message">
            No se pudo cargar la vista previa del bloque. Esto puede deberse a un problema con la plantilla o la configuración.
        </p>

        <button class="retry-btn" onclick="window.location.reload()">
            Reintentar
        </button>

        @if(isset($error))
        <div class="error-details">
            <strong>Detalles del error:</strong><br>
            {{ $error }}
        </div>
        @endif
    </div>

    <script>
        // Send error notification to parent
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'iframeError',
                error: @json($error ?? 'Error desconocido'),
                timestamp: Date.now()
            }, window.location.origin);
        }
    </script>
</body>
</html>