<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Services\Config\EstadoService;
use App\Services\Config\MunicipioService;
use App\Services\Config\ParroquiaService;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    // Métodos para Estados
    
    /**
     * Display a listing of estados.
     */
    public function indexEstados(EstadoService $service)
    {
        return $service->index();
    }

    /**
     * Store a newly created estado.
     */
    public function storeEstado(Request $request, EstadoService $service)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            // otras validaciones necesarias
        ]);
        
        return $service->create($validatedData);
    }

    /**
     * Display the specified estado.
     */
    public function showEstado($id, EstadoService $service)
    {
        return $service->findById($id);
    }

    /**
     * Update the specified estado.
     */
    public function updateEstado(Request $request, $id, EstadoService $service)
    {
        $estado = $service->findById($id);
        if (!$estado) {
            return response()->json(['message' => 'Estado no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            // otras validaciones necesarias
        ]);

        return $service->update($estado, $validatedData);
    }

    /**
     * Remove the specified estado.
     */
    public function destroyEstado($id, EstadoService $service)
    {
        $estado = $service->findById($id);
        if (!$estado) {
            return response()->json(['message' => 'Estado no encontrado'], 404);
        }

        return $service->delete($estado);
    }

    // Métodos para Municipios

    /**
     * Display a listing of municipios.
     */
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

    // Métodos para Parroquias

    /**
     * Display a listing of parroquias.
     */
    public function indexParroquias(ParroquiaService $service, Request $request)
    {
        if ($request->has('municipio_id')) {
            return $service->getByMunicipio($request->municipio_id);
        }
        return $service->getAll();
    }

    /**
     * Store a newly created parroquia.
     */
    public function storeParroquia(Request $request, ParroquiaService $service)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'municipio_id' => 'required|exists:municipios,id',
            // otras validaciones necesarias
        ]);
        
        return $service->create($validatedData);
    }

    /**
     * Display the specified parroquia.
     */
    public function showParroquia($id, ParroquiaService $service)
    {
        return $service->findById($id);
    }

    /**
     * Update the specified parroquia.
     */
    public function updateParroquia(Request $request, $id, ParroquiaService $service)
    {
        $parroquia = $service->findById($id);
        if (!$parroquia) {
            return response()->json(['message' => 'Parroquia no encontrada'], 404);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'municipio_id' => 'sometimes|exists:municipios,id',
            // otras validaciones necesarias
        ]);

        return $service->update($parroquia, $validatedData);
    }

    /**
     * Remove the specified parroquia.
     */
    public function destroyParroquia($id, ParroquiaService $service)
    {
        $parroquia = $service->findById($id);
        if (!$parroquia) {
            return response()->json(['message' => 'Parroquia no encontrada'], 404);
        }

        return $service->delete($parroquia);
    }
}