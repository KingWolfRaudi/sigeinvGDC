<?php

namespace App\Http\Controllers;

use App\Models\Computador;
use App\Models\Dispositivo;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComputadoresExport;
use App\Exports\DispositivosExport;
use App\Exports\InsumosExport;
use App\Exports\CatalogosExport;
use App\Exports\MassiveReportExport;
use App\Exports\IncidenciasExport;
use App\Exports\MovimientosExport;
use App\Exports\UsersExport;

class ReporteController extends Controller
{
    /**
     * Genera la ficha técnica de un computador en PDF (Vista de Impresión).
     */
    public function computadorFicha($id)
    {
        $pc = Computador::with(['marca', 'tipoDispositivo', 'sistemaOperativo', 'procesador.marca', 'gpu.marca', 'trabajador', 'departamento', 'movimientos.aprobador', 'discos', 'rams', 'puertos'])
            ->findOrFail($id);
            
        $pdf = Pdf::loadView('reports.ficha-computador', compact('pc'));

        activity()
            ->performedOn($pc)
            ->log("Generó ficha técnica PDF del equipo: {$pc->bien_nacional}");

        return $pdf->stream("Ficha_Tecnica_{$pc->bien_nacional}.pdf");
    }

    /**
     * Genera la ficha técnica de un dispositivo en PDF (Vista de Impresión).
     */
    public function dispositivoFicha($id)
    {
        $disp = Dispositivo::with(['marca', 'tipoDispositivo', 'trabajador', 'departamento', 'computador', 'puertos', 'movimientos.aprobador'])
            ->findOrFail($id);
            
        $pdf = Pdf::loadView('reports.ficha-dispositivo', compact('disp'));

        activity()
            ->performedOn($disp)
            ->log("Generó ficha técnica PDF del dispositivo: {$disp->nombre} (BN: {$disp->bien_nacional})");

        return $pdf->stream("Ficha_Dispositivo_{$disp->bien_nacional}.pdf");
    }

    /**
     * Genera la ficha técnica de un insumo en PDF (Vista de Impresión).
     */
    public function insumoFicha($id)
    {
        $insumo = Insumo::with(['marca', 'categoriaInsumo', 'movimientos.aprobador'])
            ->findOrFail($id);
            
        $pdf = Pdf::loadView('reports.ficha-insumo', compact('insumo'));

        activity()
            ->performedOn($insumo)
            ->log("Generó ficha técnica PDF del insumo: {$insumo->nombre}");

        return $pdf->stream("Ficha_Insumo_{$insumo->nombre}.pdf");
    }

    /**
     * Exporta el inventario de computadores a Excel (Con Filtros).
     */
    public function computadoresExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado', 'departamento_id']);
        
        activity()->log("Exportó inventario de Computadores a Excel con filtros: " . json_encode($filters));

        return Excel::download(new ComputadoresExport($filters), 'Inventario_Computadores_' . now()->format('d-m-Y') . '.xlsx');
    }

    /**
     * Exporta el inventario de dispositivos a Excel.
     */
    public function dispositivosExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado', 'departamento_id']);
        
        activity()->log("Exportó inventario de Dispositivos a Excel con filtros: " . json_encode($filters));

        return Excel::download(new DispositivosExport($filters), 'Inventario_Dispositivos_' . now()->format('d-m-Y') . '.xlsx');
    }

    /**
     * Exporta el inventario de insumos a Excel.
     */
    public function insumosExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado']);
        
        activity()->log("Exportó catálogo de Insumos a Excel con filtros: " . json_encode($filters));

        return Excel::download(new InsumosExport($filters), 'Inventario_Insumos_' . now()->format('d-m-Y') . '.xlsx');
    }

    /**
     * Exporta catálogos genéricos.
     */
    public function catalogoExcel(Request $request, $tipo)
    {
        $this->authorize('reportes-excel');

        $map = [
            'marcas' => [\App\Models\Marca::class, 'Marcas'],
            'tipos' => [\App\Models\TipoDispositivo::class, 'Tipos_Dispositivos'],
            'departamentos' => [\App\Models\Departamento::class, 'Departamentos'],
            'trabajadores' => [\App\Models\Trabajador::class, 'Trabajadores'],
            'procesadores' => [\App\Models\Procesador::class, 'Procesadores'],
            'gpus' => [\App\Models\Gpu::class, 'GPUs'],
            'puertos' => [\App\Models\Puerto::class, 'Puertos'],
            'so' => [\App\Models\SistemaOperativo::class, 'Sistemas_Operativos'],
        ];

        if (!isset($map[$tipo])) abort(404);

        [$model, $title] = $map[$tipo];
        $filters = $request->only(['search', 'estado']);

        activity()->log("Exportó catálogo de {$title} a Excel");

        return Excel::download(new CatalogosExport($model, $filters, $title), "Catalogo_{$title}_" . now()->format('d-m-Y') . ".xlsx");
    }

    /**
     * Exporta reporte de Incidencias.
     */
    public function incidenciasExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado', 'prioridad']);
        
        activity()->log("Exportó reporte de Incidencias a Excel");

        return Excel::download(new IncidenciasExport($filters), 'Reporte_Incidencias_' . now()->format('d-m-Y') . '.xlsx');
    }

    /**
     * Exporta reporte de Movimientos.
     */
    public function movimientosExcel(Request $request, $segmento)
    {
        $this->authorize('reportes-excel');

        $map = [
            'computadores' => [\App\Models\MovimientoComputador::class, 'Movimientos_Computadores'],
            'dispositivos' => [\App\Models\MovimientoDispositivo::class, 'Movimientos_Dispositivos'],
            'insumos'      => [\App\Models\MovimientoInsumo::class, 'Movimientos_Insumos'],
        ];

        if (!isset($map[$segmento])) abort(404);

        [$model, $title] = $map[$segmento];
        $filters = $request->only(['search', 'tipo_operacion', 'estado_workflow', 'tipo_modelo']);

        activity()->log("Exportó reporte de {$title} a Excel con filtros: " . json_encode($filters));

        return Excel::download(new MovimientosExport($model, $filters, $title), "Reporte_{$title}_" . now()->format('d-m-Y') . ".xlsx");
    }

    /**
     * Exporta listado de Usuarios.
     */
    public function usuariosExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado']);
        
        activity()->log("Exportó listado de Usuarios del Sistema a Excel con filtros: " . json_encode($filters));

        return Excel::download(new UsersExport($filters), 'Listado_Usuarios_' . now()->format('d-m-Y') . '.xlsx');
    }

    /**
     * Generador de Reporte Masivo.
     */
    public function reporteMasivo(Request $request)
    {
        $this->authorize('reportes-masivos-filtros');

        $selections = $request->input('selections');

        // Si viene como string JSON (desde el form oculto), decodificarlo
        if (is_string($selections)) {
            $selections = json_decode($selections, true);
        }
        
        if (empty($selections)) return back()->with('error', 'Debe seleccionar al menos un módulo.');

        activity()->log("Generó reporte masivo multi-hoja de " . count($selections) . " módulos.");

        return Excel::download(new MassiveReportExport($selections), 'Reporte_MasivoGDC_' . now()->format('d-m-Y') . '.xlsx');
    }
}
