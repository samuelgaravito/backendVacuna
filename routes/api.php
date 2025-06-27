<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    AuthenticatedSessionController,
    RegisteredUserController,
    RegisteredUserAdminController,
    PasswordResetLinkController,
    NewPasswordController,
    UserAdminController,
    UserProfileController
};

use App\Http\Controllers\Config\{
    UbicacionController,
    VacunaController,
    IndigenaController,
    GrupoRiesgoController
};

use App\Http\Controllers\Representado\RepresentadoController;
use App\Http\Controllers\Registro\RegistroController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// =============================================================
//  AUTH ROUTES GROUP
//  All routes will be under '/api/auth' prefix
// =============================================================
Route::prefix('auth')->group(function () {

    // --- Public Routes (No authentication required) ---
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('auth.register');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.login');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.forgot');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.reset');
    // Las rutas de Config (Indígenas, Grupos de Riesgo, Ubicación) son públicas en este grupo inicial
    Route::get('/indigenas', [IndigenaController::class, 'index']);
    Route::get('/grupo-riesgo', [GrupoRiesgoController::class, 'index']);
    Route::get('/estado', [UbicacionController::class, 'indexEstados']);
    Route::get('/municipio', [UbicacionController::class, 'indexMunicipios']);
    Route::get('/parroquia', [UbicacionController::class, 'indexParroquias']);

    // --- Protected Routes (Require Sanctum authentication) ---
    Route::middleware('auth:sanctum')->group(function () {

        // Authenticated User Session Management
        Route::get('/user', [AuthenticatedSessionController::class, 'show'])->name('auth.user');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');

        // Authentication Check Endpoint
        Route::get('/check', function (Request $request) {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'authenticated' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            return response()->json([
                'authenticated' => true,
                'message' => 'Usuario autenticado',
                'user' => $user->only(['id', 'name', 'email', 'cedula']),
                'roles' => $user->roles->pluck('name'),
                'is_admin' => $user->hasRole('admin'),
                'is_medico' => $user->hasRole('personal_de_salud'),
                'is_paciente' => $user->hasRole('representante')
            ]);
        })->name('auth.check');

        // User Profile Routes
        Route::prefix('profile')->group(function () {
            Route::get('/', [UserProfileController::class, 'show'])->name('profile.show');
            Route::put('/', [UserProfileController::class, 'update'])->name('profile.update');
            Route::delete('/', [UserProfileController::class, 'destroy'])->name('profile.delete');
        });

        // Admin Routes
        Route::middleware('role:admin')->group(function () {
            Route::post('/register-admin', [RegisteredUserAdminController::class, 'store'])->name('admin.register');
            Route::get('/users', [UserAdminController::class, 'index']);
            Route::get('/users/{id}', [UserAdminController::class, 'show']);
            Route::put('/users/{id}', [UserAdminController::class, 'update']);
        });

    }); // End of 'auth:sanctum' middleware group

}); // End of 'auth' prefix group

// =============================================================
//  CONFIG ROUTES GROUP - Solo accesible por administradores (gestión de datos maestros)
// =============================================================
Route::prefix('config')->middleware(['auth:sanctum', 'role:admin'])->group(function () {

    Route::prefix('estados')->group(function () {
        Route::post('/', [UbicacionController::class, 'storeEstado']);
        Route::get('/{id}', [UbicacionController::class, 'showEstado']);
        Route::put('/{id}', [UbicacionController::class, 'updateEstado']);
        Route::delete('/{id}', [UbicacionController::class, 'destroyEstado']);
    });

    Route::prefix('municipios')->group(function () {
        Route::post('/', [UbicacionController::class, 'storeMunicipio']);
        Route::get('/{id}', [UbicacionController::class, 'showMunicipio']);
        Route::put('/{id}', [UbicacionController::class, 'updateMunicipio']);
        Route::delete('/{id}', [UbicacionController::class, 'destroyMunicipio']);
        Route::get('/por-estado/{estadoId}', [UbicacionController::class, 'indexMunicipios']);
    });

    Route::prefix('parroquias')->group(function () {
        Route::post('/', [UbicacionController::class, 'storeParroquia']);
        Route::get('/{id}', [UbicacionController::class, 'showParroquia']);
        Route::put('/{id}', [UbicacionController::class, 'updateParroquia']);
        Route::delete('/{id}', [UbicacionController::class, 'destroyParroquia']);
        Route::get('/por-municipio/{municipioId}', [UbicacionController::class, 'indexParroquias']);
    });

    Route::prefix('vacunas')->group(function () {
        // La ruta GET / se ha movido a un nuevo grupo para permitir más roles
        Route::post('/', [VacunaController::class, 'store']); // Solo admin puede crear
        Route::get('/{id}', [VacunaController::class, 'show']); // Solo admin puede ver una vacuna específica
        Route::put('/{id}', [VacunaController::class, 'update']); // Solo admin puede actualizar
        Route::delete('/{id}', [VacunaController::class, 'destroy']); // Solo admin puede eliminar
    });

    Route::prefix('indigenas')->group(function () {
        Route::post('/', [IndigenaController::class, 'store']);
        Route::get('/{id}', [IndigenaController::class, 'show']);
        Route::put('/{id}', [IndigenaController::class, 'update']);
        Route::delete('/{id}', [IndigenaController::class, 'destroy']);
    });

    Route::prefix('grupos-riesgo')->group(function () {
        Route::post('/', [GrupoRiesgoController::class, 'store']);
        Route::get('/{id}', [GrupoRiesgoController::class, 'show']);
        Route::put('/{id}', [GrupoRiesgoController::class, 'update']);
        Route::delete('/{id}', [GrupoRiesgoController::class, 'destroy']);
    });

});

