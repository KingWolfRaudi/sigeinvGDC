{{-- 
    Partial para los campos del formulario de Dispositivos.
    Se utiliza tanto en el inventario como en el generador de movimientos.
--}}
<h6 class="border-bottom pb-2 text-primary">1. Identificación y Hardware Base</h6>
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">Bien Nacional <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('bien_nacional') is-invalid @enderror" wire:model="bien_nacional">
        @error('bien_nacional') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Serial <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('serial') is-invalid @enderror" wire:model="serial">
        @error('serial') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Marca <span class="text-danger">*</span></label>
        <div class="input-group">
            @if($creando_marca)
                <input type="text" class="form-control border-primary" wire:model="nueva_marca" placeholder="Nueva marca...">
                <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_marca', false)"><i class="bi bi-x-lg"></i></button>
            @else
                <select class="form-select @error('marca_id') is-invalid @enderror" wire:model="marca_id">
                    <option value="">Seleccione...</option>
                    @foreach($marcas as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                </select>
                <button class="btn btn-outline-success" type="button" wire:click="$set('creando_marca', true)" title="Crear nueva marca"><i class="bi bi-plus-lg"></i></button>
            @endif
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Tipo Dispositivo <span class="text-danger">*</span></label>
        <div class="input-group">
            @if($creando_tipo)
                <input type="text" class="form-control border-primary" wire:model="nuevo_tipo" placeholder="Nuevo tipo...">
                <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_tipo', false)"><i class="bi bi-x-lg"></i></button>
            @else
                <select class="form-select @error('tipo_dispositivo_id') is-invalid @enderror" wire:model="tipo_dispositivo_id">
                    <option value="">Seleccione...</option>
                    @foreach($tipos as $t) <option value="{{ $t->id }}">{{ $t->nombre }}</option> @endforeach
                </select>
                <button class="btn btn-outline-success" type="button" wire:click="$set('creando_tipo', true)"><i class="bi bi-plus-lg"></i></button>
            @endif
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Modelo / Diseño Específico <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre" placeholder="Ej: LaserJet Pro M402dn">
        @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Estado de Funcionamiento <span class="text-danger">*</span></label>
        <select class="form-select @error('estado') is-invalid @enderror" wire:model="estado">
            <option value="operativo">Operativo</option>
            <option value="dañado">Dañado</option>
            <option value="en_reparacion">En Reparación</option>
            <option value="indeterminado">Indeterminado</option>
        </select>
        @error('estado') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Dirección IP (Red)</label>
        <input type="text" class="form-control @error('ip') is-invalid @enderror" wire:model="ip" placeholder="192.168.X.X">
        @error('ip') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>

<h6 class="border-bottom pb-2 text-primary">2. Distribución y Asignación</h6>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Departamento / Área <span class="text-danger">*</span></label>
        <div class="input-group">
            @if($creando_departamento)
                <input type="text" class="form-control border-primary" wire:model="nuevo_departamento" placeholder="Nuevo departamento...">
                <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_departamento', false)"><i class="bi bi-x-lg"></i></button>
            @else
                <select class="form-select @error('departamento_id') is-invalid @enderror" wire:model.live="departamento_id">
                    <option value="">Seleccione...</option>
                    @foreach($departamentos as $dep)
                        <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline-success" type="button" wire:click="$set('creando_departamento', true)" title="Crear nuevo departamento"><i class="bi bi-plus-lg"></i></button>
            @endif
        </div>
        @error('departamento_id') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Dependencia (Opcional)</label>
        <select class="form-select @error('dependencia_id') is-invalid @enderror" wire:model="dependencia_id" {{ empty($dependencias_disponibles) ? 'disabled' : '' }}>
            <option value="">Seleccione una dependencia...</option>
            @foreach($dependencias_disponibles as $depen)
                <option value="{{ $depen->id }}">{{ $depen->nombre }}</option>
            @endforeach
        </select>
        @if(empty($dependencias_disponibles) && $departamento_id)
            <div class="form-text text-muted small"><i class="bi bi-info-circle"></i> Este departamento no tiene dependencias.</div>
        @endif
        @error('dependencia_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-5 mb-3">
        <label class="form-label">Trabajador Responsable</label>
        <div class="input-group">
            <select class="form-select" wire:model="trabajador_id" @if(!$departamento_id) disabled @endif>
                @if(!$departamento_id)
                    <option value="">Seleccione dep. primero...</option>
                @else
                    <option value="">(Sin asignar)</option>
                    @foreach($trabajadores as $t)
                        <option value="{{ $t->id }}">{{ $t->nombres }} {{ $t->apellidos }}</option>
                    @endforeach
                @endif
            </select>
            <button class="btn btn-outline-primary" type="button" wire:click="abrirModalTrabajador" title="Registrar Trabajador" @if(!$departamento_id) disabled @endif>
                <i class="bi bi-person-plus-fill"></i>
            </button>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Conectado a Computador</label>
        <select class="form-select" wire:model="computador_id">
            <option value="">Seleccione si aplica...</option>
            @foreach($computadores as $comp)
                <option value="{{ $comp->id }}">{{ $comp->nombre_equipo }} ({{ $comp->tipo_computador }}) - BN: {{ $comp->bien_nacional }}</option>
            @endforeach
        </select>
    </div>
</div>

<h6 class="border-bottom pb-2 text-primary">3. Puertos de Salida y Notas Adicionales</h6>
<div class="row">
    <div class="col-12 mb-3">
        <div class="border rounded p-3 bg-body-secondary">
            <div class="row">
                @foreach($puertos as $puerto)
                <div class="col-md-3 col-sm-4 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $puerto->id }}" id="puerto_{{ $puerto->id }}" wire:model="puertos_seleccionados">
                        <label class="form-check-label text-sm" for="puerto_{{ $puerto->id }}">
                            {{ $puerto->nombre }}
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Observaciones Adicionales</label>
        <textarea class="form-control" wire:model="notas" rows="2"></textarea>
    </div>
</div>
