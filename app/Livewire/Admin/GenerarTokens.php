<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\TokenLote;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin', ['heading' => 'Tokens'])]
class GenerarTokens extends Component
{
    public string $cantidad = '10';

    public string $nombre = '';

    public string $empresaId = '';

    protected function rules(): array
    {
        return [
            'cantidad' => 'required|numeric|integer|min:1|max:500',
            'nombre' => 'nullable|string|max:100',
            'empresaId' => 'required|exists:empresas,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'cantidad.required' => 'Indica cuántos tokens quieres generar.',
            'cantidad.numeric' => 'La cantidad debe ser un número.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'El mínimo permitido es 1 token.',
            'cantidad.max' => 'El máximo permitido son 500 tokens.',
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

        // Crear el lote
        $lote = TokenLote::create([
            'empresa_id' => $this->empresaId,
            'user_id' => $user->id,
            'cantidad' => $this->cantidad,
            'nombre' => $this->nombre ?: null,
        ]);

        // Generar tokens en lote — una sola query
        $tokens = collect(range(1, $this->cantidad))->map(fn () => [
            'token' => Str::random(64),
            'empresa_id' => $this->empresaId,
            'lote_id' => $lote->id,
            'estado' => 'disponible',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Encuesta::insert($tokens->toArray());

        $this->totalGenerado = (int) $this->cantidad;
        $this->generado = true;

        // Reset del formulario
        $this->cantidad = '10';
        $this->nombre = '';
        if ($user->role === 'super_admin') {
            $this->empresaId = '';
        }
    }

    public function render()
    {
        $user = auth()->user();

        $empresas = $user->role === 'super_admin'
            ? Empresa::orderBy('nombre')->get()
            : collect();

        $lotes = TokenLote::with('empresa', 'user')
            ->when($user->role === 'admin_empresa', fn ($q) => $q->where('empresa_id', $user->empresa_id)
            )
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.generar-tokens', compact('empresas', 'lotes'));
    }
}
