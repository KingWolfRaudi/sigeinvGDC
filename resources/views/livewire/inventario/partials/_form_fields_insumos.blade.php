{{-- 
    Partial para los campos del formulario de Insumos.
    Se utiliza tanto en el inventario como en el generador de movimientos.
--}}

<h6 class="border-bottom pb-2 text-primary">1. Identificación y Categorización</h6>
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <label class="form-label">Bien Nacional (Opcional)</label>
        <input type="text" class="form-control @error('bien_nacional') is-invalid @enderror" wire:model="bien_nacional">
        @error('bien_nacional') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Serial Fabricante (Opcional)</label>
        <input type="text" class="form-control @error('serial') is-invalid @enderror" wire:model="serial">
        @error('serial') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Categoría <span class="text-danger">*</span></label>
        <div class="input-group">
            @if($creando_categoria)
                <input type="text" class="form-control border-primary" wire:model="nueva_categoria" placeholder="Nueva categoría...">
                <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_categoria', false)"><i class="bi bi-x-lg"></i></button>
            @else
                <select class="form-select @error('categoria_insumo_id') is-invalid @enderror" wire:model="categoria_insumo_id">
                    <option value="">Seleccione...</option>
                    @foreach($categorias as $cat) <option value="{{ $cat->id }}">{{ $cat->nombre }}</option> @endforeach
                </select>
                <button class="btn btn-outline-success" type="button" wire:click="$set('creando_categoria', true)" title="Crear nueva categoría"><i class="bi bi-plus-lg"></i></button>
            @endif
        </div>
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

    <div class="col-md-9 mb-3">
        <label class="form-label">Nombre del Insumo / Modelo <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre" placeholder="Ej: Bobina Cable UTP Cat 6 / Memoria RAM DDR4 8GB">
        @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Estado Físico <span class="text-danger">*</span></label>
        <select class="form-select @error('estado_fisico') is-invalid @enderror" wire:model="estado_fisico">
            <option value="operativo">Operativo (Bueno)</option>
            <option value="danado">Dañado</option>
            <option value="en_reparacion">En Reparación</option>
            <option value="indeterminado">Indeterminado</option>
        </select>
        @error('estado_fisico') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>

<h6 class="border-bottom pb-2 text-primary">2. Distribución y Asignación</h6>
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <label class="form-label">Departamento / Área <span class="text-danger">*</span></label>
        <div class="input-group">
            @if($creando_departamento)
                <input type="text" class="form-control border-primary" wire:model="nuevo_departamento" placeholder="Nuevo dpto...">
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
        <label class="form-label">Responsable / Trabajador</label>
        <div class="input-group">
            <select class="form-select @error('trabajador_id') is-invalid @enderror" wire:model="trabajador_id" @if(!$departamento_id) disabled @endif>
                @if(!$departamento_id)
                    <option value="">Seleccione dpto...</option>
                @else
                    <option value="">Seleccione...</option>
                    @foreach($trabajadores as $trab)
                        <option value="{{ $trab->id }}">{{ $trab->nombres }} {{ $trab->apellidos }}</option>
                    @endforeach
                @endif
            </select>
            <button class="btn btn-outline-primary" type="button" wire:click="abrirModalTrabajador" @if(!$departamento_id) disabled @endif title="Crear trabajador"><i class="bi bi-person-plus-fill"></i></button>
        </div>
        @error('trabajador_id') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Dispositivo Asociado</label>
        <select class="form-select @error('dispositivo_id') is-invalid @enderror" wire:model="dispositivo_id" @if(!$departamento_id) disabled @endif>
            <option value="">Ninguno...</option>
            @foreach($dispositivos as $disp)
                <option value="{{ $disp->id }}">{{ $disp->nombre }} ({{ $disp->bien_nacional ?? $disp->serial }})</option>
            @endforeach
        </select>
        @error('dispositivo_id') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Computador Asociado</label>
        <select class="form-select @error('computador_id') is-invalid @enderror" wire:model="computador_id" @if(!$departamento_id) disabled @endif>
            <option value="">Ninguno...</option>
            @foreach($computadores as $comp)
                <option value="{{ $comp->id }}">{{ $comp->nombre_equipo }} ({{ $comp->bien_nacional ?? $comp->serial }})</option>
            @endforeach
        </select>
        @error('computador_id') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>

<h6 class="border-bottom pb-2 text-primary">3. Control de Existencias</h6>
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <label class="form-label">Unidad de Medición <span class="text-danger">*</span></label>
        <select class="form-select @error('unidad_medida') is-invalid @enderror" wire:model="unidad_medida">
            <option value="unidad">Unidades / Piezas</option>
            <option value="metros">Metros (Longitud)</option>
            <option value="litros">Litros (Volumen)</option>
            <option value="cajas">Cajas / Paquetes</option>
            <option value="pares">Pares</option>
        </select>
        @error('unidad_medida') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Stock Actual <span class="text-danger">*</span></label>
        <input type="number" step="1" min="0" 
            class="form-control @error('medida_actual') is-invalid @enderror" wire:model="medida_actual">
        @error('medida_actual') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Alerta Stock Crítico <span class="text-danger">*</span></label>
        <input type="number" step="1" min="0" 
            class="form-control @error('medida_minima') is-invalid @enderror" wire:model="medida_minima">
        @error('medida_minima') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>

<h6 class="border-bottom pb-2 text-primary">4. Atributos y Descripción</h6>
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100 bg-light border-0 shadow-sm">
                    <div class="card-body py-2 d-flex align-items-center">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="reutilizable" wire:model="reutilizable">
                            <label class="form-check-label fw-bold" for="reutilizable">Herramienta Reutilizable (Debe Retornar)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 bg-light border-0 shadow-sm">
                    <div class="card-body py-2 d-flex align-items-center">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="instalable_en_equipo" wire:model="instalable_en_equipo">
                            <label class="form-check-label fw-bold" for="instalable_en_equipo">Pieza Incrustable (Para PCs/Equipos)</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Descripción Detallada / Ficha Técnica</label>
        <textarea class="form-control @error('description') is-invalid @enderror" wire:model="descripcion" rows="4" 
            placeholder="Introduce detalles técnicos, dimensiones, colores o cualquier información relevante..."></textarea>
        @error('descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
