<?php

use App\Enums\Role;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Reportes;
use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        \Database\Seeders\DimensionesSeeder::class,
        \Database\Seeders\SubdimensionesSeeder::class,
        \Database\Seeders\PreguntasSeeder::class,
        \Database\Seeders\OpcionesRespuestaSeeder::class,
    ]);
});

it('muestra banner escenario A cuando todas las empresas del corporativo tienen lote activo', function () {
    $corp = Corporativo::create(['nombre' => 'Corp Alpha', 'activa' => true]);
    $emp1 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Empresa 1', 'activa' => true]);
    $emp2 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Empresa 2', 'activa' => true]);

    $adminCorp = User::factory()->create([
        'role' => Role::ADMIN_CORPORATIVO->value,
        'corporativo_id' => $corp->id,
    ]);

    Lote::create([
        'empresa_id' => $emp1->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Activo Emp 1',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    Lote::create([
        'empresa_id' => $emp2->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Activo Emp 2',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $component = Livewire::actingAs($adminCorp)->test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['is_multi'])->toBeTrue()
        ->and($clima['banners_multi'])->toBe(['Hay 2 rondas activas en tu corporativo. Resultados parciales.'])
        ->and($clima['metadata'])->toBe([
            'total_empresas' => 2,
            'empresas_con_activo' => 2,
            'empresas_con_cerrado' => 0,
            'empresas_con_lote' => 2,
            'empresas_sin_lote' => 0,
        ]);

    $component->assertSee('Hay 2 rondas activas en tu corporativo. Resultados parciales.');
});

it('no muestra ningún banner de resultados parciales cuando ninguna empresa tiene lote activo (Escenario B)', function () {
    $corp = Corporativo::create(['nombre' => 'Corp Escenario B', 'activa' => true]);
    $emp1 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Empresa 1', 'activa' => true]);
    $emp2 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Empresa 2', 'activa' => true]);

    $adminCorp = User::factory()->create([
        'role' => Role::ADMIN_CORPORATIVO->value,
        'corporativo_id' => $corp->id,
    ]);

    Lote::create([
        'empresa_id' => $emp1->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Cerrado Emp 1',
        'fecha_inicio' => now()->subDays(30)->toDateString(),
        'fecha_fin' => now()->subDays(5)->toDateString(),
        'activo' => false,
    ]);

    Lote::create([
        'empresa_id' => $emp2->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Cerrado Emp 2',
        'fecha_inicio' => now()->subDays(30)->toDateString(),
        'fecha_fin' => now()->subDays(5)->toDateString(),
        'activo' => false,
    ]);

    $component = Livewire::actingAs($adminCorp)->test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['is_multi'])->toBeTrue()
        ->and($clima['banners_multi'])->toBeEmpty()
        ->and($clima['metadata'])->toBe([
            'total_empresas' => 2,
            'empresas_con_activo' => 0,
            'empresas_con_cerrado' => 2,
            'empresas_con_lote' => 2,
            'empresas_sin_lote' => 0,
        ]);

    $component->assertDontSee('Resultados parciales')
        ->assertDontSee('rondas activas')
        ->assertDontSee('tienen una ronda en curso');
});

it('muestra banner escenario C cuando hay mezcla de empresas con lotes activos y cerrados', function () {
    $corp = Corporativo::create(['nombre' => 'Corp Beta', 'activa' => true]);
    $emp1 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Emp 1', 'activa' => true]);
    $emp2 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Emp 2', 'activa' => true]);

    $adminCorp = User::factory()->create([
        'role' => Role::ADMIN_CORPORATIVO->value,
        'corporativo_id' => $corp->id,
    ]);

    // Emp 1: Lote activo
    Lote::create([
        'empresa_id' => $emp1->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Activo Emp 1',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    // Emp 2: Lote cerrado
    Lote::create([
        'empresa_id' => $emp2->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Cerrado Emp 2',
        'fecha_inicio' => now()->subDays(30)->toDateString(),
        'fecha_fin' => now()->subDays(5)->toDateString(),
        'activo' => false,
    ]);

    $component = Livewire::actingAs($adminCorp)->test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['is_multi'])->toBeTrue()
        ->and($clima['banners_multi'])->toBe(['Resultados parciales: 1 de 2 empresas tienen una ronda en curso.'])
        ->and($clima['metadata'])->toBe([
            'total_empresas' => 2,
            'empresas_con_activo' => 1,
            'empresas_con_cerrado' => 1,
            'empresas_con_lote' => 2,
            'empresas_sin_lote' => 0,
        ]);

    $component->assertSee('Resultados parciales: 1 de 2 empresas tienen una ronda en curso.');
});

it('muestra banner escenario D cuando al menos una empresa nunca ha generado lotes', function () {
    $corp = Corporativo::create(['nombre' => 'Corp Gamma', 'activa' => true]);
    $emp1 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Emp 1', 'activa' => true]);
    $emp2 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Emp 2 (Sin Lote)', 'activa' => true]);

    $adminCorp = User::factory()->create([
        'role' => Role::ADMIN_CORPORATIVO->value,
        'corporativo_id' => $corp->id,
    ]);

    // Emp 1: Lote cerrado
    Lote::create([
        'empresa_id' => $emp1->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Cerrado Emp 1',
        'fecha_inicio' => now()->subDays(30)->toDateString(),
        'fecha_fin' => now()->subDays(5)->toDateString(),
        'activo' => false,
    ]);

    $component = Livewire::actingAs($adminCorp)->test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['is_multi'])->toBeTrue()
        ->and($clima['banners_multi'])->toBe(['1 de 2 empresas aún no tiene(n) encuestas generadas.'])
        ->and($clima['metadata'])->toBe([
            'total_empresas' => 2,
            'empresas_con_activo' => 0,
            'empresas_con_cerrado' => 1,
            'empresas_con_lote' => 1,
            'empresas_sin_lote' => 1,
        ]);

    $component->assertSee('1 de 2 empresas aún no tiene(n) encuestas generadas.');
});

it('muestra escenarios C y D simultáneamente si hay mezcla de activas/cerradas y empresa sin lote', function () {
    $corp = Corporativo::create(['nombre' => 'Corp Delta', 'activa' => true]);
    $emp1 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Emp 1 (Activa)', 'activa' => true]);
    $emp2 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Emp 2 (Cerrada)', 'activa' => true]);
    $emp3 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Emp 3 (Sin Lotes)', 'activa' => true]);

    $adminCorp = User::factory()->create([
        'role' => Role::ADMIN_CORPORATIVO->value,
        'corporativo_id' => $corp->id,
    ]);

    Lote::create([
        'empresa_id' => $emp1->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Activo Emp 1',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    Lote::create([
        'empresa_id' => $emp2->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Cerrado Emp 2',
        'fecha_inicio' => now()->subDays(30)->toDateString(),
        'fecha_fin' => now()->subDays(5)->toDateString(),
        'activo' => false,
    ]);

    $component = Livewire::actingAs($adminCorp)->test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['is_multi'])->toBeTrue()
        ->and($clima['banners_multi'])->toBe([
            'Resultados parciales: 1 de 3 empresas tienen una ronda en curso.',
            '1 de 3 empresas aún no tiene(n) encuestas generadas.',
        ])
        ->and($clima['metadata'])->toBe([
            'total_empresas' => 3,
            'empresas_con_activo' => 1,
            'empresas_con_cerrado' => 1,
            'empresas_con_lote' => 2,
            'empresas_sin_lote' => 1,
        ]);

    $component->assertSee('Resultados parciales: 1 de 3 empresas tienen una ronda en curso.')
        ->assertSee('1 de 3 empresas aún no tiene(n) encuestas generadas.');
});

