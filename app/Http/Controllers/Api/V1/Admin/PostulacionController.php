<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use App\Models\Postulacion;
use Illuminate\Http\Request;

class PostulacionController extends Controller
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
     * RF-A.9.1: Listar todas las postulaciones de un examen
     */
    public function index(Examen $examen)
    {
        $postulaciones = Postulacion::where('idExamen', $examen->idExamen)
            ->orderBy('nombre')
            ->get();

        return response()->json($postulaciones);
    }

    /**
     * RF-A.9.1: Crear una nueva postulación
     */
    public function store(Request $request, Examen $examen)
    {
        \Illuminate\Support\Facades\Log::info('PostulacionController@store - Iniciando creación', [
            'examen_id' => $examen->idExamen,
            'examen_estado' => $examen->estado,
            'request_data' => $request->all(),
        ]);

        try {
            $validated = $request->validate([
                'nombre' => 'required|string|min:5|max:100',
                'descripcion' => 'nullable|string|max:500',
            ]);
            \Illuminate\Support\Facades\Log::info('PostulacionController@store - Validación exitosa', [
                'validated_data' => $validated,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('PostulacionController@store - Error de validación', [
                'errors' => $e->errors(),
                'request_all' => $request->all(),
                'request_input_nombre' => $request->input('nombre'),
                'request_input_descripcion' => $request->input('descripcion'),
                'request_json' => $request->json()->all(),
            ]);
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        }

        // Verificar que no haya otra postulación con el mismo nombre en este examen
        $nombreExistente = Postulacion::where('idExamen', $examen->idExamen)
            ->where('nombre', $request->nombre)
            ->exists();

        if ($nombreExistente) {
            \Illuminate\Support\Facades\Log::warning('PostulacionController@store - Nombre duplicado', [
                'nombre' => $request->nombre,
                'examen_id' => $examen->idExamen,
            ]);
            return response()->json([
                'message' => "Ya existe una postulación con el nombre '{$request->nombre}' en este examen",
            ], 422);
        }

        // Verificar que el examen esté en borrador
        // Si el estado es null, asumimos que es borrador (examen recién creado)
        // Convertir a string para comparación segura (puede venir como '0', 0, null, etc.)
        $estadoRaw = $examen->estado;
        $estadoExamen = $estadoRaw !== null ? (string) $estadoRaw : '0';

        \Illuminate\Support\Facades\Log::info('PostulacionController@store - Verificando estado del examen', [
            'examen_id' => $examen->idExamen,
            'estado_raw' => $estadoRaw,
            'estado_raw_type' => gettype($estadoRaw),
            'estadoExamen' => $estadoExamen,
            'estadoExamen_type' => gettype($estadoExamen),
            'comparacion' => $estadoExamen !== '0',
        ]);

        if ($estadoExamen !== '0') {
            \Illuminate\Support\Facades\Log::warning('PostulacionController@store - Examen no en borrador', [
                'examen_id' => $examen->idExamen,
                'estado_raw' => $estadoRaw,
                'estado_raw_type' => gettype($estadoRaw),
                'estadoExamen' => $estadoExamen,
                'estadoExamen_type' => gettype($estadoExamen),
            ]);
            return response()->json([
                'message' => 'Solo se pueden crear postulaciones en exámenes en estado Borrador'
            ], 422);
        }

        // Verificar que no haya intentos iniciados
        try {
            $this->verificarSinIntentos($examen);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('PostulacionController@store - Intentos iniciados', [
                'examen_id' => $examen->idExamen,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        try {
            $postulacion = Postulacion::create([
                'idExamen' => $examen->idExamen,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            \Illuminate\Support\Facades\Log::info('PostulacionController@store - Postulación creada exitosamente', [
                'postulacion_id' => $postulacion->idPostulacion,
                'nombre' => $postulacion->nombre,
            ]);

            return response()->json($postulacion, 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PostulacionController@store - Error al crear postulación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Error al crear la postulación: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * RF-A.9.1: Actualizar una postulación
     */
    public function update(Request $request, Postulacion $postulacion)
    {
        $request->validate([
            'nombre' => 'required|string|min:5|max:100',
            'descripcion' => 'nullable|string|max:500',
        ]);

        // Verificar que no haya otra postulación con el mismo nombre (excepto la actual)
        $nombreExistente = Postulacion::where('idExamen', $postulacion->idExamen)
            ->where('nombre', $request->nombre)
            ->where('idPostulacion', '!=', $postulacion->idPostulacion)
            ->exists();

        if ($nombreExistente) {
            return response()->json([
                'message' => "Ya existe una postulación con el nombre '{$request->nombre}' en este examen",
            ], 422);
        }

        $examen = $postulacion->examen;

        // Verificar que el examen no esté finalizado
        try {
            $this->verificarExamenNoFinalizado($examen);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // Verificar que el examen esté en borrador
        if ($examen->estado !== '0') {
            return response()->json([
                'message' => 'Solo se pueden modificar postulaciones en exámenes en estado Borrador'
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

        $postulacion->update($request->only(['nombre', 'descripcion']));

        return response()->json($postulacion);
    }

    /**
     * RF-A.9.1: Eliminar una postulación
     */
    public function destroy(Postulacion $postulacion)
    {
        $examen = $postulacion->examen;

        // Verificar que el examen no esté finalizado
        try {
            $this->verificarExamenNoFinalizado($examen);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // Verificar que el examen esté en borrador
        if ($examen->estado !== '0') {
            return response()->json([
                'message' => 'Solo se pueden eliminar postulaciones en exámenes en estado Borrador'
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

        // Verificar si tiene reglas de puntaje asociadas
        if ($postulacion->reglasPuntaje()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la postulación porque tiene reglas de puntaje asociadas'
            ], 422);
        }

        $postulacion->delete();
        return response()->json(null, 204);
    }
}
