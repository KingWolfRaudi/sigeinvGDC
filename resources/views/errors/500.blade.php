<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error Interno del Servidor | SigeinvGDC</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #212529;
            --text-muted: #6c757d;
        }

        [data-bs-theme="dark"] {
            --bg-color: #1a1d20;
            --card-bg: #2b3035;
            --text-color: #f8f9fa;
            --text-muted: #adb5bd;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .error-container {
            text-align: center;
            padding: 3rem;
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
            max-width: 500px;
            width: 90%;
            transition: background-color 0.3s ease;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: #dc3545;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
    </style>
    <script>
        const savedTheme = localStorage.getItem('sigeinv-theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>
</head>
<body>
    <div class="error-container">
        <div class="error-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="error-code">500</div>
        <h2 class="mb-3">Error Interno del Servidor</h2>
        <p class="text-muted mb-4">¡Ups! Algo salió mal de nuestro lado. Estamos trabajando para solucionarlo. Por favor, intenta de nuevo más tarde.</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="{{ url('/') }}" class="btn btn-danger px-4 py-2">
                <i class="bi bi-house-door me-2"></i> Volver al Inicio
            </a>
            <button onclick="window.location.reload()" class="btn btn-outline-secondary px-4 py-2">
                <i class="bi bi-arrow-clockwise me-2"></i> Reintentar
            </button>
        </div>
        @if(app()->environment('local', 'testing'))
            <div class="mt-4 text-start">
                <hr>
                <small class="text-danger font-monospace" style="word-break: break-all;">
                    {{ $exception->getMessage() }}
                </small>
            </div>
        @endif
    </div>
</body>
</html>
