<?php

use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\PdfController;
use App\Http\Controllers\EncuestaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'encuesta');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::prefix('admin')
    ->middleware(['auth', 'role:super_admin,admin_empresa,admin_corporativo,admin_sucursal'])
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)
            ->name('dashboard');

        Route::get('/encuestas/exportar', [ExportController::class, 'encuestasCSV'])
            ->name('encuestas.exportar');

        Route::get('/encuestas', \App\Livewire\Admin\EncuestasTable::class)
            ->name('encuestas');

        Route::get('/reportes', \App\Livewire\Admin\Reportes::class)
            ->name('reportes');

        Route::get('/reportes/pdf', [PdfController::class, 'reportePDF'])
            ->name('reportes.pdf');

        // Solo super_admin
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/tokens', \App\Livewire\Admin\GenerarTokens::class)
                ->name('tokens');
            Route::get('/empresas', \App\Livewire\Admin\EmpresasTable::class)
                ->name('empresas');
        });
    });

require __DIR__.'/auth.php';

// Sprint 2 - Rutas públicas de encuesta
Route::get('/encuesta', [EncuestaController::class, 'bienvenida'])->name('encuesta.bienvenida');

// Flujo OTP v1.1
Route::post('/encuesta/verificar-llave', [EncuestaController::class, 'verificarLlave'])->name('encuesta.verificar-llave');
Route::post('/encuesta/solicitar-otp', [EncuestaController::class, 'solicitarOtp'])->name('encuesta.solicitar-otp');
Route::post('/encuesta/verificar-otp', [EncuestaController::class, 'verificarOtp'])->name('encuesta.verificar-otp');

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
