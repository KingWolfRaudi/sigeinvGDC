<div class="container-fluid py-5 text-center">
    <!-- Contenedor simple para devolver en el render de Livewire cuando falla -->
    <div class="card shadow-sm mx-auto" style="max-width: 500px; border-radius: 1rem;">
        <div class="card-body p-5">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
            <h3 class="mt-3 text-danger">Error Inesperado</h3>
            <p class="text-muted">
                Ocurrió un error al cargar los datos de este módulo. Por favor, recarga la página. Si el problema persiste, contacta al soporte técnico.
            </p>
            <button onclick="window.location.reload();" class="btn btn-primary mt-3">
                <i class="bi bi-arrow-clockwise me-2"></i> Recargar
            </button>
        </div>
    </div>
</div>
