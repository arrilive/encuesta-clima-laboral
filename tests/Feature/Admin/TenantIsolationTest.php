<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\EncuestasTable;
use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('admin_sucursal solo ve encuestas de su sucursal', function () {
    $corp = Corporativo::factory()->create();
    $empresa = Empresa::factory()->create(['corporativo_id' => $corp->id]);
    $sucursal1 = Sucursal::factory()->create(['empresa_id' => $empresa->id]);
    $sucursal2 = Sucursal::factory()->create(['empresa_id' => $empresa->id]);

    $lote1 = Lote::factory()->create(['empresa_id' => $empresa->id, 'sucursal_id' => $sucursal1->id]);
    $lote2 = Lote::factory()->create(['empresa_id' => $empresa->id, 'sucursal_id' => $sucursal2->id]);

    $encuesta1 = Encuesta::factory()->create(['lote_id' => $lote1->id, 'estado' => 'completado']);
    $encuesta2 = Encuesta::factory()->create(['lote_id' => $lote2->id, 'estado' => 'completado']);

    $admin = User::factory()->adminSucursal($sucursal1->id)->create();
    $this->actingAs($admin);

    // En Dashboard
    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');
    expect($kpis['total_tokens'])->toBe(1);

    // En EncuestasTable
    Livewire::test(EncuestasTable::class)
        ->assertViewHas('encuestas', function ($encuestas) use ($encuesta1) {
            return $encuestas->count() === 1 && $encuestas->first()->id === $encuesta1->id;
        });
});

it('admin_corporativo solo ve encuestas de empresas de su corporativo', function () {
    $corpA = Corporativo::factory()->create();
    $corpB = Corporativo::factory()->create();

    $empresaA1 = Empresa::factory()->create(['corporativo_id' => $corpA->id]);
    $empresaA2 = Empresa::factory()->create(['corporativo_id' => $corpA->id]);
    $empresaB = Empresa::factory()->create(['corporativo_id' => $corpB->id]);

    $loteA1 = Lote::factory()->create(['empresa_id' => $empresaA1->id]);
    $loteA2 = Lote::factory()->create(['empresa_id' => $empresaA2->id]);
    $loteB = Lote::factory()->create(['empresa_id' => $empresaB->id]);

    $encuestaA1 = Encuesta::factory()->create(['lote_id' => $loteA1->id, 'estado' => 'completado']);
    $encuestaA2 = Encuesta::factory()->create(['lote_id' => $loteA2->id, 'estado' => 'completado']);
    $encuestaB = Encuesta::factory()->create(['lote_id' => $loteB->id, 'estado' => 'completado']);

    $admin = User::factory()->adminCorporativo($corpA->id)->create();
    $this->actingAs($admin);

    // En Dashboard
    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');
    expect($kpis['total_tokens'])->toBe(2);

    // En EncuestasTable
    Livewire::test(EncuestasTable::class)
        ->assertViewHas('encuestas', function ($encuestas) use ($encuestaA1, $encuestaA2, $encuestaB) {
            $ids = $encuestas->pluck('id')->toArray();

            return count($ids) === 2 &&
                   in_array($encuestaA1->id, $ids) &&
                   in_array($encuestaA2->id, $ids) &&
                   ! in_array($encuestaB->id, $ids);
        });
});

it('admin_empresa no ve encuestas de otra empresa', function () {
    $corp = Corporativo::factory()->create();
    $empresaA = Empresa::factory()->create(['corporativo_id' => $corp->id]);
    $empresaB = Empresa::factory()->create(['corporativo_id' => $corp->id]);

    $loteA = Lote::factory()->create(['empresa_id' => $empresaA->id]);
    $loteB = Lote::factory()->create(['empresa_id' => $empresaB->id]);

    $encuestaA = Encuesta::factory()->create(['lote_id' => $loteA->id, 'estado' => 'completado']);
    $encuestaB = Encuesta::factory()->create(['lote_id' => $loteB->id, 'estado' => 'completado']);

    $admin = User::factory()->adminEmpresa($empresaA->id)->create();
    $this->actingAs($admin);

    // En Dashboard
    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');
    expect($kpis['total_tokens'])->toBe(1);

    // En EncuestasTable
    Livewire::test(EncuestasTable::class)
        ->assertViewHas('encuestas', function ($encuestas) use ($encuestaA, $encuestaB) {
            $ids = $encuestas->pluck('id')->toArray();

            return count($ids) === 1 &&
                   in_array($encuestaA->id, $ids) &&
                   ! in_array($encuestaB->id, $ids);
        });
});

it('super_admin ve todo', function () {
    $corp = Corporativo::factory()->create();
    $empresaA = Empresa::factory()->create(['corporativo_id' => $corp->id]);
    $empresaB = Empresa::factory()->create(['corporativo_id' => $corp->id]);

    $loteA = Lote::factory()->create(['empresa_id' => $empresaA->id]);
    $loteB = Lote::factory()->create(['empresa_id' => $empresaB->id]);

    $encuestaA = Encuesta::factory()->create(['lote_id' => $loteA->id, 'estado' => 'completado']);
    $encuestaB = Encuesta::factory()->create(['lote_id' => $loteB->id, 'estado' => 'completado']);

    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    // En Dashboard
    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');
    expect($kpis['total_tokens'])->toBe(2);

    // En EncuestasTable
    Livewire::test(EncuestasTable::class)
        ->assertViewHas('encuestas', function ($encuestas) {
            return $encuestas->count() === 2;
        });
});
