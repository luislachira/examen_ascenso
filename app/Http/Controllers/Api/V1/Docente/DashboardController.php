<?php

namespace App\Http\Controllers\Api\V1\Docente;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use App\Http\Resources\ExamenResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * RF-D.1.2: Ver Exámenes Disponibles
     * Muestra solo exámenes que cumplan:
     * - estado = "1" (Publicado) Y
     * - La hora actual del servidor está entre fecha_inicio_vigencia y fecha_fin_vigencia. Y
     * - (tipo_acceso = "publico" O el idUsuario del docente existe en ExamenesUsuario para ese idExamen)
     */
    public function index()
    {
        $usuario = Auth::user();
        // Usar la zona horaria de la aplicación para comparar con las fechas guardadas
        $ahora = Carbon::now(config('app.timezone'));
        $idUsuario = $usuario->idUsuario;

        // Obtener IDs de exámenes asignados al usuario (exámenes privados)
        $examenesAsignados = DB::table('examenes_usuarios')
            ->where('idUsuario', $idUsuario)
            ->pluck('idExamen')
            ->toArray();

        // Query base: solo exámenes publicados (estado = '1')
        $query = Examen::where('estado', '1');

        // Filtrar por tipo de acceso:
        // - Si es público: todos los docentes pueden verlo
        // - Si es privado: solo los docentes asignados pueden verlo
        $query->where(function($q) use ($examenesAsignados) {
            // Exámenes públicos (visibles para todos)
            $q->where('tipo_acceso', 'publico');

            // O exámenes privados asignados al usuario
            if (!empty($examenesAsignados)) {
                $q->orWhere(function($subQ) use ($examenesAsignados) {
                    $subQ->where('tipo_acceso', 'privado')
                         ->whereIn('idExamen', $examenesAsignados);
                });
            }
        });

        // RF-D.1.2: Filtrar por fechas de vigencia
        // La hora actual del servidor (now()) está entre fecha_inicio_vigencia y fecha_fin_vigencia
        $query->where(function($q) use ($ahora) {
            $q->where(function($dateQ) use ($ahora) {
                // Si no tiene fecha_inicio_vigencia, está disponible
                // Si tiene fecha_inicio_vigencia, debe haber comenzado
                $dateQ->whereNull('fecha_inicio_vigencia')
                      ->orWhereRaw('fecha_inicio_vigencia <= ?', [$ahora->format('Y-m-d H:i:s')]);
            })
            ->where(function($dateQ) use ($ahora) {
                // Si no tiene fecha_fin_vigencia, no tiene límite
                // Si tiene fecha_fin_vigencia, no debe haber finalizado (>= permite ver hasta el último momento)
                $dateQ->whereNull('fecha_fin_vigencia')
                      ->orWhereRaw('fecha_fin_vigencia >= ?', [$ahora->format('Y-m-d H:i:s')]);
            });
        });

        // Obtener exámenes ordenados por fecha de inicio de vigencia (más recientes primero)
        // Cargar relación de preguntas y contar preguntas para mostrar total_preguntas
        $examenes = $query->with('tipoConcurso')
            ->withCount('preguntas')
            ->orderByRaw('COALESCE(fecha_inicio_vigencia, created_at) DESC')
            ->get();

        // Usar ExamenResource para transformar los datos
        return ExamenResource::collection($examenes);
    }
}
