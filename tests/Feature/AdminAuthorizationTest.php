<?php

use App\Models\Empresa;
use App\Models\User;

it('redirige al login si no está autenticado', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('super_admin puede acceder al dashboard admin', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('admin_empresa puede acceder al dashboard admin', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('usuario sin rol admin no puede acceder al dashboard admin', function () {
    $empresa = Empresa::factory()->create();
    $user = User::factory()->create([
        'role' => 'admin_empresa',
        'empresa_id' => $empresa->id,
    ]);

    // Forzamos un rol inválido directamente
    $user->role = 'otro_rol';

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('admin_corporativo puede acceder al dashboard admin', function () {
    $corporativo = \App\Models\Corporativo::factory()->create();
    $admin = User::factory()->adminCorporativo($corporativo->id)->create();
    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('admin_sucursal puede acceder al dashboard admin', function () {
    $empresa = Empresa::factory()->create();
    $sucursal = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id]);
    $admin = User::factory()->adminSucursal($sucursal->id)->create();
    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('admin_corporativo puede ver el historial de tokens pero no el formulario de generación', function () {
    $corporativo = \App\Models\Corporativo::factory()->create();
    $admin = User::factory()->adminCorporativo($corporativo->id)->create();
    $this->actingAs($admin)
        ->get(route('admin.tokens'))
        ->assertOk()
        ->assertDontSee('Generar nuevo lote de tokens')
        ->assertSee('Historial de lotes');
});

it('admin_sucursal puede ver el historial de tokens pero no el formulario de generación', function () {
    $empresa = Empresa::factory()->create();
    $sucursal = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id]);
    $admin = User::factory()->adminSucursal($sucursal->id)->create();
    $this->actingAs($admin)
        ->get(route('admin.tokens'))
        ->assertOk()
        ->assertDontSee('Generar nuevo lote de tokens')
        ->assertSee('Historial de lotes');
});

it('admin_empresa puede ver el historial de tokens pero no el formulario de generación', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $this->actingAs($admin)
        ->get(route('admin.tokens'))
        ->assertOk()
        ->assertDontSee('Generar nuevo lote de tokens')
        ->assertSee('Historial de lotes');
});
