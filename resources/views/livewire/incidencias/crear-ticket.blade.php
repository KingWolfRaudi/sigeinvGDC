<div>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header Premium -->
                <div class="card border-0 rounded-4 shadow-sm mb-4 bg-primary text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
                    <div class="position-absolute top-0 end-0 opacity-25" style="transform: translate(30%, -30%);">
                        <i class="bi bi-headset" style="font-size: 15rem;"></i>
                    </div>
                    <div class="card-body p-4 p-md-5 position-relative z-1">
                        <h2 class="fw-bold mb-2">Reportar Incidentes Generales (SIGEINV)</h2>
                        <p class="mb-0 opacity-75 fs-5">Genera un nuevo ticket de soporte. Nuestro equipo de técnicos atenderá tu solicitud a la brevedad.</p>
                    </div>
                </div>

                <!-- Formulario -->
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        @if(!Auth::user()->trabajador)
                            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 rounded-3 mb-4 d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill fs-4 text-warning me-3"></i>
                                <div>
                                    <h6 class="fw-bold text-body mb-1">Perfil Incompleto</h6>
                                    <p class="mb-0 small text-body opacity-75">Tu usuario no está vinculado a una ficha de trabajador. Deberás seleccionar manualmente el departamento administrativo en el cual te encuentras ahora mismo para que los técnicos puedan ubicar este requerimiento.</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info border-0 bg-info bg-opacity-10 rounded-3 mb-4 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill fs-4 text-info me-3"></i>
                                <div>
                                    <p class="mb-0 small text-body opacity-75">Este ticket se asociará automáticamente a tu departamento: <strong>{{ Auth::user()->trabajador->departamento->nombre ?? 'Sin Asignar' }}</strong></p>
                                </div>
                            </div>
                        @endif

                        <form wire:submit.prevent="submitTicket">
                            
                            @if(!Auth::user()->trabajador)
                            <div class="row">
                                <!-- Departamento Manual -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold text-body mb-2">
                                        <i class="bi bi-building text-primary me-2"></i>Departamento <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg border-2 shadow-none @error('departamento_id') is-invalid @enderror" wire:model.live="departamento_id">
                                        <option value="">Selecciona tu departamento actual</option>
                                        @foreach($catalogoDepartamentos as $dep)
                                            <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('departamento_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <!-- Dependencia Manual -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold text-body mb-2">
                                        <i class="bi bi-diagram-2 text-primary me-2"></i>Dependencia <span class="text-muted fw-normal small">(Opcional)</span>
                                    </label>
                                    <select class="form-select form-select-lg border-2 shadow-none @error('dependencia_id') is-invalid @enderror" wire:model="dependencia_id" {{ empty($dependencias_disponibles) ? 'disabled' : '' }}>
                                        <option value="">Selecciona una dependencia...</option>
                                        @foreach($dependencias_disponibles as $depen)
                                            <option value="{{ $depen->id }}">{{ $depen->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @if(empty($dependencias_disponibles) && $departamento_id)
                                        <div class="form-text text-muted small mt-1"><i class="bi bi-info-circle"></i> Este departamento no tiene dependencias.</div>
                                    @endif
                                    @error('dependencia_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            @endif
                            
                            <!-- Tipo de Problema -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-body mb-2">
                                    <i class="bi bi-bookmark-star-fill text-primary me-2"></i>¿Qué tipo de problema presentas? <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg border-2 shadow-none @error('problema_id') is-invalid @enderror" wire:model="problema_id">
                                    <option value="">Selecciona la categoría más adecuada</option>
                                    @foreach($catalogoProblemas as $prob)
                                        <option value="{{ $prob->id }}">{{ $prob->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('problema_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Equipo Asociado (Opcional/Obligatorio) -->
                            @if(count($misEquipos) > 0 || count($misDispositivos) > 0)
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-body mb-2">
                                        <i class="bi bi-pc-display text-primary me-2"></i>¿Deseas relacionar un equipo a este reporte?
                                        @if($activoObligatorio)<span class="text-danger">*</span>@else<span class="text-muted fw-normal small">(Opcional)</span>@endif
                                    </label>
                                    
                                    <div class="bg-body-secondary p-3 rounded-3 border">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <select class="form-select border-2 shadow-none @error('tipo_activo') is-invalid @enderror" wire:model.live="tipo_activo">
                                                    <option value="">Ninguno</option>
                                                    @if(count($misEquipos) > 0)<option value="computador">Computador</option>@endif
                                                    @if(count($misDispositivos) > 0)<option value="dispositivo">Dispositivo</option>@endif
                                                </select>
                                                @error('tipo_activo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-8">
                                                @if($tipo_activo === 'computador')
                                                    <select class="form-select border-2 shadow-none @error('modelo_id') is-invalid @enderror" wire:model="modelo_id">
                                                        <option value="">Selecciona tu Computador</option>
                                                        @foreach($misEquipos as $eq)
                                                            <option value="{{ $eq->id }}">{{ $eq->codigo }} - {{ $eq->marca->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($tipo_activo === 'dispositivo')
                                                    <select class="form-select border-2 shadow-none @error('modelo_id') is-invalid @enderror" wire:model="modelo_id">
                                                        <option value="">Selecciona tu Dispositivo</option>
                                                        @foreach($misDispositivos as $dis)
                                                            <option value="{{ $dis->id }}">{{ $dis->codigo }} - {{ $dis->marca->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input type="text" class="form-control bg-transparent border-0 text-muted" placeholder="No se requiere selección" disabled>
                                                @endif
                                                @error('modelo_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($activoObligatorio)
                                <div class="alert alert-danger border-0 d-flex align-items-center mb-4">
                                    <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                                    <div>El sistema requiere relacionar un activo, pero no tienes equipos asignados. Por favor contacta al administrador.</div>
                                </div>
                            @endif

                            <!-- Descripción -->
                            <div class="mb-5">
                                <label class="form-label fw-bold text-body mb-2">
                                    <i class="bi bi-text-paragraph text-primary me-2"></i>Descripción Detallada <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control border-2 shadow-none @error('descripcion') is-invalid @enderror" 
                                          wire:model="descripcion" 
                                          rows="5" 
                                          placeholder="Describe el problema que presentas, qué estabas haciendo cuando ocurrió, si hay algún mensaje de error, etc."
                                          maxlength="500"></textarea>
                                <div class="form-text d-flex justify-content-between">
                                    <span>Sé lo más explícito posible para ayudar a los técnicos.</span>
                                    <span x-data="{ desc: @entangle('descripcion') }" x-text="(desc ? desc.length : 0) + ' / 500'"></span>
                                </div>
                                @error('descripcion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <hr class="mb-4 text-muted opacity-25">

                            <div class="d-flex justify-content-end gap-3 align-items-center">
                                <a href="{{ route('incidencias.gestion') }}" class="btn btn-light fw-bold px-4 hover-shadow">Cancelar</a>
                                <button type="submit" class="btn btn-primary fw-bold px-5 shadow-sm" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="submitTicket">Generar Reporte</span>
                                    <span wire:loading wire:target="submitTicket"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Microanimaciones (Alpine - CSS) -->
    <style>
        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            transition: all 0.2s ease-in-out;
        }
    </style>
</div>
