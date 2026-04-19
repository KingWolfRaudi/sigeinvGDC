<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <img src="{{ asset('favicon.ico') }}" alt="Logo" class="mb-3 shadow-sm rounded-circle p-1 bg-body border" style="width: 70px; height: 70px; object-fit: contain;">
                    <h3 class="fw-bold text-body mb-1">¡Bienvenido a SIGEINV!</h3>
                    <p class="text-body-secondary small">Gestión Integral de Inventarios y Mesa de Ayuda</p>
                </div>

                <form wire:submit.prevent="iniciarSesion">
                    
                    <div class="mb-3">
                        <label for="identificador" class="form-label">Usuario o Correo Electrónico</label>
                        <input type="text" id="identificador" class="form-control @error('identificador') is-invalid @enderror" wire:model="identificador" autofocus>
                        @error('identificador') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-body">Contraseña</label>
                        <div class="input-group">
                            <input type="password" id="password" class="form-control border-end-0 @error('password') is-invalid @enderror" wire:model="password">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password') 
                            <div class="invalid-feedback d-block">{{ $message }}</div> 
                        @enderror
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const toggleBtn = document.getElementById('togglePassword');
                            const passwordInput = document.getElementById('password');
                            const toggleIcon = document.getElementById('toggleIcon');

                            if (toggleBtn) {
                                toggleBtn.addEventListener('click', function() {
                                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                                    passwordInput.setAttribute('type', type);
                                    
                                    // Alternar icono
                                    toggleIcon.classList.toggle('bi-eye');
                                    toggleIcon.classList.toggle('bi-eye-slash');
                                });
                            }
                        });
                    </script>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" wire:model="remember">
                        <label class="form-check-label" for="remember">Mantener sesión iniciada</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <span wire:loading.remove wire:target="iniciarSesion">Entrar al Sistema</span>
                            <span wire:loading wire:target="iniciarSesion">Verificando...</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>