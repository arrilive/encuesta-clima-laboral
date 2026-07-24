<?php

use App\Livewire\Admin\ComparativasDemograficas;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\EncuestasTable;
use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\Respuesta;
use App\Models\Sucursal;
use App\Models\User;
use Database\Seeders\DimensionesSeeder;
use Database\Seeders\OpcionesRespuestaSeeder;
use Database\Seeders\PreguntasSeeder;
use Database\Seeders\SexosSeeder;
use Database\Seeders\SubdimensionesSeeder;
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

it('comparativas demograficas aisla datos por tenant para admin_sucursal y admin_corporativo', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class, SexosSeeder::class]);

    $corpA = Corporativo::factory()->create();
    $corpB = Corporativo::factory()->create();

    $empresaA = Empresa::factory()->create(['corporativo_id' => $corpA->id]);
    $empresaB = Empresa::factory()->create(['corporativo_id' => $corpB->id]);

    $sucursalA1 = Sucursal::factory()->create(['empresa_id' => $empresaA->id]);
    $sucursalA2 = Sucursal::factory()->create(['empresa_id' => $empresaA->id]);

    $loteA1 = Lote::factory()->create(['empresa_id' => $empresaA->id, 'sucursal_id' => $sucursalA1->id]);
    $loteA2 = Lote::factory()->create(['empresa_id' => $empresaA->id, 'sucursal_id' => $sucursalA2->id]);
    $loteB = Lote::factory()->create(['empresa_id' => $empresaB->id]);

    $encuestaA1 = Encuesta::factory()->completada()->create(['lote_id' => $loteA1->id]);
    $encuestaA2 = Encuesta::factory()->completada()->create(['lote_id' => $loteA2->id]);
    $encuestaB = Encuesta::factory()->completada()->create(['lote_id' => $loteB->id]);

    $sexo = \App\Models\Sexo::first();
    foreach ([$encuestaA1, $encuestaA2, $encuestaB] as $enc) {
        \App\Models\DatoDemografico::create(['encuesta_id' => $enc->id, 'sexo_id' => $sexo->id]);
    }

    $opcion = \App\Models\OpcionRespuesta::where('valor_numerico', 3)->first();
    $pregunta = \App\Models\Pregunta::first();
    foreach ([$encuestaA1, $encuestaA2, $encuestaB] as $enc) {
        Respuesta::create([
            'encuesta_id' => $enc->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    // 1. admin_sucursal solo ve respuestas de su sucursal (sucursalA1)
    $adminSucursal = User::factory()->adminSucursal($sucursalA1->id)->create();
    $compSucursal = Livewire::actingAs($adminSucursal)->test(ComparativasDemograficas::class);
    $reflection = new \ReflectionMethod(ComparativasDemograficas::class, 'getBaseQuery');
    $reflection->setAccessible(true);
    $querySucursal = $reflection->invoke($compSucursal->instance());
    expect($querySucursal->count())->toBe(1);

    // 2. admin_corporativo sin filtroEmpresaId solo ve respuestas de su corporativo (corpA -> 2 encuestas)
    $adminCorp = User::factory()->adminCorporativo($corpA->id)->create();
    $compCorp = Livewire::actingAs($adminCorp)->test(ComparativasDemograficas::class);
    $queryCorp = $reflection->invoke($compCorp->instance());
    expect($queryCorp->count())->toBe(2);
});
