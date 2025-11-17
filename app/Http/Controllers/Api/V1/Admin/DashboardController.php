<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Examen;
use App\Models\Pregunta;
use App\Models\IntentoExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * RF-A.1: Dashboard - Widgets de Estadísticas y Gráficos
     */
    public function index()
    {
        try {
            // RF-A.1.1: Widgets de Estadísticas Rápidas
            $totalUsuarios = Usuario::count();
            $usuariosActivos = Usuario::where('estado', Usuario::ESTADO_ACTIVO)->count();
            $totalExamenes = Examen::count();
            $examenesPublicados = Examen::where('estado', '1')->count();
            $examenesBorrador = Examen::where('estado', '0')->count();
            $examenesFinalizados = Examen::where('estado', '2')->count();
            $totalPreguntas = Pregunta::count();
            $totalIntentos = IntentoExamen::count();
            $intentosCompletados = IntentoExamen::where('estado', 'enviado')->count();
            $intentosEnProgreso = IntentoExamen::where('estado', 'iniciado')->count();

            $estadisticas = [
                'total_usuarios' => $totalUsuarios,
                'usuarios_activos' => $usuariosActivos,
                'total_examenes' => $totalExamenes,
                'examenes_publicados' => $examenesPublicados,
                'examenes_borrador' => $examenesBorrador,
                'examenes_finalizados' => $examenesFinalizados,
                'total_preguntas' => $totalPreguntas,
                'total_intentos' => $totalIntentos,
                'intentos_completados' => $intentosCompletados,
                'intentos_en_progreso' => $intentosEnProgreso,
            ];

            // RF-A.1.2: Gráfico de Línea (Intentos por Día - últimos 30 días)
            $fechaInicio = Carbon::now(config('app.timezone'))->subDays(30);
            $intentosPorDia = IntentoExamen::where('estado', 'enviado')
                ->whereNotNull('hora_fin')
                ->where('hora_fin', '>=', $fechaInicio)
                ->select(
                    DB::raw('DATE(hora_fin) as fecha'),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get()
                ->map(function ($item) {
                    return [
                        'fecha' => $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d-m-Y') : null,
                        'total' => (int)$item->total,
                    ];
                });

            // RF-A.1.2: Gráfico de Dona (Tasa de Aprobación)
            $aprobados = IntentoExamen::where('estado', 'enviado')
                ->where('es_aprobado', true)
                ->count();
            $noAprobados = IntentoExamen::where('estado', 'enviado')
                ->where('es_aprobado', false)
                ->count();

            $tasaAprobacion = [
                'aprobados' => $aprobados,
                'no_aprobados' => $noAprobados,
                'total' => $aprobados + $noAprobados,
                'porcentaje_aprobacion' => ($aprobados + $noAprobados) > 0
                    ? round(($aprobados / ($aprobados + $noAprobados)) * 100, 2)
                    : 0,
            ];

            // Estadísticas de exámenes por estado (últimos 7 días)
            $examenesPorEstado = [
                'borrador' => Examen::where('estado', '0')
                    ->where('created_at', '>=', Carbon::now(config('app.timezone'))->subDays(7))
                    ->count(),
                'publicados' => Examen::where('estado', '1')
                    ->where('created_at', '>=', Carbon::now(config('app.timezone'))->subDays(7))
                    ->count(),
                'finalizados' => Examen::where('estado', '2')
                    ->where('created_at', '>=', Carbon::now(config('app.timezone'))->subDays(7))
                    ->count(),
            ];

            // Promedio de puntaje global
            $promedioPuntajeGlobal = IntentoExamen::where('estado', 'enviado')
                ->whereNotNull('puntaje')
                ->avg('puntaje') ?? 0;

            return response()->json([
                'estadisticas' => $estadisticas,
                'intentos_por_dia' => $intentosPorDia,
                'tasa_aprobacion' => $tasaAprobacion,
                'examenes_por_estado' => $examenesPorEstado,
                'promedio_puntaje_global' => round((float)$promedioPuntajeGlobal, 2),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en DashboardController@index: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al cargar las estadísticas del dashboard.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
