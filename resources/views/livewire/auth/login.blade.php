<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <h3 class="text-center mb-4">Iniciar Sesión</h3>

                <form wire:submit.prevent="iniciarSesion">
                    
                    <div class="mb-3">
                        <label for="identificador" class="form-label">Usuario o Correo Electrónico</label>
                        <input type="text" id="identificador" class="form-control @error('identificador') is-invalid @enderror" wire:model="identificador" autofocus>
                        @error('identificador') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" wire:model="password">
                        @error('password') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                    </div>

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