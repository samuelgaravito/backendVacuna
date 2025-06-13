<?php

namespace App\Services\Registro;

use App\Models\User;
use App\Models\Representado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request; // Importa Request
use Illuminate\Validation\ValidationException; // Importa ValidationException

class BuscarPersonaService
{
    /**
     * Busca información por cédula, priorizando Usuarios y luego Representados.
     * Incluye la validación de la cédula dentro del servicio.
     *
     * @param Request $request La solicitud HTTP que contiene la cédula.
     * @return array|null Un array con 'type' y 'data' si se encuentra, o null si no se encuentra.
     * @throws ValidationException Si la cédula no es válida o no está presente.
     */
    public function search(Request $request): ?array // Ahora recibe el Request
    {
        // Realiza la validación de la cédula aquí
        $validatedData = $request->validate([
            'cedula' => 'required|string|max:20', // La validación se movió aquí
        ], [
            'cedula.required' => 'La cédula es obligatoria para realizar la búsqueda.',
            'cedula.string'   => 'La cédula debe ser una cadena de texto.',
            'cedula.max'      => 'La cédula no puede exceder los :max caracteres.',
        ]);

        $cedula = $validatedData['cedula'];

        // 1. Intentar buscar un usuario por esta cédula
        $user = User::where('cedula', $cedula)->first();

        if ($user) {
            $representados = $user->representados()->with([
                'parroquia.municipio.estado',
                'grupoRiesgo',
                'indigena'
            ])->get();

            return [
                'type'    => 'user_representados',
                'message' => 'Cédula de usuario encontrada. Se muestran todos sus representados.',
                'data'    => $representados,
            ];
        }

        // 2. Si no se encuentra un usuario, intentar buscar un representado por esta cédula
        $representado = Representado::where('cedula', $cedula)->first();

        if ($representado) {
            $representado->load([
                'user',
                'parroquia.municipio.estado',
                'grupoRiesgo',
                'indigena'
            ]);

            return [
                'type'    => 'single_representado',
                'message' => 'Cédula de representado encontrada.',
                'data'    => $representado,
            ];
        }

        // 3. Si no se encuentra ni usuario ni representado con esa cédula
        return null;
    }
}