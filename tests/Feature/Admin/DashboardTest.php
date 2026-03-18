<?php

use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\User;

it('super_admin puede acceder al dashboard', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
         ->get(route('admin.dashboard'))
         ->assertOk();
});

it('admin_empresa puede acceder al dashboard', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin)
         ->get(route('admin.dashboard'))
         ->assertOk();
});

it('usuario sin autenticar no puede acceder al dashboard', function () {
    $this->get(route('admin.dashboard'))
         ->assertRedirect(route('login'));
});

it('los KPIs del super_admin incluyen todas las empresas', function () {
    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();

    Encuesta::factory()->count(3)->create(['empresa_id' => $empresa1->id, 'estado' => 'completado']);
    Encuesta::factory()->count(2)->create(['empresa_id' => $empresa2->id, 'estado' => 'disponible']);

    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertViewHas('kpis', fn($kpis) =>
        $kpis['total_tokens'] === 5 &&
        $kpis['completadas'] === 3 &&
        $kpis['disponibles'] === 2
    );
});

it('los KPIs del admin_empresa solo incluyen su empresa', function () {
    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();

    Encuesta::factory()->count(3)->create(['empresa_id' => $empresa1->id]);
    Encuesta::factory()->count(2)->create(['empresa_id' => $empresa2->id]);

    $admin = User::factory()->adminEmpresa($empresa1->id)->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertViewHas('kpis', fn($kpis) => $kpis['total_tokens'] === 3);
});
