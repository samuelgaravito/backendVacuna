<?php

namespace App\Http\Controllers\Registro;

use App\Http\Controllers\Controller;
use App\Services\Registro\BuscarPersonaService;
use App\Services\Registro\StoreRegistroVacunaService;
use App\Services\Registro\UpdateRegistroVacunaService;
use App\Services\Registro\IndexRegistroService;
use App\Services\Registro\ShowRegistroService;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RegistroController extends Controller
{
    protected $buscarPersonaService;
    protected $storeRegistroVacunaService;
    protected $updateRegistroVacunaService; // <-- Nueva propiedad para el servicio de actualización

    /**
     * Constructor del controlador.
     * Inyecta los servicios necesarios.
     */
    public function __construct(
        BuscarPersonaService $buscarPersonaService,
        StoreRegistroVacunaService $storeRegistroVacunaService,
        UpdateRegistroVacunaService $updateRegistroVacunaService // <-- Inyección del nuevo servicio
    ) {
        $this->buscarPersonaService = $buscarPersonaService;
        $this->storeRegistroVacunaService = $storeRegistroVacunaService;
        $this->updateRegistroVacunaService = $updateRegistroVacunaService; // <-- Asigna el nuevo servicio
    }

    /**
     * Busca información de representado(s) por número de cédula.
     */
    public function searchByCedula(Request $request): JsonResponse
    {
        // ... (código existente para searchByCedula) ...
        try {
            $result = $this->buscarPersonaService->search($request);
            if ($result) {
                return response()->json([
                    'success' => true, 'type' => $result['type'], 'message' => $result['message'], 'data' => $result['data']
                ], 200);
            } else {
                return response()->json([
                    'success' => false, 'message' => 'La cédula no fue encontrada en el sistema.', 'data' => null
                ], 404);
            }
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación en la solicitud.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado al buscar la cédula: ' . $e->getMessage(),], 500);
        }
    }

    /**
     * Registra una nueva vacuna administrada a un representado.
     */
    public function store(Request $request): JsonResponse
    {
        // ... (código existente para store) ...
        try {
            return $this->storeRegistroVacunaService->execute($request);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Error de validación al registrar la vacuna.', 'errors' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Recurso no encontrado (Representado o Vacuna).', 'error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado al registrar la vacuna: ' . $e->getMessage(),], 500);
        }
    }

    /**
     * Edita un registro de vacuna existente.
     * Solo para administradores.
     *
     * @param Request $request
     * @param int $id El ID del registro a actualizar.
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Delega la lógica de actualización al servicio UpdateRegistroVacunaService
            return $this->updateRegistroVacunaService->execute($request, $id);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación al actualizar el registro.',
                'errors'  => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'El registro de vacuna especificado no fue encontrado.',
                'error'   => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al actualizar el registro: ' . $e->getMessage(),
            ], 500);
        }
    }


 public function indexRegistros(Request $request, string $fecha_inicio, string $fecha_fin, IndexRegistroService $indexRegistroService): JsonResponse
    {
        // El controlador simplemente delega y devuelve la respuesta del servicio.
        // El servicio se encarga de manejar los errores y formatear el JSON.
        $data = [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin'    => $fecha_fin,
            'per_page'     => $request->input('per_page', 15),
        ];

        return $indexRegistroService->execute($data);
    }


    public function show(int $id, ShowRegistroService $showRegistroService): JsonResponse
    {
        // El controlador simplemente delega y devuelve la respuesta del servicio.
        // El servicio se encarga de encontrar el registro y manejar los errores 404/500.
        return $showRegistroService->execute($id);
    }
}