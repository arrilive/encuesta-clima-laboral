<?php

use App\Livewire\Admin\EncuestasTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('puede limpiar filtros correctamente', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    Livewire::test(EncuestasTable::class)
        ->set('buscar', 'TokenDePrueba')
        ->set('filtroCorporativo', '1')
        ->set('filtroEmpresa', '2')
        ->set('filtroSucursal', '3')
        ->set('filtroLote', '4')
        ->set('filtroEstado', 'completado')
        ->set('filtroDesde', '2026-06-01')
        ->set('filtroHasta', '2026-06-30')
        ->call('gotoPage', 2)
        ->call('limpiarFiltros')
        ->assertSet('buscar', '')
        ->assertSet('filtroCorporativo', '')
        ->assertSet('filtroEmpresa', '')
        ->assertSet('filtroSucursal', '')
        ->assertSet('filtroLote', '')
        ->assertSet('filtroEstado', '')
        ->assertSet('filtroDesde', '')
        ->assertSet('filtroHasta', '');
});