it('consolida correctamente promedio general y dimensiones cuando 2 o más empresas tienen lotes simultáneos', function () {
    $corp = Corporativo::create(['nombre' => 'Corp Epsilon', 'activa' => true]);
    $emp1 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Empresa A', 'activa' => true]);
    $emp2 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Empresa B', 'activa' => true]);

    $adminCorp = User::factory()->create([
        'role' => Role::ADMIN_CORPORATIVO->value,
        'corporativo_id' => $corp->id,
    ]);

    $lote1 = Lote::create([
        'empresa_id' => $emp1->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 5,
        'nombre' => 'Lote 1',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $lote2 = Lote::create([
        'empresa_id' => $emp2->id,
        'user_id' => $adminCorp->id,
        'tokens_total' => 5,
        'nombre' => 'Lote 2',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $opcionTotalmente = OpcionRespuesta::where('valor_numerico', 3)->first();
    $opcionDesacuerdo = OpcionRespuesta::where('valor_numerico', 1)->first();
    $preguntas = Pregunta::all();

    // Encuestas en Emp 1 -> Respuestas al 100% (3 pts)
    for ($i = 0; $i < 3; $i++) {
        $enc1 = Encuesta::create(['token' => 'TK-EMP1-'.$i, 'estado' => 'completado', 'lote_id' => $lote1->id]);
        foreach ($preguntas as $p) {
            Respuesta::create(['encuesta_id' => $enc1->id, 'pregunta_id' => $p->id, 'opcion_respuesta_id' => $opcionTotalmente->id]);
        }
    }

    // Encuestas en Emp 2 -> Respuestas al 0% (1 pt)
    for ($i = 0; $i < 3; $i++) {
        $enc2 = Encuesta::create(['token' => 'TK-EMP2-'.$i, 'estado' => 'completado', 'lote_id' => $lote2->id]);
        foreach ($preguntas as $p) {
            Respuesta::create(['encuesta_id' => $enc2->id, 'pregunta_id' => $p->id, 'opcion_respuesta_id' => $opcionDesacuerdo->id]);
        }
    }

    // El promedio consolidado de las 2 empresas combinadas (100% + 0%) debe dar 50.0%
    Livewire::actingAs($adminCorp)
        ->test(Dashboard::class)
        ->assertSee('50.0');

    Livewire::actingAs($adminCorp)
        ->test(Reportes::class)
        ->assertSee('50.0');
});

it('super_admin filtrando por corporativo recibe el mismo tratamiento consolidado que admin_corporativo', function () {
    $corp = Corporativo::create(['nombre' => 'Corp Zeta', 'activa' => true]);
    $emp1 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Empresa 1', 'activa' => true]);
    $emp2 = Empresa::factory()->create(['corporativo_id' => $corp->id, 'nombre' => 'Empresa 2', 'activa' => true]);

    $superAdmin = User::factory()->superAdmin()->create();

    $lote1 = Lote::create([
        'empresa_id' => $emp1->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 5,
        'nombre' => 'Lote Activo Emp 1',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $lote2 = Lote::create([
        'empresa_id' => $emp2->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 5,
        'nombre' => 'Lote Activo Emp 2',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $this->actingAs($superAdmin);

    $class = new class
    {
        use \App\Traits\HasTenantScope;

        public ?string $filtroCorporativoId = null;

        public ?string $filtroEmpresaId = null;

        public ?string $filtroSucursalId = null;

        public ?string $filtroLoteId = null;

        public function testResolverLotes()
        {
            return $this->resolverLotesEstadoActual();
        }
    };

    $class->filtroCorporativoId = (string) $corp->id;
    $infoLote = $class->testResolverLotes();

    expect($infoLote['is_multi'])->toBeTrue()
        ->and($infoLote['lote_ids'])->toEqualCanonicalizing([$lote1->id, $lote2->id])
        ->and($infoLote['metadata']['total_empresas'])->toBe(2);
});

it('aisla correctamente el alcance de admin_sucursal evitando la consolidación multi-empresa', function () {
    $corp1 = Corporativo::create(['nombre' => 'Corp 1', 'activa' => true]);
    $emp1 = Empresa::factory()->create(['corporativo_id' => $corp1->id, 'nombre' => 'Empresa A', 'activa' => true]);
    $suc1 = Sucursal::factory()->create(['empresa_id' => $emp1->id, 'nombre' => 'Sucursal A']);

    $corp2 = Corporativo::create(['nombre' => 'Corp 2', 'activa' => true]);
    $emp2 = Empresa::factory()->create(['corporativo_id' => $corp2->id, 'nombre' => 'Empresa B', 'activa' => true]);
    $suc2 = Sucursal::factory()->create(['empresa_id' => $emp2->id, 'nombre' => 'Sucursal B']);

    // admin_sucursal se crea con empresa_id null por especificación del esquema
    $adminSuc = User::factory()->create([
        'role' => Role::ADMIN_SUCURSAL->value,
        'empresa_id' => null,
        'sucursal_id' => $suc1->id,
    ]);

    $lote1 = Lote::create([
        'empresa_id' => $emp1->id,
        'sucursal_id' => $suc1->id,
        'user_id' => $adminSuc->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Sucursal A',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $lote2 = Lote::create([
        'empresa_id' => $emp2->id,
        'sucursal_id' => $suc2->id,
        'user_id' => $adminSuc->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Sucursal B',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    Encuesta::factory()->count(10)->create(['lote_id' => $lote1->id, 'estado' => 'disponible']);
    Encuesta::factory()->count(10)->create(['lote_id' => $lote2->id, 'estado' => 'disponible']);

    Livewire::actingAs($adminSuc);

    $component = Livewire::test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['is_multi'])->toBeFalse()
        ->and($clima['lote_nombre'])->toBe('Lote Sucursal A');

    $kpis = $component->viewData('kpis');
    expect($kpis['total_tokens'])->toBe(10);
});

it('super_admin sin filtro de corporativo no dispara consolidación multi-empresa (getEmpresasInScope retorna colección vacía)', function () {
    $corp1 = Corporativo::create(['nombre' => 'Corp 1', 'activa' => true]);
    $emp1 = Empresa::factory()->create(['corporativo_id' => $corp1->id, 'nombre' => 'Empresa 1', 'activa' => true]);

    $corp2 = Corporativo::create(['nombre' => 'Corp 2', 'activa' => true]);
    $emp2 = Empresa::factory()->create(['corporativo_id' => $corp2->id, 'nombre' => 'Empresa 2', 'activa' => true]);

    $superAdmin = User::factory()->superAdmin()->create();

    Lote::create([
        'empresa_id' => $emp1->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 10,
        'nombre' => 'Lote 1',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    Lote::create([
        'empresa_id' => $emp2->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 10,
        'nombre' => 'Lote 2',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $this->actingAs($superAdmin);

    $class = new class
    {
        use \App\Traits\HasTenantScope;

        public ?string $filtroCorporativoId = null;

        public ?string $filtroEmpresaId = null;

        public ?string $filtroSucursalId = null;

        public ?string $filtroLoteId = null;

        public function testScope(?array $filtros = null)
        {
            return $this->getEmpresasInScope($filtros);
        }

        public function testResolverLotes()
        {
            return $this->resolverLotesEstadoActual();
        }
    };

    // Sin filtro de corporativo: getEmpresasInScope debe retornar colección vacía
    expect($class->testScope())->toBeEmpty();

    $infoLote = $class->testResolverLotes();
    expect($infoLote['is_multi'])->toBeFalse()
        ->and($infoLote['lote_ids'])->toBeEmpty();

    // Con filtro de corporativo: debe resolver únicamente las empresas del corporativo especificado
    $class->filtroCorporativoId = (string) $corp1->id;
    expect($class->testScope())->toHaveCount(1)
        ->and($class->testScope()->first())->toBe($emp1->id);
});