// =============================================================
//  REPRESENTADO ROUTES GROUP - Accesible por usuarios con rol 'representante'
// =============================================================
Route::prefix('representados')->middleware(['auth:sanctum', 'role:representante'])->group(function () {
    Route::get('/', [RepresentadoController::class, 'indexUserRepresentados']);
    Route::post('/', [RepresentadoController::class, 'store']);
    Route::put('/{id}', [RepresentadoController::class, 'update']);
    Route::get('/{id}', [RepresentadoController::class, 'show']);
});


// =============================================================
//  ADMIN REPRESENTADO ROUTES GROUP - Accesible solo por administradores
// =============================================================
Route::prefix('admin/representados')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [RepresentadoController::class, 'indexAllRepresentadosAdmin']);
    Route::get('/{id}', [RepresentadoController::class, 'showAdmin']);
    Route::post('/', [RepresentadoController::class, 'storeForUserAdmin']);
    Route::put('/{representadoId}', [RepresentadoController::class, 'updateForUserAdmin']);
    Route::delete('/{representadoId}', [RepresentadoController::class, 'destroyForUserAdmin']);
});


// =============================================================
//  REGISTRO ROUTES GROUP - Accesible por Admin y Personal de Salud
// =============================================================
Route::prefix('registro')->middleware(['auth:sanctum', 'role:admin,personal_de_salud'])->group(function () {
    Route::get('/search-cedula', [RegistroController::class, 'searchByCedula']);
    Route::post('/', [RegistroController::class, 'store']);
    Route::get('/{registroId}/seguimientos', [RegistroController::class, 'showSeguimientos']);
});


// =============================================================
//  REGISTRO ROUTES GROUP - Accesible por Admin para ver resultado total de las vacunas y a detalle de cada una
// =============================================================
Route::prefix('admin/registro')->middleware(['auth:sanctum', 'role:admin'])->group(function () {

    Route::get('/{fecha_inicio}/{fecha_fin}', [RegistroController::class, 'indexRegistros']);
    Route::get('/{id}', [RegistroController::class, 'show']);
    Route::get('/estadisticas/{fecha_inicio}/{fecha_fin}', [RegistroController::class, 'estadisticasVacunas']);

});




// =============================================================
//  ADMIN REGISTRO ROUTES GROUP - Accesible SOLO por Admin para Edición
// =============================================================
Route::prefix('admin/registros')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::put('/{id}', [RegistroController::class, 'update']);
});


// =============================================================
//  ACCESO A DATOS DE REPRESENTADOS (Tarjeta de Vacunación)
//  Accesible por Representante, Admin y Personal de Salud
// =============================================================
Route::prefix('tarjeta-vacunacion')->middleware(['auth:sanctum', 'role:representante,admin,personal_de_salud'])->group(function () {
    Route::get('/{id}', [RepresentadoController::class, 'tarjetaVacunacion']);
});


// =============================================================
//  VACUNAS (VISUALIZACIÓN) - Accesible por Admin y Personal de Salud
// =============================================================
Route::prefix('vacunas')->middleware(['auth:sanctum', 'role:admin,personal_de_salud'])->group(function () {
    // Esta ruta permite a Admin y Personal de Salud ver todas las vacunas
    Route::get('/', [VacunaController::class, 'index']);

});


// =============================================================
//  PERSONAL DE SALUD REGISTROS GROUP - SOLO ACCESIBLE POR PERSONAL DE SALUD
// =============================================================
Route::prefix('mis-registros')->middleware(['auth:sanctum', 'role:personal_de_salud'])->group(function () {

// New route with date parameters
Route::get('/{fecha_desde}/{fecha_hasta}', [RegistroController::class, 'misRegistros']);

});