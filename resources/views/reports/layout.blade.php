<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte SigeinvGDC</title>
    <style>
        @page { margin: 100px 25px; }
        header { position: fixed; top: -80px; left: 0px; right: 0px; height: 60px; border-bottom: 2px solid #333; }
        footer { position: fixed; bottom: -80px; left: 0px; right: 0px; height: 50px; text-align: center; font-size: 10px; border-top: 1px solid #ccc; padding-top: 10px; }
        .page-number:after { content: counter(page); }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin-top: 20px; }
        .h1 { font-size: 24px; font-weight: bold; margin-bottom: 10px; color: #1a2a6c; }
        .h2 { font-size: 18px; font-weight: bold; margin-bottom: 5px; color: #333; }
        .text-muted { color: #666; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background-color: #f2f2f2; border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; }
        .table td { border: 1px solid #ddd; padding: 8px; font-size: 11px; }
        .badge { padding: 4px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .bg-success { background-color: #d4edda; color: #155724; }
        .bg-danger { background-color: #f8d7da; color: #721c24; }
        .text-right { text-align: right; }
        .mb-20 { margin-bottom: 20px; }
        .header-logo { float: left; font-weight: bold; font-size: 20px; color: #1a2a6c; }
        .header-date { float: right; font-size: 12px; margin-top: 10px; }
        .clear { clear: both; }
        .signature-box { margin-top: 80px; }
        .signature-line { width: 200px; border-bottom: 1px solid #000; margin-bottom: 5px; }
    </style>
</head>
<body>
    <header>
        <div class="header-logo">SigeinvGDC</div>
        <div class="header-date">Emitido el: {{ date('d/m/Y h:i A') }}</div>
        <div class="clear"></div>
    </header>

    <footer>
        <div class="page-number">Página </div>
        <div style="margin-top: 5px;">Sistema de Gestión de Inventario Tecnológico - GDC</div>
    </footer>

    <main>
        @yield('content')
    </main>
</body>
</html>
