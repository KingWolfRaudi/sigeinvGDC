<?php

namespace App\Observers;

use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TrabajadorObserver
{
    /**
     * Se dispara cuando un Trabajador ha sido creado.
     */
    public function created(Trabajador $trabajador): void
    {
        $user = User::create([
            'name' => $trabajador->nombres . ' ' . $trabajador->apellidos,
            'email' => $trabajador->cedula . '@sistema.local', 
            'password' => Hash::make($trabajador->cedula),
            'activo' => $trabajador->activo, // Guardamos el estado inicial
        ]);

        // Buscamos y asignamos el rol "Trabajador"
        $role = Role::where('name', 'Trabajador')->first();
        if ($role) {
            $user->assignRole($role);
        }
    }

    /**
     * Se dispara cuando un Trabajador ha sido actualizado.
     */
    public function updated(Trabajador $trabajador): void
    {
        $user = User::where('email', $trabajador->cedula . '@sistema.local')->first();

        if ($user) {
            if ($trabajador->isDirty(['nombres', 'apellidos'])) {
                $user->name = $trabajador->nombres . ' ' . $trabajador->apellidos;
            }

            if ($trabajador->isDirty('activo')) {
                $user->activo = $trabajador->activo; 
            }

            $user->save();
        }
    }

    /**
     * Se dispara si eliminas permanentemente a un trabajador
     */
    public function deleted(Trabajador $trabajador): void
    {
        $user = User::where('email', $trabajador->cedula . '@sistema.local')->first();
        if ($user) {
            $user->delete();
        }
    }
}