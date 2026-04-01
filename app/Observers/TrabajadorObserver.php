<?php

namespace App\Observers;

use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Support\Str;

class TrabajadorObserver
{
    public function created(Trabajador $trabajador)
    {
        // 1. Limpiamos nombres y apellidos (quitar espacios y a minúsculas)
        $nombres = Str::slug($trabajador->nombres, '');
        $apellidos = Str::slug($trabajador->apellidos, '');
        
        // 2. Obtenemos el dominio del .env
        $dominio = env('DOMINIO_ORGANIZACION', '@organizacion.com');

        // 3. Generamos el correo: nombres + apellidos + # + id + dominio
        $emailGenerado = $nombres . $apellidos . '#' . $trabajador->id . $dominio;

        // 4. Creamos el usuario vinculado
        $user = User::create([
            'name'     => $trabajador->nombres . ' ' . $trabajador->apellidos,
            'email'    => $emailGenerado,
            'password' => bcrypt('12345678'), // Contraseña por defecto inicial
            'activo'   => true, 
        ]);

        // 5. Asignamos el rol base (ajusta el nombre del rol según tu seeder)
        $user->assignRole('Trabajador');

        // 6. Vinculamos el usuario al trabajador y guardamos sin disparar el observer de nuevo
        $trabajador->user_id = $user->id;
        $trabajador->saveQuietly();
    }

    public function updated(Trabajador $trabajador)
    {
        // Si cambia el estado activo del trabajador, sincronizamos con el usuario
        if ($trabajador->user) {
            $trabajador->user->update([
                'active' => $trabajador->activo
            ]);
        }
    }
}