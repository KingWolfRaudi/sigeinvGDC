<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 - No Autorizado | SigeinvGDC</title>
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
            color: #6c757d;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-icon {
            font-size: 4rem;
            color: #6c757d;
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
        <div class="error-icon"><i class="bi bi-person-lock text-secondary"></i></div>
        <div class="error-code">401</div>
        <h2 class="mb-3">No Autorizado</h2>
        <p class="text-muted mb-4">No tienes autorización para acceder a este recurso. Por favor, inicia sesión con una cuenta válida.</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="{{ route('login') }}" class="btn btn-secondary px-4 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
            </a>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary px-4 py-2">
                <i class="bi bi-house-door me-2"></i> Inicio
            </a>
        </div>
    </div>
</body>
</html>
