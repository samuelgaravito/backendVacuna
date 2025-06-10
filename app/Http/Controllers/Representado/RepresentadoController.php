<?php

namespace App\Http\Controllers\Representado;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Services\Representado\IndexUserRepresentadosService; // ¡Importa el nuevo servicio!

use App\Services\Representado\StoreRepresentadoService;
use App\Services\Representado\UpdateRepresentadoService;

class RepresentadoController extends Controller
{


    public function indexUserRepresentados(Request $request, IndexUserRepresentadosService $service): JsonResponse
    {
        return $service->execute($request);
    }

    public function store(Request $request, StoreRepresentadoService $service): JsonResponse
    {
        return $service->execute($request);
    }


    public function update(Request $request, int $id, UpdateRepresentadoService $service): JsonResponse
    {
        return $service->execute($request, $id);
    }
}