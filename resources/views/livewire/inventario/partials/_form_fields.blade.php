{{-- 
    Partial para los campos del formulario de Computadores.
    Se utiliza tanto en el inventario como en el generador de movimientos.
--}}
<h6 class="border-bottom pb-2 text-primary">1. Identificación y Hardware Base</h6>
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <label class="form-label">Nombre del Equipo <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nombre_equipo') is-invalid @enderror" wire:model="nombre_equipo" maxlength="15" placeholder="Ej: PC-ADM-01">
        @error('nombre_equipo') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
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
        <label class="form-label">Tipo de Computador <span class="text-danger">*</span></label>
        <select class="form-select @error('tipo_computador') is-invalid @enderror" wire:model="tipo_computador">
            <option value="">Seleccione...</option>
            <option value="Computador de escritorio">Computador de escritorio</option>
            <option value="Laptop">Laptop</option>
            <option value="Mini Laptop">Mini Laptop</option>
        </select>
        @error('tipo_computador') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Sist. Operativo <span class="text-danger">*</span></label>
        <div class="input-group">
            @if($creando_so)
                <input type="text" class="form-control border-primary" wire:model="nuevo_so" placeholder="Nuevo SO...">
                <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_so', false)"><i class="bi bi-x-lg"></i></button>
            @else
                <select class="form-select @error('sistema_operativo_id') is-invalid @enderror" wire:model="sistema_operativo_id">
                    <option value="">Seleccione...</option>
                    @foreach($sistemas as $s) <option value="{{ $s->id }}">{{ $s->nombre }}</option> @endforeach
                </select>
                <button class="btn btn-outline-success" type="button" wire:click="$set('creando_so', true)"><i class="bi bi-plus-lg"></i></button>
            @endif
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Procesador <span class="text-danger">*</span></label>
        @if($creando_procesador)
            <div class="input-group">
                <select class="form-select border-primary" wire:model="nuevo_procesador_marca_id">
                    <option value="">Marca...</option>
                    @foreach($marcas as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                </select>
                <input type="text" class="form-control border-primary w-25" wire:model="nuevo_procesador_modelo" placeholder="Modelo...">
                <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_procesador', false)"><i class="bi bi-x-lg"></i></button>
            </div>
        @else
            <div class="input-group">
                <select class="form-select @error('procesador_id') is-invalid @enderror" wire:model="procesador_id">
                    <option value="">Seleccione...</option>
                    @foreach($procesadores as $p) <option value="{{ $p->id }}">{{ $p->marca->nombre }} {{ $p->modelo }}</option> @endforeach
                </select>
                <button class="btn btn-outline-success" type="button" wire:click="$set('creando_procesador', true)" title="Crear rápido"><i class="bi bi-plus-lg"></i></button>
            </div>
        @endif
        @error('procesador_id') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">GPU Dedicada (Opcional)</label>
        @if($creando_gpu)
            <div class="input-group">
                <select class="form-select border-primary" wire:model="nueva_gpu_marca_id">
                    <option value="">Marca...</option>
                    @foreach($marcas as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                </select>
                <input type="text" class="form-control border-primary w-25" wire:model="nueva_gpu_modelo" placeholder="Modelo...">
                <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_gpu', false)"><i class="bi bi-x-lg"></i></button>
            </div>
        @else
            <div class="input-group">
                <select class="form-select" wire:model="gpu_id">
                    <option value="">Ninguna / Integrada</option>
                    @foreach($gpus as $g) <option value="{{ $g->id }}">{{ $g->marca->nombre }} {{ $g->modelo }}</option> @endforeach
                </select>
                <button class="btn btn-outline-success" type="button" wire:click="$set('creando_gpu', true)" title="Crear rápida"><i class="bi bi-plus-lg"></i></button>
            </div>
        @endif
    </div>
    <div class="col-md-4 mb-3 d-flex flex-column justify-content-center">
        <label class="form-label d-none d-md-block">&nbsp;</label> <div class="d-flex gap-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="unidad_dvd_form" wire:model="unidad_dvd">
                <label class="form-check-label" for="unidad_dvd_form">Unidad DVD</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="fuente_poder_form" wire:model="fuente_poder">
                <label class="form-check-label" for="fuente_poder_form">Fuente de Poder</label>
            </div>
        </div>
    </div>
</div>

<h6 class="border-bottom pb-2 text-primary">2. Componentes Internos</h6>
<div class="row mb-4">
    <div class="col-md-6 border-end">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-bold mb-0">Módulos de RAM</label>
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm" style="width: 100px;" wire:model="tipo_ram">
                    <option value="">Tipo...</option>
                    <option value="DDR2">DDR2</option>
                    <option value="DDR3">DDR3</option>
                    <option value="DDR4">DDR4</option>
                    <option value="DDR5">DDR5</option>
                </select>
                <button type="button" class="btn btn-sm btn-success" wire:click="addRam"><i class="bi bi-plus"></i></button>
            </div>
        </div>
        @error('tipo_ram') <span class="text-danger small d-block mb-2">{{ $message }}</span> @enderror
        
        @foreach($rams as $index => $ram)
        <div class="row mb-2">
            <div class="col-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">Slot {{ $ram['slot'] }}</span>
                </div>
            </div>
            <div class="col-6">
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control" wire:model="rams.{{ $index }}.capacidad" min="1" placeholder="Ej: 8">
                    <span class="input-group-text text-muted">GB</span>
                </div>
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" wire:click="removeRam({{ $index }})"><i class="bi bi-trash"></i></button>
            </div>
        </div>
        @endforeach
    </div>

    <div class="col-md-6">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-bold mb-0">Discos de Almacenamiento</label>
            <button type="button" class="btn btn-sm btn-success" wire:click="addDisco"><i class="bi bi-plus"></i> Agregar Disco</button>
        </div>
        @foreach($discos as $index => $disco)
        <div class="row mb-2">
            <div class="col-5">
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control" wire:model="discos.{{ $index }}.capacidad" min="1" placeholder="Ej: 500">
                    <span class="input-group-text text-muted">GB</span>
                </div>
            </div>
            <div class="col-5">
                <select class="form-select form-select-sm" wire:model="discos.{{ $index }}.tipo">
                    <option value="">Tipo...</option>
                    <option value="SSD">SSD</option>
                    <option value="NVME">NVME</option>
                    <option value="M.2">M.2</option>
                    <option value="HDD">HDD</option>
                </select>
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" wire:click="removeDisco({{ $index }})"><i class="bi bi-trash"></i></button>
            </div>
        </div>
        @endforeach
    </div>
</div>

<h6 class="border-bottom pb-2 text-primary">3. Red, Asignación y Estado</h6>
<div class="row mb-3">
    <div class="col-md-3 mb-3">
        <label class="form-label">Dirección MAC</label>
        <input type="text" class="form-control @error('mac') is-invalid @enderror" wire:model="mac" placeholder="XX:XX:XX:XX:XX:XX">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Dirección IP</label>
        <input type="text" class="form-control @error('ip') is-invalid @enderror" wire:model="ip" placeholder="192.168.X.X">
        @error('ip') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Tipo Conexión</label>
        <select class="form-select" wire:model="tipo_conexion">
            <option value="">Seleccione...</option>
            <option value="Ethernet">Ethernet</option>
            <option value="Wi-Fi">Wi-Fi</option>
            <option value="Ambas">Ambas</option>
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Estado Físico <span class="text-danger">*</span></label>
        <select class="form-select" wire:model="estado_fisico">
            <option value="operativo">Operativo</option>
            <option value="danado">Dañado</option>
            <option value="en_reparacion">En Reparación</option>
            <option value="indeterminado">Indeterminado</option>
        </select>
    </div>
    
    <div class="col-md-4 mb-3">
        <label class="form-label">Departamento / Área</label>
        <div class="input-group">
            @if($creando_departamento ?? false)
                <input type="text" class="form-control border-primary" wire:model="nuevo_departamento" placeholder="Nombre del departamento...">
                <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_departamento', false)">
                    <i class="bi bi-x-lg"></i>
                </button>
            @else
                <select class="form-select" wire:model.live="departamento_id">
                    <option value="">Sin asignar / En stock</option>
                    @foreach($departamentos as $dep)
                        <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline-success" type="button" wire:click="$set('creando_departamento', true)" title="Crear nuevo departamento">
                    <i class="bi bi-plus-lg"></i>
                </button>
            @endif
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label">Trabajador Responsable (Específico)</label>
        <div class="input-group">
            <select class="form-select" wire:model="trabajador_id" @if(!$departamento_id) disabled @endif>
                @if(!$departamento_id)
                    <option value="">Seleccione un departamento primero...</option>
                @else
                    <option value="">Sin trabajador específico (Uso general del área)</option>
                    @foreach($trabajadores as $t) 
                        <option value="{{ $t->id }}">{{ $t->nombres }} {{ $t->apellidos }}</option> 
                    @endforeach
                @endif
            </select>
            <button class="btn btn-outline-primary" type="button" wire:click="abrirModalTrabajador" title="Registrar Trabajador" @if(!$departamento_id) disabled @endif>
                <i class="bi bi-person-plus-fill"></i> Nuevo
            </button>
        </div>
    </div>
</div>

<h6 class="border-bottom pb-2 text-primary">4. Puertos y Notas</h6>
<div class="row">
    <div class="col-12 mb-3">
        <div class="border rounded p-3 bg-light">
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
        <textarea class="form-control" wire:model="observaciones" rows="2"></textarea>
    </div>
</div>
