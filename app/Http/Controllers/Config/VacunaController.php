<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Services\Config\VacunaService;
use Illuminate\Http\Request;

class VacunaController extends Controller
{
    protected $vacunaService;

    public function __construct(VacunaService $vacunaService)
    {
        $this->vacunaService = $vacunaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('search')) {
            return $this->vacunaService->search($request->search);
        }
        return $this->vacunaService->getAll();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'cantidad' => 'required|integer|min:0'
        ]);

        return $this->vacunaService->create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $vacuna = $this->vacunaService->findById($id);
        
        if (!$vacuna) {
            return response()->json(['message' => 'Vacuna no encontrada'], 404);
        }

        return $vacuna;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $vacuna = $this->vacunaService->findById($id);
        
        if (!$vacuna) {
            return response()->json(['message' => 'Vacuna no encontrada'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'cantidad' => 'sometimes|integer|min:0'
        ]);

        return $this->vacunaService->update($vacuna, $validated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $vacuna = $this->vacunaService->findById($id);
        
        if (!$vacuna) {
            return response()->json(['message' => 'Vacuna no encontrada'], 404);
        }

        $this->vacunaService->delete($vacuna);

        return response()->json(['message' => 'Vacuna eliminada correctamente']);
    }
}