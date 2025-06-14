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
    // Ya no necesitas RepresentadoController aquí
};

// Importa el controlador de Representado desde su ubicación correcta
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
//  CONFIG ROUTES GROUP - Solo accesible por administradores
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
        
        // Get municipalities by state
        Route::get('/por-estado/{estadoId}', [UbicacionController::class, 'indexMunicipios']);
    });


    Route::prefix('parroquias')->group(function () {
        
        Route::post('/', [UbicacionController::class, 'storeParroquia']);
        Route::get('/{id}', [UbicacionController::class, 'showParroquia']);
        Route::put('/{id}', [UbicacionController::class, 'updateParroquia']);
        Route::delete('/{id}', [UbicacionController::class, 'destroyParroquia']);
        
        // Get parishes by municipality
        Route::get('/por-municipio/{municipioId}', [UbicacionController::class, 'indexParroquias']);
    });

 
    Route::prefix('vacunas')->group(function () {
        Route::get('/', [VacunaController::class, 'index']);
        Route::post('/', [VacunaController::class, 'store']);
        Route::get('/{id}', [VacunaController::class, 'show']);
        Route::put('/{id}', [VacunaController::class, 'update']);
        Route::delete('/{id}', [VacunaController::class, 'destroy']);
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

}); // Correct End of 'config' prefix group

// =============================================================
//  REPRESENTADO ROUTES GROUP - Accesible por usuarios con rol 'user'
// =============================================================
Route::prefix('representados')->middleware(['auth:sanctum', 'role:user'])->group(function () {
    // Estas rutas ahora serán accesibles en /api/representados
    Route::get('/', [RepresentadoController::class, 'indexUserRepresentados']);
    Route::post('/', [RepresentadoController::class, 'store']);
    Route::put('/{id}', [RepresentadoController::class, 'update']);
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

/// ejemplo de como debe ser la consulta
/* {
    "user_id": 3,  // ID del usuario al que se le asignará este representado (debe tener rol 'representante')
    "cedula": "28123456",
    "nombre_completo": "Pedro Perez hijp",
    "fecha_nacimiento": "2000-05-15",
    "sexo": "M",
    "nacionalidad": "venezolano",
    "direccion": "Calle Falsa 123, Urb. Admin, Ciudad Admin",
    "parroquia_id": null,   // ID de una parroquia existente (opcional, puede ser null)
    "grupo_riesgo_id": null, // ID de un grupo de riesgo existente (opcional, puede ser null)
    "indigena_id": null  // ID de un grupo indígena existente (opcional, puede ser null)
} */


// =============================================================
//  REGISTRO ROUTES GROUP - Accesible por Admin y Personal de Salud
// =============================================================
Route::prefix('registro')->middleware(['auth:sanctum', 'role:admin,personal_de_salud'])->group(function () {
    // Ruta para buscar por cédula (ej. /api/registro/search-cedula?cedula=V12345678)
    Route::get('/search-cedula', [RegistroController::class, 'searchByCedula'])->name('registro.search_cedula');
});