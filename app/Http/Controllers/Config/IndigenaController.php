<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Services\Config\IndigenaService;
use Illuminate\Http\Request;

class IndigenaController extends Controller
{
    protected $indigenaService;

    public function __construct(IndigenaService $indigenaService)
    {
        $this->indigenaService = $indigenaService;
    }

    public function index(Request $request)
    {
        if ($request->has('search')) {
            return $this->indigenaService->search($request->search);
        }
        return $this->indigenaService->getAll();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:255'
        ]);

        return $this->indigenaService->create($validated);
    }

    public function show(int $id)
    {
        $indigena = $this->indigenaService->findById($id);
        
        return $indigena ?? response()->json([
            'message' => 'Registro indígena no encontrado'
        ], 404);
    }

    public function update(Request $request, int $id)
    {
        $indigena = $this->indigenaService->findById($id);
        
        if (!$indigena) {
            return response()->json([
                'message' => 'Registro indígena no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'tipo' => 'sometimes|string|max:255'
        ]);

        return $this->indigenaService->update($indigena, $validated);
    }

    public function destroy(int $id)
    {
        $indigena = $this->indigenaService->findById($id);
        
        if (!$indigena) {
            return response()->json([
                'message' => 'Registro indígena no encontrado'
            ], 404);
        }

        $this->indigenaService->delete($indigena);

        return response()->json([
            'message' => 'Registro indígena eliminado correctamente'
        ]);
    }
}