<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin', ['heading' => 'Tokens'])]
class GenerarTokens extends Component
{
    public string $tokens_total = '10';

    public string $nombre = '';

    public string $empresaId = '';

    public string $fecha_inicio = '';

    public string $fecha_fin = '';

    protected function rules(): array
    {
        return [
            'tokens_total' => 'required|numeric|integer|min:1|max:500',
            'nombre' => 'nullable|string|max:100',
            'empresaId' => 'required|exists:empresas,id',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ];
    }

    protected function messages(): array
    {
        return [
            'tokens_total.required' => 'Indica cuántos tokens quieres generar.',
            'tokens_total.numeric' => 'La cantidad debe ser un número.',
            'tokens_total.integer' => 'La cantidad debe ser un número entero.',
            'tokens_total.min' => 'El mínimo permitido es 1 token.',
            'tokens_total.max' => 'El máximo permitido son 500 tokens.',
            'empresaId.required' => 'Selecciona una empresa.',
            'empresaId.exists' => 'La empresa seleccionada no existe.',
        ];
    }

    public bool $generado = false;

    public int $totalGenerado = 0;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->role === 'admin_empresa') {
            $this->empresaId = (string) $user->empresa_id;
        }

        $this->fecha_inicio = now()->toDateString();
        $this->fecha_fin = '';
    }

    public function generar(): void
    {
        $this->validate($this->rules(), $this->messages());

        $user = auth()->user();

        // Seguridad: admin_empresa no puede generar para otra empresa
        if ($user->role === 'admin_empresa' && (int) $this->empresaId !== $user->empresa_id) {
            $this->addError('empresaId', 'No tienes permiso para generar tokens para otra empresa.');

            return;
        }

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
                'tokens_total' => $this->tokens_total,
                'nombre' => $this->nombre ?: null,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
            ]);

            // Generar tokens en lote — una sola query
            $tokens = collect(range(1, $this->tokens_total))->map(fn () => [
                'token' => Str::random(64),
                'lote_id' => $lote->id,
                'estado' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Encuesta::insert($tokens->toArray());
        });

        $this->totalGenerado = (int) $this->tokens_total;
        $this->generado = true;

        // Reset del formulario
        $this->tokens_total = '10';
        $this->nombre = '';
        $this->fecha_inicio = now()->toDateString();
        $this->fecha_fin = '';
        if ($user->role === 'super_admin') {
            $this->empresaId = '';
        }
    }

    public function render()
    {
        $user = auth()->user();

        $empresas = $user->role === 'super_admin'
            ? Empresa::orderByDesc('activa')->orderBy('nombre')->get()
            : collect();

        $lotes = Lote::with('empresa', 'user')
            ->when($user->role === 'admin_empresa', fn ($q) => $q->where('empresa_id', $user->empresa_id)
            )
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.generar-tokens', compact('empresas', 'lotes'));
    }
}
