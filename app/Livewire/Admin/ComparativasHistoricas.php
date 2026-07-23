<?php

namespace App\Livewire\Admin;

use App\Models\Corporativo;
use App\Models\Dimension;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\Respuesta;
use App\Models\Subdimension;
use App\Models\Sucursal;
use App\Services\ClimaScoringService;
use App\Traits\HasTenantScope;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin', ['heading' => 'Tendencias'])]
class ComparativasHistoricas extends Component
{
    use HasTenantScope;

    public int $nivel = 1;

    public ?int $dimensionActivaId = null;

    public string $loteIdA = '';

    public string $loteIdB = '';

    // Filtros para acotar el dropdown de lotes
    public string $filtroCorporativoId = '';

    public string $filtroEmpresaId = '';

    public string $filtroSucursalId = '';

    public function mount(): void
    {
        // Default state is empty so user sees '-- Seleccionar Periodo Base --' and '-- Seleccionar Periodo Comparado --'
    }

    public function updatedFiltroCorporativoId(): void
    {
        $this->filtroEmpresaId = '';
        $this->filtroSucursalId = '';
        $this->validarYLimpiarLotesSeleccionados();
    }

    public function updatedFiltroEmpresaId(): void
    {
        $this->filtroSucursalId = '';
        $this->validarYLimpiarLotesSeleccionados();
    }

    public function updatedFiltroSucursalId(): void
    {
        $this->validarYLimpiarLotesSeleccionados();
    }

    private function validarYLimpiarLotesSeleccionados(): void
    {
        $lotesPermitidos = $this->lotes->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        if (! in_array($this->loteIdA, $lotesPermitidos, true)) {
            $this->loteIdA = '';
        }
        if (! in_array($this->loteIdB, $lotesPermitidos, true)) {
            $this->loteIdB = '';
        }
    }

    public function getCorporativosProperty()
    {
        $user = auth()->user();
        if ($user->role !== \App\Enums\Role::SUPER_ADMIN->value) {
            return collect();
        }

        return Corporativo::orderBy('nombre')->get();
    }

    public function getEmpresasProperty()
    {
        $user = auth()->user();

        if ($user->role === \App\Enums\Role::SUPER_ADMIN->value) {
            return $this->filtroCorporativoId
                ? Empresa::where('corporativo_id', $this->filtroCorporativoId)->orderBy('nombre')->get()
                : Empresa::orderBy('nombre')->get();
        }

        if ($user->role === \App\Enums\Role::ADMIN_CORPORATIVO->value) {
            return Empresa::where('corporativo_id', $user->corporativo_id)->orderBy('nombre')->get();
        }

        return collect();
    }

