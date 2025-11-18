<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pregunta;
use App\Models\OpcionesPregunta;
use App\Models\Categoria;
use App\Models\Contexto;
use App\Models\Examen;
use App\Models\IntentoExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PreguntaController extends Controller
{
    /**
     * Verificar si una pregunta está siendo usada en algún examen activo
     * Un examen está activo si:
     * 1. Tiene intentos con estado 'iniciado' (en progreso)
     * 2. O tiene intentos donde el temporizador no ha terminado (hora_inicio + tiempo_limite > ahora)
     *
     * @param Pregunta $pregunta
     * @return array ['puede_modificar' => bool, 'examenes_activos' => array, 'mensaje' => string]
     */
    private function verificarPreguntaEnUso(Pregunta $pregunta): array
    {
        // Obtener todos los exámenes que usan esta pregunta
        $examenesIds = DB::table('examen_pregunta')
            ->where('idPregunta', $pregunta->idPregunta)
            ->pluck('idExamen')
            ->unique()
            ->toArray();

        if (empty($examenesIds)) {
            // La pregunta no está en ningún examen, se puede modificar
            return [
                'puede_modificar' => true,
                'examenes_activos' => [],
                'mensaje' => null
            ];
        }

        $examenesActivos = [];
        $ahora = Carbon::now();

        foreach ($examenesIds as $idExamen) {
            $examen = Examen::find($idExamen);
            if (!$examen) {
                continue;
            }

            // Verificar si hay intentos activos:
            // - Estado 'iniciado' Y el temporizador no ha terminado (hora_inicio + tiempo_limite > ahora)
            // - O estado 'iniciado' (aunque el temporizador haya terminado, el intento sigue activo hasta que se envíe)
            $intentosActivos = IntentoExamen::where('idExamen', $idExamen)
                ->where('estado', 'iniciado')
                ->get();

            // Si hay intentos activos, el examen está activo
            if ($intentosActivos->isNotEmpty()) {
                $examenesActivos[] = [
                    'idExamen' => $examen->idExamen,
                    'codigo_examen' => $examen->codigo_examen,
                    'titulo' => $examen->titulo,
                    'intentos_activos' => $intentosActivos->count(),
                ];
            }
        }

        if (!empty($examenesActivos)) {
            $codigosExamenes = collect($examenesActivos)->pluck('codigo_examen')->join(', ');
            return [
                'puede_modificar' => false,
                'examenes_activos' => $examenesActivos,
                'mensaje' => "No se puede modificar la pregunta porque está siendo utilizada en examen(es) activo(s): {$codigosExamenes}. " .
                    "Una pregunta solo se puede modificar cuando: " .
                    "1) No está en ningún examen, o " .
                    "2) Todos los exámenes que la usan están finalizados (todos los intentos están completados y el temporizador ha terminado)."
            ];
        }

        // Todos los exámenes están finalizados, se puede modificar
        return [
            'puede_modificar' => true,
            'examenes_activos' => [],
            'mensaje' => null
        ];
    }
    /**
     * RF-A.3.3: CRUD de Preguntas
     * RF-A.3.5: Filtros del Banco de Preguntas
     */
    public function index(Request $request)
    {
        try {
            $query = Pregunta::with(['categoria', 'contexto', 'opciones']);

            if ($request->filled('idCategoria')) {
                $query->where('idCategoria', $request->idCategoria);
            }

            if ($request->filled('ano')) {
                $query->where('ano', $request->ano);
            }

            if ($request->filled('codigo')) {
                $query->where('codigo', 'LIKE', '%' . $request->codigo . '%');
            }

            $perPage = $request->integer('per_page', 10);
            $preguntas = $query->orderBy('codigo')->paginate($perPage);
            return response()->json($preguntas);
        } catch (\Exception $e) {
            Log::error('Error en PreguntaController@index: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Error al cargar las preguntas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    public function show($id)
    {
        $pregunta = Pregunta::with(['categoria', 'contexto', 'opciones'])
            ->where('idPregunta', $id)
            ->firstOrFail();

        return response()->json($pregunta);
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:100|unique:preguntas,codigo',
            'idCategoria' => 'required|exists:categorias,idCategoria',
            'ano' => 'required|integer',
            'idContexto' => 'nullable|exists:contextos,idContexto',
            'enunciado' => 'required|string',
            'opciones' => 'required|array|min:2|max:6',
            'opciones.*.contenido' => 'required|string',
            'opcion_correcta' => 'required|integer|min:0|max:' . (count($request->opciones) - 1),
        ]);

        DB::beginTransaction();
        try {
            $pregunta = Pregunta::create($request->only([
                'codigo', 'idCategoria', 'ano', 'idContexto', 'enunciado'
            ]));

            foreach ($request->opciones as $index => $opcion) {
                OpcionesPregunta::create([
                    'idPregunta' => $pregunta->idPregunta,
                    'contenido' => $opcion['contenido'],
                    'es_correcta' => $index == $request->opcion_correcta,
                ]);
            }

            DB::commit();
            $pregunta->load(['categoria', 'contexto', 'opciones']);
            return response()->json($pregunta, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear la pregunta', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $pregunta = Pregunta::where('idPregunta', $id)->firstOrFail();

        // Verificar si la pregunta está en uso en algún examen activo
        $verificacion = $this->verificarPreguntaEnUso($pregunta);
        if (!$verificacion['puede_modificar']) {
            return response()->json([
                'message' => $verificacion['mensaje'],
                'examenes_activos' => $verificacion['examenes_activos']
            ], 422);
        }

        $request->validate([
            'codigo' => 'required|string|max:100|unique:preguntas,codigo,' . $pregunta->idPregunta . ',idPregunta',
            'idCategoria' => 'required|exists:categorias,idCategoria',
            'ano' => 'required|integer',
            'idContexto' => 'nullable|exists:contextos,idContexto',
            'enunciado' => 'required|string',
            'opciones' => 'required|array|min:2|max:6',
            'opciones.*.contenido' => 'required|string',
            'opcion_correcta' => 'required|integer|min:0|max:' . (count($request->opciones) - 1),
        ]);

        DB::beginTransaction();
        try {
            $pregunta->update($request->only([
                'codigo', 'idCategoria', 'ano', 'idContexto', 'enunciado'
            ]));

            // Eliminar opciones existentes y crear nuevas
            $pregunta->opciones()->delete();
            foreach ($request->opciones as $index => $opcion) {
                OpcionesPregunta::create([
                    'idPregunta' => $pregunta->idPregunta,
                    'contenido' => $opcion['contenido'],
                    'es_correcta' => $index == $request->opcion_correcta,
                ]);
            }

            DB::commit();
            $pregunta->load(['categoria', 'contexto', 'opciones']);
            return response()->json($pregunta);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar la pregunta', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $pregunta = Pregunta::where('idPregunta', $id)->firstOrFail();

        if ($pregunta->examenes()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la pregunta porque está asociada a exámenes.'
            ], 422);
        }

        $pregunta->delete();
        return response()->json(null, 204);
    }
}
