<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Services\Config\GrupoRiesgoService;
use Illuminate\Http\Request;

class GrupoRiesgoController extends Controller
{
    protected $grupoRiesgoService;

    public function __construct(GrupoRiesgoService $grupoRiesgoService)
    {
        $this->grupoRiesgoService = $grupoRiesgoService;
    }

    public function index(Request $request)
    {
        if ($request->has('search')) {
            return $this->grupoRiesgoService->search($request->search);
        }
        return $this->grupoRiesgoService->getAll();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:255'
        ]);

        return $this->grupoRiesgoService->create($validated);
    }

    public function show(int $id)
    {
        $grupo = $this->grupoRiesgoService->findById($id);
        
        return $grupo ?? response()->json([
            'message' => 'Grupo de riesgo no encontrado'
        ], 404);
    }

    public function update(Request $request, int $id)
    {
        $grupo = $this->grupoRiesgoService->findById($id);
        
        if (!$grupo) {
            return response()->json([
                'message' => 'Grupo de riesgo no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'tipo' => 'sometimes|string|max:255'
        ]);

        return $this->grupoRiesgoService->update($grupo, $validated);
    }

    public function destroy(int $id)
    {
        $grupo = $this->grupoRiesgoService->findById($id);
        
        if (!$grupo) {
            return response()->json([
                'message' => 'Grupo de riesgo no encontrado'
            ], 404);
        }

        $this->grupoRiesgoService->delete($grupo);

        return response()->json([
            'message' => 'Grupo de riesgo eliminado correctamente'
        ]);
    }
}