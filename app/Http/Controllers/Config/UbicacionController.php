<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Services\Config\EstadoService;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(EstadoService $service)
    {
        return $service->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, EstadoService $service)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            // añade aquí otras validaciones necesarias
        ]);
        
        return $service->create($validatedData);
    }

    /**
     * Display the specified resource.
     */
    public function show($id, EstadoService $service)
    {
        return $service->findById($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, EstadoService $service)
    {
        $estado = $service->findById($id);
        if (!$estado) {
            return response()->json(['message' => 'Estado no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            // añade aquí otras validaciones necesarias
        ]);

        return $service->update($estado, $validatedData);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, EstadoService $service)
    {
        $estado = $service->findById($id);
        if (!$estado) {
            return response()->json(['message' => 'Estado no encontrado'], 404);
        }

        return $service->delete($estado);
    }

    public function indexMunicipios(MunicipioService $service, Request $request)
    {
        if ($request->has('estado_id')) {
            return $service->getByEstado($request->estado_id);
        }
        return $service->getAll();
    }

    /**
     * Store a newly created municipio.
     */
    public function storeMunicipio(Request $request, MunicipioService $service)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'estado_id' => 'required|exists:estados,id',
            // otras validaciones necesarias
        ]);
        
        return $service->create($validatedData);
    }

    /**
     * Display the specified municipio.
     */
    public function showMunicipio($id, MunicipioService $service)
    {
        return $service->findById($id);
    }

    /**
     * Update the specified municipio.
     */
    public function updateMunicipio(Request $request, $id, MunicipioService $service)
    {
        $municipio = $service->findById($id);
        if (!$municipio) {
            return response()->json(['message' => 'Municipio no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'estado_id' => 'sometimes|exists:estados,id',
            // otras validaciones necesarias
        ]);

        return $service->update($municipio, $validatedData);
    }

    /**
     * Remove the specified municipio.
     */
    public function destroyMunicipio($id, MunicipioService $service)
    {
        $municipio = $service->findById($id);
        if (!$municipio) {
            return response()->json(['message' => 'Municipio no encontrado'], 404);
        }

        return $service->delete($municipio);
    }

    /**
     * Get municipios by estado.
     */
    public function municipiosByEstado($estadoId, MunicipioService $service)
    {
        return $service->getByEstado($estadoId);
    }
}