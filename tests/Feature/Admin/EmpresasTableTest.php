<?php

use App\Models\Empresa;
use App\Models\User;

// ── Autorización ─────────────────────────────────────────────────────────────

todo('super_admin puede acceder a /admin/empresas — withCount encuestas requiere refactor en issue #117');

it('admin_empresa no puede acceder a /admin/empresas', function () {
    $this->seed();

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin)
        ->get(route('admin.empresas'))
        ->assertForbidden();
});

it('usuario no autenticado es redirigido al login', function () {
    $this->seed();

    $this->get(route('admin.empresas'))
        ->assertRedirect(route('login'));
});

// ── Acciones del componente ──────────────────────────────────────────────────

todo('crear() genera empresa y user admin en la base de datos — withCount encuestas requiere refactor en issue #117');

todo('crear() falla si el email del admin ya existe — withCount encuestas requiere refactor en issue #117');

todo('editarNombre() actualiza el nombre de la empresa — withCount encuestas requiere refactor en issue #117');

todo('toggleActiva() cambia el estado de activa a inactiva y viceversa — withCount encuestas requiere refactor en issue #117');

todo('cambiarPasswordAdmin() actualiza la contraseña del user admin de la empresa — withCount encuestas requiere refactor en issue #117');
