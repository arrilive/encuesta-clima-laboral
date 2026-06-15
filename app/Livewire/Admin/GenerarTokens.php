<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\Sucursal;
use App\Traits\HasTenantScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin', ['heading' => 'Tokens'])]
class GenerarTokens extends Component
{
    use HasTenantScope;

    public string $tokensTotal = '';

    public string $nombre = '';

    public string $empresaId = '';

    public string $sucursalId = '';

    public string $fechaInicio = '';

    public string $fechaFin = '';

    public string $modo = 'a';

    public string $empresaIdModoB = '';

    public string $loteId = '';

    public string $cantidadModoB = '10';

    public string $nuevaFechaFin = '';

    public bool $mostrarConfirmacion = false;

    protected function rules(): array
    {
        return [
            'tokensTotal' => 'required|numeric|integer|min:1|max:500',
            'nombre' => 'nullable|string|max:75',
            'empresaId' => 'required|exists:empresas,id',
            'sucursalId' => 'nullable|exists:sucursales,id',
            'fechaInicio' => 'required|date|after_or_equal:today',
            'fechaFin' => 'required|date|after:fechaInicio',
        ];
    }

    protected function messages(): array
    {
        return [
            'tokensTotal.required' => 'Indica cuántos tokens quieres generar.',
            'tokensTotal.numeric' => 'La cantidad debe ser un número.',
            'tokensTotal.integer' => 'La cantidad debe ser un número entero.',
            'tokensTotal.min' => 'El mínimo permitido es 1 token.',
            'tokensTotal.max' => 'El máximo permitido son 500 tokens.',
            'empresaId.required' => 'Selecciona una empresa.',
            'empresaId.exists' => 'La empresa seleccionada no existe.',
            'sucursalId.exists' => 'La sucursal seleccionada no existe.',
            'fechaInicio.required' => 'La fecha de inicio es obligatoria.',
            'fechaInicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fechaInicio.after_or_equal' => 'La fecha de inicio debe ser hoy o una fecha futura.',
            'fechaFin.required' => 'La fecha de fin es obligatoria.',
            'fechaFin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fechaFin.after' => 'La fecha de cierre debe ser posterior a la fecha de inicio.',
        ];
    }

    public bool $generado = false;

