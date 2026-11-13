<?php

namespace App\Http\Controllers;

use App\Exports\CatalogosExport;
use App\Exports\ComputadoresExport;
use App\Exports\DispositivosExport;
use App\Exports\IncidenciasExport;
use App\Exports\InsumosExport;
use App\Exports\LogsExport;
use App\Exports\MassiveReportExport;
use App\Exports\MovimientosExport;
use App\Exports\SoftwareExport;
use App\Exports\UsersExport;
use App\Models\Computador;
use App\Models\Departamento;
use App\Models\Dependencia;
use App\Models\Dispositivo;
use App\Models\Gpu;
use App\Models\Incidencia;
use App\Models\Insumo;
use App\Models\Marca;
use App\Models\MovimientoComputador;
use App\Models\MovimientoDispositivo;
use App\Models\MovimientoInsumo;
use App\Models\Procesador;
use App\Models\Puerto;
use App\Models\SistemaOperativo;
use App\Models\TipoDispositivo;
use App\Models\Trabajador;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class ReporteController extends Controller
{
    /**
     * Genera la ficha técnica de un computador en PDF (Vista de Impresión).
     */
    public function computadorFicha($id)
    {
        $pc = Computador::with(['marca', 'sistemaOperativo', 'procesador.marca', 'gpu.marca', 'trabajador', 'departamento', 'movimientos.aprobador', 'movimientos.incidencia', 'discos', 'rams', 'puertos'])
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
        $disp = Dispositivo::with(['marca', 'tipoDispositivo', 'trabajador', 'departamento', 'computador', 'puertos', 'movimientos.aprobador', 'movimientos.incidencia'])
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
        $insumo = Insumo::with(['marca', 'categoriaInsumo', 'movimientos.aprobador', 'movimientos.incidencia'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('reports.ficha-insumo', compact('insumo'));

        activity()
            ->performedOn($insumo)
            ->log("Generó ficha técnica PDF del insumo: {$insumo->nombre}");

        return $pdf->stream("Ficha_Insumo_{$insumo->nombre}.pdf");
    }

    /**
     * Genera la ficha técnica de una GPU en PDF.
     */
    public function gpuFicha($id)
    {
        $gpu = Gpu::with(['marca', 'puertos'])->findOrFail($id);

        $pdf = Pdf::loadView('reports.ficha-gpu', compact('gpu'));

        activity()
            ->performedOn($gpu)
            ->log("Generó ficha técnica PDF de la GPU: {$gpu->modelo} ({$gpu->marca->nombre})");

        return $pdf->stream("Ficha_GPU_{$gpu->modelo}.pdf");
    }

    /**
     * Genera la ficha de una incidencia en PDF.
     */
    public function incidenciaFicha($id)
    {
        $incidencia = Incidencia::with(['problema.especialidad', 'departamento', 'trabajador', 'tecnico', 'creator', 'movimientoComputador', 'movimientoDispositivo', 'movimientoInsumo'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('reports.ficha-incidencia', compact('incidencia'));

        activity()
            ->performedOn($incidencia)
            ->log("Generó reporte de incidencia PDF: Folio #{$incidencia->id}");

        return $pdf->stream("Reporte_Incidencia_{$incidencia->id}.pdf");
    }

    /**
     * Exporta el inventario de computadores a Excel (Con Filtros).
     */
    public function computadoresExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado', 'departamento_id']);

        activity()->log('Exportó inventario de Computadores a Excel con filtros: '.json_encode($filters));

        return Excel::download(new ComputadoresExport($filters), 'Inventario_Computadores_'.now()->format('d-m-Y').'.xlsx');
    }

    /**
     * Exporta la auditoría de logs a Excel.
     */
    public function logsExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['searchUser', 'searchModule', 'dateFrom', 'dateTo']);

        activity()->log('Exportó Auditoría de Logs a Excel');

        return Excel::download(new LogsExport($filters), 'Auditoria_Logs_'.now()->format('d-m-Y').'.xlsx');
    }

    /**
     * Exporta la auditoría de logs a PDF.
     */
    public function logsPdf(Request $request)
    {
        $this->authorize('reportes-excel'); // Usamos el mismo permiso base

        $filters = $request->only(['searchUser', 'searchModule', 'dateFrom', 'dateTo']);

        $query = Activity::with('causer')->latest();

        if (! empty($filters['searchUser'])) {
            $query->whereHas('causer', function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['searchUser'].'%')
                    ->orWhere('email', 'like', '%'.$filters['searchUser'].'%');
            });
        }

        if (! empty($filters['searchModule'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('subject_type', 'like', '%'.$filters['searchModule'].'%')
                    ->orWhere('description', 'like', '%'.$filters['searchModule'].'%');
            });
        }

        if (! empty($filters['dateFrom'])) {
            $query->whereDate('created_at', '>=', $filters['dateFrom']);
        }

        if (! empty($filters['dateTo'])) {
            $query->whereDate('created_at', '<=', $filters['dateTo']);
        }

        // Limitamos a 500 registros para PDF para evitar sobrecarga de memoria
        $logs = $query->take(500)->get();

        activity()->log('Exportó Auditoría de Logs a PDF');

        $pdf = Pdf::loadView('reports.logs-pdf', compact('logs', 'filters'))->setPaper('a4', 'landscape');

        return $pdf->stream('Auditoria_Logs_'.now()->format('d-m-Y').'.pdf');
    }

    /**
     * Exporta el reporte de auditoría individual de un usuario a PDF.
     */
    public function auditoriaUsuarioPdf(Request $request, $id)
    {
        $this->authorize('ver-auditoria-usuarios');

        $user = User::with(['roles', 'especialidad'])->findOrFail($id);

        $dateFrom = $request->query('dateFrom');
        $dateTo = $request->query('dateTo');

        $query = Activity::where('causer_id', $user->id)
            ->where('causer_type', User::class)
            ->latest();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Limitamos a 200 registros en PDF
        $actividades = $query->take(200)->get();

        activity()->log("Generó Reporte PDF de Auditoría del Usuario: {$user->name}");

        $pdf = Pdf::loadView('reports.auditoria-usuario-pdf', compact('user', 'actividades', 'dateFrom', 'dateTo'))->setPaper('a4', 'portrait');

        return $pdf->stream("Auditoria_Usuario_{$user->username}_".now()->format('d-m-Y').'.pdf');
    }

    /**
     * Exporta el inventario de dispositivos a Excel.
     */
    public function dispositivosExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado', 'departamento_id']);

        activity()->log('Exportó inventario de Dispositivos a Excel con filtros: '.json_encode($filters));

        return Excel::download(new DispositivosExport($filters), 'Inventario_Dispositivos_'.now()->format('d-m-Y').'.xlsx');
    }

    /**
     * Exporta el inventario de insumos a Excel.
     */
    public function insumosExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado', 'departamento_id']);

        activity()->log('Exportó catálogo de Insumos a Excel con filtros: '.json_encode($filters));

        return Excel::download(new InsumosExport($filters), 'Inventario_Insumos_'.now()->format('d-m-Y').'.xlsx');
    }

    /**
     * Exporta catálogos genéricos.
     */
    public function catalogoExcel(Request $request, $tipo)
    {
        $this->authorize('reportes-excel');

        $map = [
            'marcas' => [Marca::class, 'Marcas'],
            'tipos' => [TipoDispositivo::class, 'Tipos_Dispositivos'],
            'departamentos' => [Departamento::class, 'Departamentos'],
            'dependencias' => [Dependencia::class, 'Dependencias'],
            'trabajadores' => [Trabajador::class, 'Trabajadores'],
            'procesadores' => [Procesador::class, 'Procesadores'],
            'gpus' => [Gpu::class, 'GPUs'],
            'puertos' => [Puerto::class, 'Puertos'],
            'so' => [SistemaOperativo::class, 'Sistemas_Operativos'],
        ];

        if (! isset($map[$tipo])) {
            abort(404);
        }

        [$model, $title] = $map[$tipo];
        $filters = $request->only(['search', 'estado']);

        activity()->log("Exportó catálogo de {$title} a Excel");

        return Excel::download(new CatalogosExport($model, $filters, $title), "Catalogo_{$title}_".now()->format('d-m-Y').'.xlsx');
    }

    /**
     * Exporta reporte de Incidencias.
     */
    public function incidenciasExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado', 'departamento_id']);

        activity()->log('Exportó reporte de Incidencias a Excel');

        return Excel::download(new IncidenciasExport($filters), 'Reporte_Incidencias_'.now()->format('d-m-Y').'.xlsx');
    }

    /**
     * Exporta reporte de Movimientos.
     */
    public function movimientosExcel(Request $request, $segmento)
    {
        $this->authorize('reportes-excel');

        $map = [
            'computadores' => [MovimientoComputador::class, 'Movimientos_Computadores'],
            'dispositivos' => [MovimientoDispositivo::class, 'Movimientos_Dispositivos'],
            'insumos' => [MovimientoInsumo::class, 'Movimientos_Insumos'],
        ];

        if (! isset($map[$segmento])) {
            abort(404);
        }

        [$model, $title] = $map[$segmento];
        $filters = $request->only(['search', 'tipo_operacion', 'estado_workflow', 'tipo_modelo']);

        activity()->log("Exportó reporte de {$title} a Excel con filtros: ".json_encode($filters));

        return Excel::download(new MovimientosExport($model, $filters, $title), "Reporte_{$title}_".now()->format('d-m-Y').'.xlsx');
    }

    /**
     * Exporta listado de Usuarios.
     */
    public function usuariosExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado']);

        activity()->log('Exportó listado de Usuarios del Sistema a Excel con filtros: '.json_encode($filters));

        return Excel::download(new UsersExport($filters), 'Listado_Usuarios_'.now()->format('d-m-Y').'.xlsx');
    }

    /**
     * Exporta el catálogo de software a Excel.
     */
    public function softwareExcel(Request $request)
    {
        $this->authorize('reportes-excel');

        $filters = $request->only(['search', 'estado']);

        activity()->log('Exportó catálogo de Software a Excel con filtros: '.json_encode($filters));

        return Excel::download(new SoftwareExport($filters), 'Catalogo_Software_'.now()->format('d-m-Y').'.xlsx');
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

        if (empty($selections)) {
            return back()->with('error', 'Debe seleccionar al menos un módulo.');
        }

        activity()->log('Generó reporte masivo multi-hoja de '.count($selections).' módulos.');

        return Excel::download(new MassiveReportExport($selections), 'Reporte_MasivoGDC_'.now()->format('d-m-Y').'.xlsx');
    }
}
