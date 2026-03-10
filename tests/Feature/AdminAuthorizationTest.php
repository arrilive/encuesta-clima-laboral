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
        'role'       => 'admin_empresa',
        'empresa_id' => $empresa->id,
    ]);

    // Forzamos un rol inválido directamente
    $user->role = 'otro_rol';

    $this->actingAs($user)
         ->get(route('admin.dashboard'))
         ->assertForbidden();
});