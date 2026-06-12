<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
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

    public string $fechaInicio = '';

    public string $fechaFin = '';

    protected function rules(): array
    {
        return [
            'tokensTotal' => 'required|numeric|integer|min:1|max:500',
            'nombre' => 'nullable|string|max:100',
            'empresaId' => 'required|exists:empresas,id',
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

        $lote = null;

        // Crear el lote e insertar tokens de manera atómica
        DB::transaction(function () use (&$lote, $user) {
            $lote = Lote::create([
                'empresa_id' => $this->empresaId,
                'user_id' => $user->id,
                'tokens_total' => $this->tokensTotal,
                'nombre' => $this->nombre ?: null,
                'fecha_inicio' => $this->fechaInicio,
                'fecha_fin' => $this->fechaFin,
            ]);

            // Generar tokens en lote — una sola query
            $tokens = collect(range(1, $this->tokensTotal))->map(fn () => [
                'token' => Str::random(64),
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
    }

    public function render()
    {
        $user = auth()->user();

        $empresas = $user->role === Role::SUPER_ADMIN->value
            ? Empresa::orderByDesc('activa')->orderBy('nombre')->get()
            : collect();

        $lotes = $this->scopeByRole(Lote::with('empresa', 'user'))
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.generar-tokens', compact('empresas', 'lotes'));
    }
}