    public function getSucursalesProperty()
    {
        $user = auth()->user();

        if ($user->role === \App\Enums\Role::ADMIN_SUCURSAL->value) {
            return collect();
        }

        if (in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]) && ! $this->filtroEmpresaId) {
            return collect();
        }

        $empresaId = $this->filtroEmpresaId ?: $user->empresa_id;

        return Sucursal::where('empresa_id', $empresaId)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get();
    }

    public function getLotesProperty()
    {
        $user = auth()->user();

        $query = Lote::with(['empresa', 'sucursal']);
        $query = $this->scopeByRole($query);

        if ($this->filtroCorporativoId && $user->role === \App\Enums\Role::SUPER_ADMIN->value) {
            $query->whereHas('empresa', fn ($q) => $q->where('corporativo_id', $this->filtroCorporativoId));
        }

        if ($this->filtroEmpresaId) {
            $sucursalIds = $this->sucursalIdsDeEmpresa((int) $this->filtroEmpresaId);
            $query->where(function ($q) use ($sucursalIds) {
                $q->where('empresa_id', $this->filtroEmpresaId)
                    ->orWhereIn('sucursal_id', $sucursalIds);
            });
        }

        if ($this->filtroSucursalId) {
            $query->where('sucursal_id', $this->filtroSucursalId);
        }

        return $query->orderByDesc('fecha_inicio')->get();
    }

    public function irNivel1(): void
    {
        $this->nivel = 1;
        $this->dimensionActivaId = null;
    }

    public function irNivel2(int $dimensionId): void
    {
        $this->nivel = 2;
        $this->dimensionActivaId = $dimensionId;
    }

    public function formatLoteLabel(Lote $lote): string
    {
        $empresaNombre = $lote->empresa?->nombre ?? ($lote->sucursal?->empresa?->nombre ?? 'Empresa');
        $sucursalTexto = $lote->sucursal ? $lote->sucursal->nombre : 'General';
        $nombreLote = $lote->nombre ?: 'Lote #'.$lote->id;
        $inicio = $lote->fecha_inicio ? $lote->fecha_inicio->format('d/m/Y') : '-';
        $fin = $lote->fecha_fin ? $lote->fecha_fin->format('d/m/Y') : 'en curso';

        return "{$empresaNombre} · {$sucursalTexto} · {$nombreLote} ({$inicio}–{$fin})";
    }

    public function formatLoteLabelShort(Lote $lote): string
    {
        $nombreLote = $lote->nombre ?: 'Lote #'.$lote->id;
        $sucursalTexto = $lote->sucursal ? $lote->sucursal->nombre : ($lote->empresa?->nombre ?? 'General');

        return "{$nombreLote} ({$sucursalTexto})";
    }

    public function formatLoteNombreSimple(Lote $lote): string
    {
        return $lote->nombre ?: 'Lote #'.$lote->id;
    }

    public function getBadgeInfo(?float $scoreA, ?float $scoreB): array
    {
        if ($scoreA === null || $scoreB === null) {
            return [
                'delta' => 0.0,
                'formatted' => 'N/A',
                'class' => 'text-slate-400 text-xs font-medium',
                'significativo' => false,
                'direccion' => 'sin_datos',
            ];
        }

        $delta = round($scoreB - $scoreA, 1);

        if ($delta >= 5.0) {
            return [
                'delta' => $delta,
                'formatted' => '▲ +'.number_format($delta, 1),
                'class' => 'bg-emerald-600 text-white font-bold px-2.5 py-1 rounded-lg text-xs inline-flex items-center gap-1 shadow-2xs',
                'significativo' => true,
                'direccion' => 'subida',
            ];
        } elseif ($delta <= -5.0) {
            return [
                'delta' => $delta,
                'formatted' => '▼ '.number_format($delta, 1),
                'class' => 'bg-rose-600 text-white font-bold px-2.5 py-1 rounded-lg text-xs inline-flex items-center gap-1 shadow-2xs',
                'significativo' => true,
                'direccion' => 'bajada',
            ];
        } else {
            return [
                'delta' => $delta,
                'formatted' => ($delta > 0 ? '+' : '').number_format($delta, 1),
                'class' => 'bg-slate-100 text-slate-600 font-semibold px-2.5 py-1 rounded-lg text-xs inline-flex items-center gap-1',
                'significativo' => false,
                'direccion' => 'neutro',
            ];
        }
    }

    public function render(ClimaScoringService $scoringService)
    {
        $lotesPermitidos = $this->lotes->pluck('id')->toArray();

        $validLoteIdA = in_array((int) $this->loteIdA, $lotesPermitidos) ? (int) $this->loteIdA : null;
        $validLoteIdB = in_array((int) $this->loteIdB, $lotesPermitidos) ? (int) $this->loteIdB : null;

        $loteA = $validLoteIdA ? Lote::with(['empresa', 'sucursal'])->find($validLoteIdA) : null;
        $loteB = $validLoteIdB ? Lote::with(['empresa', 'sucursal'])->find($validLoteIdB) : null;

        $selectedLoteIds = array_values(array_filter([$validLoteIdA, $validLoteIdB]));

        if (! empty($selectedLoteIds)) {
            $baseQuery = Respuesta::query()
                ->whereHas('encuesta', fn ($q) => $q
                    ->where('estado', 'completado')
                    ->whereIn('lote_id', $selectedLoteIds)
                );
            $scoresMap = $scoringService->scoresPorDimensionPorLotes($baseQuery);
        } else {
            $scoresMap = collect();
        }

        $dimensionesAll = Dimension::with('subdimensiones')->orderBy('orden')->get();

        $dataA = $validLoteIdA && $scoresMap->has($validLoteIdA)
            ? $scoresMap->get($validLoteIdA)
            : $this->buildEmptyScoreData($dimensionesAll);

        $dataB = $validLoteIdB && $scoresMap->has($validLoteIdB)
            ? $scoresMap->get($validLoteIdB)
            : $this->buildEmptyScoreData($dimensionesAll);

        $promedioGeneralA = $dataA['promedio_general'];
        $promedioGeneralB = $dataB['promedio_general'];

        $badgeGeneral = ($validLoteIdA && $validLoteIdB)
            ? $this->getBadgeInfo($promedioGeneralA, $promedioGeneralB)
            : [
                'delta' => 0.0,
                'formatted' => 'Selecciona 2 periodos',
                'class' => 'text-slate-400 text-xs font-medium',
                'significativo' => false,
                'direccion' => 'sin_datos',
            ];

        $datosDimensiones = $dimensionesAll->map(function ($d) use ($dataA, $dataB, $validLoteIdA, $validLoteIdB) {
            $dimA = collect($dataA['dimensiones'])->firstWhere('id', $d->id);
            $dimB = collect($dataB['dimensiones'])->firstWhere('id', $d->id);

            $puntajeA = $validLoteIdA && $dimA ? $dimA['puntaje'] : null;
            $puntajeB = $validLoteIdB && $dimB ? $dimB['puntaje'] : null;

            return [
                'id' => $d->id,
                'nombre' => $d->nombre,
                'puntajeA' => $puntajeA,
                'puntajeB' => $puntajeB,
                'badge' => ($validLoteIdA && $validLoteIdB)
                    ? $this->getBadgeInfo($puntajeA, $puntajeB)
                    : ['formatted' => '—', 'class' => 'text-slate-300 text-xs', 'significativo' => false, 'direccion' => 'sin_datos'],
            ];
        });

        $chartNivel1 = [
            'categorias' => $datosDimensiones->pluck('nombre')->toArray(),
            'series' => [
                [
                    'name' => $loteA ? $this->formatLoteLabelShort($loteA) : 'Periodo Base',
                    'data' => $datosDimensiones->pluck('puntajeA')->map(fn ($v) => $v ?? 0)->toArray(),
                ],
                [
                    'name' => $loteB ? $this->formatLoteLabelShort($loteB) : 'Periodo Comparado',
                    'data' => $datosDimensiones->pluck('puntajeB')->map(fn ($v) => $v ?? 0)->toArray(),
                ],
            ],
        ];

        $datosSubdimensiones = collect();
        $dimensionActiva = null;
        $chartNivel2 = ['categorias' => [], 'series' => []];

        if ($this->nivel === 2 && $this->dimensionActivaId) {
            $dimensionActiva = Dimension::find($this->dimensionActivaId);
            if ($dimensionActiva) {
                $subdims = Subdimension::where('dimension_id', $this->dimensionActivaId)
                    ->orderBy('orden')
                    ->get();

                $datosSubdimensiones = $subdims->map(function ($s) use ($dataA, $dataB, $validLoteIdA, $validLoteIdB) {
                    $subA = collect($dataA['subdimensiones'])->firstWhere('id', $s->id);
                    $subB = collect($dataB['subdimensiones'])->firstWhere('id', $s->id);

                    $puntajeA = ($validLoteIdA && $subA) ? $subA['puntaje'] : null;
                    $puntajeB = ($validLoteIdB && $subB) ? $subB['puntaje'] : null;

                    return [
                        'id' => $s->id,
                        'nombre' => $s->nombre,
                        'puntajeA' => $puntajeA,
                        'puntajeB' => $puntajeB,
                        'badge' => ($validLoteIdA && $validLoteIdB)
                            ? $this->getBadgeInfo($puntajeA, $puntajeB)
                            : ['formatted' => '—', 'class' => 'text-slate-300 text-xs', 'significativo' => false, 'direccion' => 'sin_datos'],
                    ];
                });

                $chartNivel2 = [
                    'categorias' => $datosSubdimensiones->pluck('nombre')->toArray(),
                    'series' => [
                        [
                            'name' => $loteA ? $this->formatLoteLabelShort($loteA) : 'Periodo Base',
                            'data' => $datosSubdimensiones->pluck('puntajeA')->map(fn ($v) => $v ?? 0)->toArray(),
                        ],
                        [
                            'name' => $loteB ? $this->formatLoteLabelShort($loteB) : 'Periodo Comparado',
                            'data' => $datosSubdimensiones->pluck('puntajeB')->map(fn ($v) => $v ?? 0)->toArray(),
                        ],
                    ],
                ];
            }
        }

        return view('livewire.admin.comparativas-historicas', [
            'loteA' => $loteA,
            'loteB' => $loteB,
            'promedioGeneralA' => $promedioGeneralA,
            'promedioGeneralB' => $promedioGeneralB,
            'badgeGeneral' => $badgeGeneral,
            'datosDimensiones' => $datosDimensiones,
            'chartNivel1' => $chartNivel1,
            'dimensionActiva' => $dimensionActiva,
            'datosSubdimensiones' => $datosSubdimensiones,
            'chartNivel2' => $chartNivel2,
            'lotes' => $this->lotes,
            'empresas' => $this->empresas,
            'corporativos' => $this->corporativos,
            'sucursales' => $this->sucursales,
        ]);
    }

    private function buildEmptyScoreData($dimensiones): array
    {
        return [
            'lote_id' => 0,
            'promedio_general' => null,
            'dimensiones' => $dimensiones->map(fn ($d) => ['id' => $d->id, 'nombre' => $d->nombre, 'puntaje' => null]),
            'subdimensiones' => collect(),
        ];
    }
}
