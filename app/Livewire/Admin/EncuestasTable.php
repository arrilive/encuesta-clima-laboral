<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Traits\HasTenantScope;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['heading' => 'Encuestas'])]
class EncuestasTable extends Component
{
    use HasTenantScope, WithPagination;

    public string $filtroCorporativo = '';

    public string $filtroEmpresa = '';

    public string $filtroSucursal = '';

    public string $filtroLote = '';

    public string $filtroEstado = '';

    public string $filtroDesde = '';

    public string $filtroHasta = '';

    /** @var array<int|string> */
    public array $selectedTokens = [];

    public bool $selectAll = false;

    public bool $confirmandoEliminacion = false;

    public function updated(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroCorporativo(): void
    {
        $this->filtroEmpresa = '';
        $this->filtroSucursal = '';
        $this->filtroLote = '';
        $this->resetSeleccion();
        $this->resetPage();
    }

    public function updatedFiltroEmpresa(): void
    {
        $this->filtroSucursal = '';
        $this->filtroLote = '';
        $this->resetSeleccion();
        $this->resetPage();
    }

    public function updatedFiltroSucursal(): void
    {
        $this->filtroLote = '';
        $this->resetSeleccion();
        $this->resetPage();
    }

    public function updatedFiltroLote(): void
    {
        $this->resetSeleccion();
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetSeleccion();
        $this->resetPage();
    }

    public function updatedFiltroDesde(): void
    {
        $this->resetSeleccion();
        $this->resetPage();
    }

    public function updatedFiltroHasta(): void
    {
        $this->resetSeleccion();
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        $user = auth()->user();
        if (! $user || $user->role !== Role::SUPER_ADMIN->value) {
            $this->selectAll = false;

            return;
        }

        if ($value) {
            $disponiblesPagina = $this->getEncuestasQuery()
                ->where('estado', 'disponible')
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();

            $this->selectedTokens = array_values(array_unique(array_merge(
                array_map('strval', $this->selectedTokens),
                $disponiblesPagina
            )));
        } else {
            $this->selectedTokens = [];
        }
    }

    public function confirmarEliminacion(): void
    {
        $user = auth()->user();
        if (! $user || $user->role !== Role::SUPER_ADMIN->value) {
            return;
        }

        if (empty($this->selectedTokens)) {
            return;
        }

        $this->confirmandoEliminacion = true;
    }

    public function cancelarEliminacion(): void
    {
        $this->confirmandoEliminacion = false;
    }

    public function eliminarTokensSeleccionados(): void
    {
        if (! $this->confirmandoEliminacion) {
            return;
        }

        $user = auth()->user();
        if (! $user || $user->role !== Role::SUPER_ADMIN->value) {
            abort(403, 'No autorizado.');
        }

        if (empty($this->selectedTokens)) {
            $this->confirmandoEliminacion = false;

            return;
        }

        $encuestasBuscadas = Encuesta::whereIn('id', $this->selectedTokens)->get();

        $validas = collect();
        $omitidasPorEstadoCount = 0;
        $omitidasPorDatosCount = 0;

        foreach ($encuestasBuscadas as $encuesta) {
            if ($encuesta->estado !== 'disponible') {
                $omitidasPorEstadoCount++;

                continue;
            }

            $tieneDatos = $encuesta->datoDemografico()->exists()
                || $encuesta->respuestas()->exists()
                || $encuesta->respuestasAbiertas()->exists();

            if ($tieneDatos) {
                $omitidasPorDatosCount++;

                continue;
            }

            $validas->push($encuesta);
        }

        $totalOmitidasCount = $omitidasPorEstadoCount + $omitidasPorDatosCount;

        if ($validas->isEmpty()) {
            $this->resetSeleccion();
            $this->confirmandoEliminacion = false;

            if ($omitidasPorDatosCount > 0 && $omitidasPorEstadoCount === 0) {
                $mensaje = "No se eliminó ningún token. {$omitidasPorDatosCount} token(s) omitido(s) por tener datos asociados inconsistentes (contacta soporte técnico para revisarlos manualmente).";
            } elseif ($omitidasPorDatosCount > 0 && $omitidasPorEstadoCount > 0) {
                $mensaje = "No se eliminó ningún token. {$omitidasPorEstadoCount} token(s) omitido(s) por no estar disponible(s) y {$omitidasPorDatosCount} token(s) omitido(s) por tener datos asociados inconsistentes (contacta soporte técnico para revisarlos manualmente).";
            } else {
                $mensaje = 'No se eliminó ningún token. Los tokens seleccionados ya no están disponibles.';
            }

            $this->dispatch('notify', mensaje: $mensaje, tipo: 'warning');

            return;
        }

        $eliminadasCount = $validas->count();

        DB::transaction(function () use ($validas) {
            $porLote = $validas->groupBy('lote_id');
            $validasIds = $validas->pluck('id')->toArray();

            Encuesta::whereIn('id', $validasIds)->delete();

            foreach ($porLote as $loteId => $encuestasDelLote) {
                if (! $loteId) {
                    continue;
                }
                $lote = Lote::find($loteId);
                if ($lote) {
                    $countEliminadas = $encuestasDelLote->count();
                    $nuevoTotal = max(0, $lote->tokens_total - $countEliminadas);
                    $lote->update(['tokens_total' => $nuevoTotal]);

                    $restantes = Encuesta::where('lote_id', $loteId)->count();
                    if ($restantes === 0) {
                        $lote->delete();
                    }
                }
            }
        });

        $this->resetSeleccion();
        $this->confirmandoEliminacion = false;

        if ($totalOmitidasCount > 0) {
            $partesOmitidas = [];
            if ($omitidasPorEstadoCount > 0) {
                $partesOmitidas[] = "{$omitidasPorEstadoCount} token(s) omitido(s) por no estar disponible(s)";
            }
            if ($omitidasPorDatosCount > 0) {
                $partesOmitidas[] = "{$omitidasPorDatosCount} token(s) omitido(s) por tener datos asociados inconsistentes (contacta soporte técnico para revisarlos manualmente)";
            }

            $mensajeOmitidos = implode(' y ', $partesOmitidas);
            $mensaje = "{$eliminadasCount} token(s) eliminado(s) correctamente. {$mensajeOmitidos}.";
            $this->dispatch('notify', mensaje: $mensaje, tipo: 'warning');
        } else {
            $mensaje = $eliminadasCount === 1
                ? '1 token eliminado correctamente.'
                : "{$eliminadasCount} tokens eliminados correctamente.";
            $this->dispatch('notify', mensaje: $mensaje, tipo: 'success');
        }
    }

    private function resetSeleccion(): void
    {
        $this->selectedTokens = [];
        $this->selectAll = false;
    }

    public function getCorporativosProperty()
    {
        $user = auth()->user();
        if ($user->role !== Role::SUPER_ADMIN->value) {
            return collect();
        }

        return \App\Models\Corporativo::where('activa', true)->orderBy('nombre')->get();
    }

    public function getEmpresasProperty()
    {
        $user = auth()->user();
        if (! in_array($user->role, [
            Role::SUPER_ADMIN->value,
            Role::ADMIN_CORPORATIVO->value,
        ])) {
            return collect();
        }

        return match ($user->role) {
            Role::SUPER_ADMIN->value => $this->filtroCorporativo
                ? \App\Models\Empresa::where('corporativo_id', $this->filtroCorporativo)->orderBy('nombre')->get()
                : \App\Models\Empresa::orderBy('nombre')->get(),
            Role::ADMIN_CORPORATIVO->value => \App\Models\Empresa::where('corporativo_id', $user->corporativo_id)->orderBy('nombre')->get(),
            default => collect(),
        };
    }

    public function getSucursalesProperty()
    {
        $user = auth()->user();

        if ($user->role === Role::ADMIN_SUCURSAL->value) {
            return collect();
        }

        if (in_array($user->role, [
            Role::SUPER_ADMIN->value,
            Role::ADMIN_CORPORATIVO->value,
        ]) && ! $this->filtroEmpresa) {
            return collect();
        }

        $empresaId = $this->filtroEmpresa ?: $user->empresa_id;

        return \App\Models\Sucursal::where('empresa_id', $empresaId)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get();
    }

    public function getLotesProperty()
    {
        $user = auth()->user();

        if (in_array($user->role, [
            Role::SUPER_ADMIN->value,
            Role::ADMIN_CORPORATIVO->value,
        ]) && ! $this->filtroEmpresa) {
            return collect();
        }

        $query = \App\Models\Lote::with('sucursal');
        $query = $this->scopeByRole($query);

        if ($this->filtroEmpresa) {
            $query->where('empresa_id', $this->filtroEmpresa);
        }

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        return $query->orderByDesc('fecha_inicio')->get();
    }

    protected function onEachSide(): int
    {
        return 1;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroCorporativo = '';
        $this->filtroEmpresa = '';
        $this->filtroSucursal = '';
        $this->filtroLote = '';
        $this->filtroEstado = '';
        $this->filtroDesde = '';
        $this->filtroHasta = '';
        $this->resetSeleccion();
        $this->resetPage();
    }

    private function getEncuestasQuery()
    {
        $user = auth()->user();

        return Encuesta::query()
            ->with(['lote.empresa', 'lote.sucursal'])
            ->whereHas('lote', fn ($q) => $this->scopeByRole($q))
            ->when($user->role === Role::SUPER_ADMIN->value && $this->filtroCorporativo, fn ($q) => $q->whereHas('lote.empresa', fn ($q2) => $q2->where('corporativo_id', $this->filtroCorporativo)))
            ->when(in_array($user->role, [
                Role::SUPER_ADMIN->value,
                Role::ADMIN_CORPORATIVO->value,
            ]) && $this->filtroEmpresa, fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('empresa_id', $this->filtroEmpresa)))
            ->when($this->filtroSucursal, fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('sucursal_id', $this->filtroSucursal)))
            ->when($this->filtroLote, fn ($q) => $q->where('lote_id', $this->filtroLote))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroDesde, fn ($q) => $q->whereDate('fecha_asignacion', '>=', $this->filtroDesde))
            ->when($this->filtroHasta, fn ($q) => $q->whereDate('fecha_asignacion', '<=', $this->filtroHasta))
            ->orderByDesc('fecha_asignacion');
    }

    public function render()
    {
        $encuestas = $this->getEncuestasQuery()->paginate(14);

        return view('livewire.admin.encuestas-table', [
            'encuestas' => $encuestas,
            'corporativos' => $this->corporativos,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
            'lotes' => $this->lotes,
        ]);
    }
}
