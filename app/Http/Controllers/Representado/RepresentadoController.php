<?php

namespace App\Http\Controllers\Representado;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Services\Representado\IndexUserRepresentadosService;
use App\Services\Representado\StoreRepresentadoService;
use App\Services\Representado\UpdateRepresentadoService;

use App\Services\Representado\IndexRepresentadosAdminService;    
use App\Services\Representado\ShowRepresentadoAdminService;      
use App\Services\Representado\StoreRepresentadoAdminService;   
use App\Services\Representado\UpdateRepresentadoAdminService;   
use App\Services\Representado\DeleteRepresentadoAdminService;  


class RepresentadoController extends Controller
{
    // =============================================================
    //  MÉTODOS PARA USUARIOS CON ROL 'REPRESENTANTE'
    //  (Protegidos por middleware 'role:representante' en las rutas)
    // =============================================================

 
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



    // =============================================================
    //  MÉTODOS PARA ADMINISTRADORES CON ROL 'ADMIN'
    //  (Protegidos por middleware 'role:admin' en las rutas)
    // =============================================================


    public function indexAllRepresentadosAdmin(Request $request, IndexRepresentadosAdminService $service): JsonResponse
    {
        return $service->execute($request);
    }


    public function showAdmin(int $id, ShowRepresentadoAdminService $service): JsonResponse
    {
        return $service->execute($id);
    }


    public function storeForUserAdmin(Request $request, StoreRepresentadoAdminService $service): JsonResponse
    {
        return $service->execute($request);
    }


    public function updateForUserAdmin(Request $request, int $representadoId, UpdateRepresentadoAdminService $service): JsonResponse
    {
        return $service->execute($request, $representadoId);
    }


    public function destroyForUserAdmin(int $representadoId, DeleteRepresentadoAdminService $service): JsonResponse
    {
        return $service->execute($representadoId);
    }
}