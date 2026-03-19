<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;

Route::redirect('/', 'encuesta');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::prefix('admin')
    ->middleware(['auth', 'role:super_admin,admin_empresa'])
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
             ->name('dashboard');

        Route::get('/encuestas/exportar', [ExportController::class, 'encuestasCSV'])
             ->name('encuestas.exportar');

        Route::get('/encuestas', \App\Livewire\Admin\EncuestasTable::class)
            ->name('encuestas');

        Route::get('/tokens', \App\Livewire\Admin\GenerarTokens::class)
             ->name('tokens');

        Route::get('/reportes', \App\Livewire\Admin\Reportes::class)
             ->name('reportes');

        // Solo super_admin
        Route::middleware('role:super_admin')->group(function () {
            // Aquí irán las rutas exclusivas de super_admin en sprints futuros
        });
    });

require __DIR__.'/auth.php';

// Sprint 2 - Rutas públicas de encuesta
Route::get('/encuesta', [EncuestaController::class, 'bienvenida'])->name('encuesta.bienvenida');
Route::post('/encuesta/acceso', [EncuestaController::class, 'acceso'])->name('encuesta.acceso');

// Pantalla de elección de acceso 
Route::get('/encuesta/continuar', [EncuestaController::class, 'mostrarAcceso'])->name('encuesta.mostrar-acceso');
Route::post('/encuesta/reanudar', [EncuestaController::class, 'reanudar'])->name('encuesta.reanudar');
Route::post('/encuesta/generar', [EncuestaController::class, 'generar'])->name('encuesta.generar');
Route::get('/encuesta/{token}', [EncuestaController::class, 'demograficos'])->name('encuesta.demograficos');

// Sprint 3 - Bloques de preguntas
Route::get('/encuesta/{token}/dimensiones', [EncuestaController::class, 'dimensiones'])
     ->name('encuesta.dimensiones');

Route::get('/encuesta/{token}/bloque/{dimension}', [EncuestaController::class, 'bloque'])
     ->name('encuesta.bloque');

Route::get('/encuesta/{token}/bloque/{dimension}/completado', [EncuestaController::class, 'completado'])
     ->name('encuesta.bloque.completado');

Route::get('/encuesta/{token}/abiertas', [EncuestaController::class, 'abiertas'])
     ->name('encuesta.abiertas');

Route::get('/encuesta/{token}/gracias', [EncuestaController::class, 'gracias'])
     ->name('encuesta.gracias');
