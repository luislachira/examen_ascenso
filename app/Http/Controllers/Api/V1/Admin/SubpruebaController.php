<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use App\Models\Subprueba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubpruebaController extends Controller
{
    /**
     * Verificar si el examen tiene intentos iniciados
     */
    private function verificarSinIntentos(Examen $examen): void
    {
        if ($examen->intentos()->exists()) {
            $cantidadIntentos = $examen->intentos()->count();
            throw new \Exception(
                "No se puede modificar el examen porque ya hay {$cantidadIntentos} intento(s) iniciado(s) por docente(s) o participante(s). " .
                "Una vez que alguien ha comenzado a tomar el examen, no se pueden realizar modificaciones."
            );
        }
    }

    /**
     * Verificar si el examen está finalizado (estado = '2')
     * Si está finalizado, lanzar una excepción
     */
    private function verificarExamenNoFinalizado(Examen $examen): void
    {
        if ($examen->estado === '2') {
            throw new \Exception(
                'No se puede modificar un examen finalizado. Solo se puede ver su configuración, duplicarlo o eliminarlo.'
            );
        }
    }

    /**
     * RF-A.4.3: CRUD de Subpruebas
     * Obtener todas las subpruebas de un examen
     */
    public function index(Examen $examen)
    {
        $subpruebas = Subprueba::where('idExamen', $examen->idExamen)
            ->orderBy('orden')
            ->get();

        return response()->json($subpruebas);
    }

    /**
     * RF-A.4.3: Crear una nueva subprueba
     */
    public function store(Request $request, Examen $examen)
    {
        $request->validate([
            'nombre' => 'required|string|min:5|max:100',
            'orden' => 'required|integer|min:1',
            'puntaje_por_pregunta' => 'nullable|numeric|min:0|max:10',
            'duracion_minutos' => 'nullable|integer|min:0',
        ]);

        // Verificar que no haya otra subprueba con el mismo orden
        $ordenExistente = Subprueba::where('idExamen', $examen->idExamen)
            ->where('orden', $request->orden)
            ->exists();

        if ($ordenExistente) {
            return response()->json([
                'message' => "Ya existe una subprueba con el orden {$request->orden}",
            ], 422);
        }

        // Verificar que no haya intentos iniciados
        try {
            $this->verificarSinIntentos($examen);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $subprueba = Subprueba::create([
            'idExamen' => $examen->idExamen,
            'nombre' => $request->nombre,
            'puntaje_por_pregunta' => $request->puntaje_por_pregunta,
            'duracion_minutos' => $request->duracion_minutos,
            'orden' => $request->orden,
        ]);

        return response()->json($subprueba, 201);
    }

    /**
     * RF-A.4.3: Actualizar una subprueba
     */
    public function update(Request $request, Subprueba $subprueba)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'puntaje_por_pregunta' => 'required|numeric|min:0|max:10',
            'duracion_minutos' => 'required|integer|min:1',
            'orden' => 'required|integer|min:1',
        ]);

        // Verificar que el examen no esté finalizado
        $examen = $subprueba->examen;
        try {
            $this->verificarExamenNoFinalizado($examen);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // Verificar que no haya intentos iniciados
        try {
            $this->verificarSinIntentos($examen);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $subprueba->update($request->only(['nombre', 'puntaje_por_pregunta', 'duracion_minutos', 'orden']));

        return response()->json($subprueba);
    }

    /**
     * RF-A.4.3: Eliminar una subprueba
     */
    public function destroy(Subprueba $subprueba)
    {
        // Verificar que el examen no esté finalizado
        $examen = $subprueba->examen;
        try {
            $this->verificarExamenNoFinalizado($examen);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // Verificar que no haya intentos iniciados
        try {
            $this->verificarSinIntentos($examen);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // Verificar si tiene preguntas asignadas
        $tienePreguntas = DB::table('examen_pregunta')
            ->where('idSubprueba', $subprueba->idSubprueba)
            ->exists();

        // Si el examen está en borrador (estado '0'), permitir eliminar la subprueba junto con sus preguntas
        if ($tienePreguntas) {
            if ($examen->estado === '0') {
                // Eliminar todas las preguntas asociadas a esta subprueba
                DB::table('examen_pregunta')
                    ->where('idSubprueba', $subprueba->idSubprueba)
                    ->where('idExamen', $examen->idExamen)
                    ->delete();
            } else {
                // Si el examen no está en borrador, no permitir eliminar
                return response()->json([
                    'message' => 'No se puede eliminar la subprueba porque tiene preguntas asignadas. Solo se pueden eliminar subpruebas con preguntas cuando el examen está en estado Borrador.'
                ], 422);
            }
        }

        $subprueba->delete();
        return response()->json(null, 204);
    }
}

