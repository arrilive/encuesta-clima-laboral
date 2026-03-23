<?php

namespace App\Livewire\Admin;

use App\Models\Dimension;
use App\Models\Empresa;
use App\Models\Respuesta;
use App\Models\Subdimension;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin', ['heading' => 'Reportes'])]
class Reportes extends Component
{
    // Navegación drill-down
    public int $nivel = 1;
    public ?int $dimensionActivaId = null;
    public ?int $subdimensionActivaId = null;

    // Filtros demográficos
    public string $filtroEdadId = '';
    public string $filtroSexoId = '';
    public string $filtroCargoId = '';
    public string $filtroLugarTrabajoId = '';
    public string $filtroGradoAcademicoId = '';
    public string $filtroAntiguedadId = '';

    // Filtro empresa (solo super_admin)
    public string $filtroEmpresaId = '';

    public function updated(): void
    {
        $this->nivel = 1;
        $this->dimensionActivaId = null;
        $this->subdimensionActivaId = null;
    }

    public function irNivel1(): void
    {
        $this->nivel = 1;
        $this->dimensionActivaId = null;
        $this->subdimensionActivaId = null;
    }

    public function irNivel2(int $dimensionId): void
    {
        $this->nivel = 2;
        $this->dimensionActivaId = $dimensionId;
        $this->subdimensionActivaId = null;
    }

    public function irNivel3(int $subdimensionId): void
    {
        $this->nivel = 3;
        $this->subdimensionActivaId = $subdimensionId;
    }

    public function limpiarFiltros(): void
    {
        $this->filtroEdadId = '';
        $this->filtroSexoId = '';
        $this->filtroCargoId = '';
        $this->filtroLugarTrabajoId = '';
        $this->filtroGradoAcademicoId = '';
        $this->filtroAntiguedadId = '';
        $this->filtroEmpresaId = '';
        $this->irNivel1();
    }

    protected function getBaseQuery()
    {
        $user = auth()->user();
        $query = Respuesta::query()
            ->whereHas('encuesta', fn($q) => $q->where('estado', 'completado'));

        if ($user->role === 'admin_empresa') {
            $query->whereHas('encuesta', fn($q) =>
                $q->where('empresa_id', $user->empresa_id)
            );
        } elseif ($this->filtroEmpresaId) {
            $query->whereHas('encuesta', fn($q) =>
                $q->where('empresa_id', $this->filtroEmpresaId)
            );
        }

        if ($this->filtroEdadId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('edad_id', $this->filtroEdadId)
            );
        }
        if ($this->filtroSexoId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('sexo_id', $this->filtroSexoId)
            );
        }
        if ($this->filtroCargoId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('cargo_id', $this->filtroCargoId)
            );
        }
        if ($this->filtroLugarTrabajoId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('lugar_trabajo_id', $this->filtroLugarTrabajoId)
            );
        }
        if ($this->filtroGradoAcademicoId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('grado_academico_id', $this->filtroGradoAcademicoId)
            );
        }
        if ($this->filtroAntiguedadId) {
            $query->whereHas('encuesta.datoDemografico', fn($q) =>
                $q->where('antiguedad_id', $this->filtroAntiguedadId)
            );
        }

        return $query;
    }

    protected function calcularPuntajeDimension(int $dimensionId): float
    {
        $result = (clone $this->getBaseQuery())
            ->whereHas('pregunta.subdimension', fn($q) =>
                $q->where('dimension_id', $dimensionId)
            )
            ->whereHas('opcionRespuesta', fn($q) =>
                $q->where('valor_numerico', '!=', 0)
            )
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->avg('opciones_respuesta.valor_numerico');

        return round($result ?? 0, 2);
    }

    protected function calcularPuntajeSubdimension(int $subdimensionId): float
    {
        $result = (clone $this->getBaseQuery())
            ->whereHas('pregunta', fn($q) =>
                $q->where('subdimension_id', $subdimensionId)
            )
            ->whereHas('opcionRespuesta', fn($q) =>
                $q->where('valor_numerico', '!=', 0)
            )
            ->join('opciones_respuesta', 'respuestas.opcion_respuesta_id', '=', 'opciones_respuesta.id')
            ->avg('opciones_respuesta.valor_numerico');

        return round($result ?? 0, 2);
    }

    public function getDatosNivel1(): array
    {
        return Dimension::orderBy('orden')->get()->map(fn($d) => [
            'id'      => $d->id,
            'nombre'  => $d->nombre,
            'puntaje' => $this->calcularPuntajeDimension($d->id),
        ])->toArray();
    }

    public function getDatosNivel2(): array
    {
        return Subdimension::where('dimension_id', $this->dimensionActivaId)
            ->orderBy('orden')
            ->get()
            ->map(fn($s) => [
                'id'      => $s->id,
                'nombre'  => $s->nombre,
                'puntaje' => $this->calcularPuntajeSubdimension($s->id),
            ])->toArray();
    }

    public function render()
    {
        $user = auth()->user();
        $datosNivel1 = $this->nivel === 1 ? $this->getDatosNivel1() : [];

        $this->dispatch('radar-datos-actualizados', datos: $datosNivel1);

        return view('livewire.admin.reportes', [
            'edades'             => \App\Models\Edad::orderBy('orden')->get(),
            'sexos'              => \App\Models\Sexo::orderBy('orden')->get(),
            'cargos'             => \App\Models\Cargo::orderBy('orden')->get(),
            'lugares'            => \App\Models\LugarTrabajo::orderBy('orden')->get(),
            'grados'             => \App\Models\GradoAcademico::orderBy('orden')->get(),
            'antiguedades'       => \App\Models\Antiguedad::orderBy('orden')->get(),
            'empresas'           => $user->role === 'super_admin' ? Empresa::orderBy('nombre')->get() : collect(),
            'dimensionActiva'    => $this->dimensionActivaId ? Dimension::find($this->dimensionActivaId) : null,
            'subdimensionActiva' => $this->subdimensionActivaId ? Subdimension::find($this->subdimensionActivaId) : null,
            'datosNivel1'        => $datosNivel1,
            'datosNivel2'        => $this->nivel === 2 ? $this->getDatosNivel2() : [],
        ]);
    }
}
