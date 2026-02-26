<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EncuestaController extends Controller
{
    public function bienvenida()
    {
        return view('encuesta.bienvenida');
    }

    public function acceso(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $empresa = Empresa::where('activa', true)
            ->get()
            ->first(fn ($e) => Hash::check($request->password, $e->password));

        if (! $empresa) {
            return back()->withErrors(['password' => 'Contraseña incorrecta.']);
        }

        $encuesta = Encuesta::where('empresa_id', $empresa->id)
            ->where('estado', 'disponible')
            ->first();

        if (! $encuesta) {
            return back()->withErrors(['password' => 'No hay tokens disponibles. Contacta al administrador.']);
        }

        $encuesta->asignar();

        return view('encuesta.token-asignado', compact('encuesta'));
    }

    public function demograficos(string $token)
    {
        Encuesta::whereIn('estado', ['asignado', 'en_progreso'])
            ->where('token', $token)
            ->firstOrFail();

        return view('encuesta.demografico', compact('token'));
    }
}
