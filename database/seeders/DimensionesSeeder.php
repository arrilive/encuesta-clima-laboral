<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DimensionesSeeder extends Seeder
{
    public function run(): void
    {
        $dimensiones = [
            [
                'nombre' => 'Credibilidad',
                'descripcion' => 'Evalúa si los líderes de la empresa comunican claramente hacia dónde va la organización, si sus acciones son consistentes con lo que dicen y si la información que comparten es honesta y transparente. Un ambiente de alta credibilidad es uno donde los colaboradores confían en que la dirección toma decisiones con integridad.',
                'orden' => 1,
            ],
            [
                'nombre' => 'Respeto',
                'descripcion' => 'Mide si la empresa apoya el desarrollo profesional de sus colaboradores, reconoce su esfuerzo, los involucra en decisiones que les afectan y les permite equilibrar su vida laboral y personal. El respeto se expresa en el día a día: en cómo se da retroalimentación, cómo se distribuyen los recursos y cómo se valora el tiempo de cada persona.',
                'orden' => 2,
            ],
            [
                'nombre' => 'Imparcialidad',
                'descripcion' => 'Evalúa si el trato en la empresa es justo y consistente para todas las personas, independientemente de su género, edad, cargo o cualquier otra característica personal. Incluye la percepción sobre si los ascensos, reconocimientos y oportunidades se otorgan con base en el mérito y no en favoritismos.',
                'orden' => 3,
            ],
            [
                'nombre' => 'Orgullo',
                'descripcion' => 'Mide el vínculo emocional que cada colaborador siente con su trabajo, con su equipo y con la empresa en general. Refleja si las personas sienten que su trabajo tiene un propósito, si se enorgullecen de pertenecer a la organización y si recomendarían trabajar aquí a alguien cercano.',
                'orden' => 4,
            ],
            [
                'nombre' => 'Compañerismo',
                'descripcion' => 'Evalúa la calidad de las relaciones entre los miembros del equipo: si existe un ambiente de apoyo mutuo, si se puede ser uno mismo en el trabajo, si se celebran los logros colectivos y si hay un sentido genuino de comunidad dentro de la organización.',
                'orden' => 5,
            ],
            [
                'nombre' => 'Seguridad y Capacitación',
                'descripcion' => 'Mide si la empresa proporciona las condiciones físicas y emocionales necesarias para trabajar con tranquilidad, así como las herramientas y formación para crecer profesionalmente. Incluye la percepción sobre si el entorno de trabajo es seguro, si se cuenta con el equipo adecuado y si la empresa invierte en el desarrollo de sus colaboradores.',
                'orden' => 6,
            ],
        ];

        foreach ($dimensiones as $dimension) {
            DB::table('dimensiones')->upsert(
                array_merge($dimension, ['created_at' => now(), 'updated_at' => now()]),
                ['orden'],
                ['descripcion', 'updated_at'],
            );
        }
    }
}