    public int $totalGenerado = 0;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->role === Role::SUPER_ADMIN->value) {
            $this->tokensTotal = '10';
            $this->fechaInicio = now()->toDateString();
            $this->fechaFin = '';
            $this->empresaId = '';
            $this->sucursalId = '';
        }
    }

    public function generar(): void
    {
        if (auth()->user()->role !== Role::SUPER_ADMIN->value) {
            return;
        }

        $this->validate($this->rules(), $this->messages());

        $user = auth()->user();

        $empresa = Empresa::findOrFail($this->empresaId);
        if (! $empresa->activa) {
            $this->addError('empresaId', 'No se pueden generar tokens para una empresa inactiva. Actívala primero en el panel de Empresas.');

            return;
        }

        if ($this->sucursalId) {
            $sucursal = Sucursal::findOrFail($this->sucursalId);
            if (! $sucursal->activa) {
                $this->addError('sucursalId', 'No se pueden generar tokens para una sucursal inactiva. Actívala primero en el panel de Empresas.');

                return;
            }
            if ($sucursal->empresa_id !== (int) $this->empresaId) {
                $this->addError('sucursalId', 'La sucursal seleccionada no pertenece a la empresa elegida.');

                return;
            }
        }

        $lote = null;

        // Crear el lote e insertar tokens de manera atómica
        DB::transaction(function () use (&$lote, $user) {
            $lote = Lote::create([
                'empresa_id' => $this->empresaId,
                'sucursal_id' => $this->sucursalId ?: null,
                'user_id' => $user->id,
                'tokens_total' => $this->tokensTotal,
                'nombre' => $this->nombre ?: null,
                'fecha_inicio' => $this->fechaInicio,
                'fecha_fin' => $this->fechaFin,
            ]);

            // Generar tokens en lote — una sola query
            $generated = [];
            while (count($generated) < $this->tokensTotal) {
                $token = 'TK-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4));
                $generated[$token] = true;
            }

            $tokens = collect(array_keys($generated))->map(fn ($token) => [
                'token' => $token,
                'lote_id' => $lote->id,
                'estado' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Encuesta::insert($tokens->toArray());
        });

        $this->totalGenerado = (int) $this->tokensTotal;
        $this->generado = true;

        // Reset del formulario
        $this->tokensTotal = '10';
        $this->nombre = '';
        $this->fechaInicio = now()->toDateString();
        $this->fechaFin = '';
        $this->empresaId = '';
        $this->sucursalId = '';
    }

    public function updatedEmpresaId(): void
    {
        $this->sucursalId = '';
    }

    public function updatedEmpresaIdModoB(): void
    {
        $this->loteId = '';
        $this->nuevaFechaFin = '';
    }

    public function updatedLoteId(): void
    {
        $this->nuevaFechaFin = '';
    }

    protected function rulesModoB(?Lote $lote = null): array
    {
        $rules = [
            'loteId' => 'required|exists:lotes,id',
            'cantidadModoB' => 'required|numeric|integer|min:1|max:500',
            'nuevaFechaFin' => 'nullable|date|after_or_equal:today',
        ];

        if ($lote && $lote->fecha_inicio && $this->nuevaFechaFin) {
            $rules['nuevaFechaFin'] .= '|after_or_equal:'.$lote->fecha_inicio->toDateString();
        }

        return $rules;
    }

    protected function messagesModoB(?Lote $lote = null): array
    {
        $messages = [
            'loteId.required' => 'Selecciona un lote.',
            'loteId.exists' => 'El lote seleccionado no existe.',
            'cantidadModoB.required' => 'Indica cuántos tokens quieres agregar.',
            'cantidadModoB.numeric' => 'La cantidad debe ser un número.',
            'cantidadModoB.integer' => 'La cantidad debe ser un número entero.',
            'cantidadModoB.min' => 'El mínimo permitido es 1 token.',
            'cantidadModoB.max' => 'El máximo permitido son 500 tokens.',
            'nuevaFechaFin.date' => 'La nueva fecha debe ser una fecha válida.',
            'nuevaFechaFin.after_or_equal' => 'La nueva fecha debe ser hoy o una fecha futura.',
        ];

        if ($lote && $lote->fecha_inicio) {
            $messages['nuevaFechaFin.after_or_equal'] = 'La nueva fecha debe ser posterior o igual a la fecha de inicio del lote ('.$lote->fecha_inicio->format('d/m/Y').') y hoy.';
        }

        return $messages;
    }

    public function prepararInyeccion(): void
    {
        if ($this->modo !== 'b') {
            return;
        }

        $lote = Lote::find($this->loteId);

        $this->validate($this->rulesModoB($lote), $this->messagesModoB($lote));

        if (! $lote || ! $lote->activo || $lote->fecha_fin->lt(today())) {
            $this->addError('loteId', 'El lote seleccionado no es válido, no está activo o ya ha expirado.');

            return;
        }

        $this->mostrarConfirmacion = true;
    }

    public function inyectar(): void
    {
        if ($this->modo !== 'b') {
            return;
        }

        if (auth()->user()->role !== Role::SUPER_ADMIN->value) {
            return;
        }

        $lote = Lote::find($this->loteId);

        $this->validate($this->rulesModoB($lote), $this->messagesModoB($lote));

        if (! $lote || ! $lote->activo || $lote->fecha_fin->lt(today())) {
            $this->addError('loteId', 'El lote seleccionado no es válido, no está activo o ya ha expirado.');

            return;
        }

        DB::transaction(function () use ($lote) {
            if ($this->nuevaFechaFin) {
                $lote->update([
                    'fecha_fin' => $this->nuevaFechaFin,
                ]);
            }

            $lote->increment('tokens_total', (int) $this->cantidadModoB);

            $generated = [];
            while (count($generated) < (int) $this->cantidadModoB) {
                $token = 'TK-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4));
                $generated[$token] = true;
            }

            $tokens = collect(array_keys($generated))->map(fn ($token) => [
                'token' => $token,
                'lote_id' => $lote->id,
                'estado' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Encuesta::insert($tokens->toArray());
        });

        $this->totalGenerado = (int) $this->cantidadModoB;
        $this->generado = true;

        $this->empresaIdModoB = '';
        $this->loteId = '';
        $this->cantidadModoB = '10';
        $this->nuevaFechaFin = '';
        $this->mostrarConfirmacion = false;

        $this->dispatch('tokens-inyectados');
    }

    public function getLotesVigentesProperty()
    {
        if (empty($this->empresaIdModoB)) {
            return collect();
        }

        return Lote::with('sucursal')
            ->where('empresa_id', $this->empresaIdModoB)
            ->where('activo', 1)
            ->where('fecha_fin', '>=', today())
            ->orderBy('nombre')
            ->get();
    }

    public function getLoteSeleccionadoProperty(): ?Lote
    {
        if (empty($this->loteId)) {
            return null;
        }

        return Lote::with('sucursal')->find($this->loteId);
    }

    public function getSucursalesProperty()
    {
        if (empty($this->empresaId)) {
            return collect();
        }

        return Sucursal::where('empresa_id', $this->empresaId)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get();
    }

    public function render()
    {
        $user = auth()->user();

        $empresas = $user->role === Role::SUPER_ADMIN->value
            ? Empresa::orderByDesc('activa')->orderBy('nombre')->get()
            : collect();

        $lotes = $this->scopeByRole(Lote::with('empresa', 'user', 'sucursal'))
            ->orderByDesc('created_at')
            ->get();

        $lotesVigentes = $this->lotesVigentes;
        $loteSeleccionado = $this->loteSeleccionado;
        $sucursales = $this->sucursales;

        return view('livewire.admin.generar-tokens', compact('empresas', 'lotes', 'lotesVigentes', 'loteSeleccionado', 'sucursales'));
    }
}
