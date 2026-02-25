<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EncuestaController extends Controller
{
    public function bienvenida()
    {
        return view('encuesta.bienvenida');
    }

    public function acceso(Request $request)
    {
        // Stub — lógica en issue #12
        return back();
    }

    public function demograficos(string $token)
    {
        // Stub — lógica pendiente
        return view('encuesta.bienvenida'); // temporal, solo para que no explote
    }

    public function guardarDemograficos(Request $request, string $token)
    {
        // Stub — lógica pendiente
        return back();
    }
}
