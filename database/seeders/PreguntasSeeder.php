<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreguntasSeeder extends Seeder
{
    public function run(): void
    {
        $comunicacion = DB::table('subdimensiones')->where('nombre', 'Comunicación')->value('id');
        $capacidad = DB::table('subdimensiones')->where('nombre', 'Capacidad')->value('id');
        $integridad = DB::table('subdimensiones')->where('nombre', 'Integridad')->value('id');
        $apoyo = DB::table('subdimensiones')->where('nombre', 'Apoyo')->value('id');
        $valoracion = DB::table('subdimensiones')->where('nombre', 'Valoración')->value('id');
        $colaboracion = DB::table('subdimensiones')->where('nombre', 'Colaboración')->value('id');
        $equidad = DB::table('subdimensiones')->where('nombre', 'Equidad')->value('id');
        $ausenciaFavoritismo = DB::table('subdimensiones')->where('nombre', 'Ausencia de favoritismo')->value('id');
        $justicia = DB::table('subdimensiones')->where('nombre', 'Justicia')->value('id');
        $delEquipo = DB::table('subdimensiones')->where('nombre', 'Del Equipo')->value('id');
        $delTrabajo = DB::table('subdimensiones')->where('nombre', 'Del Trabajo')->value('id');
        $delaEmpresa = DB::table('subdimensiones')->where('nombre', 'De la Empresa')->value('id');
        $hospitalidad = DB::table('subdimensiones')->where('nombre', 'Hospitalidad')->value('id');
        $cercania = DB::table('subdimensiones')->where('nombre', 'Cercanía')->value('id');
        $sentidoFamilia = DB::table('subdimensiones')->where('nombre', 'Sentido de Familia')->value('id');
        $seguridad = DB::table('subdimensiones')->where('nombre', 'Seguridad')->value('id');
        $capacitacion = DB::table('subdimensiones')->where('nombre', 'Capacitación')->value('id');

        DB::table('preguntas')->insertOrIgnore([
            // Comunicación (3)
            ['subdimension_id' => $comunicacion, 'texto' => '¿Los jefes comunican claramente sus expectativas?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $comunicacion, 'texto' => '¿Mis jefes son accesibles y responden con claridad a mis dudas o inquietudes?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $comunicacion, 'texto' => '¿Los jefes me mantienen informado acerca de asuntos y cambios importantes?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],

            // Capacidad (7)
            ['subdimension_id' => $capacidad, 'texto' => '¿Los jefes confían que los colaboradores hacen un buen trabajo sin supervisarlos continuamente?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $capacidad, 'texto' => '¿Los jefes asignan y coordinan bien al personal?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $capacidad, 'texto' => '¿La gente conoce los objetivos y metas en su trabajo?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $capacidad, 'texto' => '¿Los jefes manejan el negocio de forma competente?', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $capacidad, 'texto' => '¿Los jefes contratan gente de acuerdo a la cultura de la empresa?', 'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $capacidad, 'texto' => '¿Los jefes promueven el trabajo en equipo?', 'orden' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $capacidad, 'texto' => '¿A la gente le delegan responsabilidades?', 'orden' => 7, 'created_at' => now(), 'updated_at' => now()],

            // Integridad (5)
            ['subdimension_id' => $integridad, 'texto' => '¿Los jefes dirigen el negocio de una manera honesta y ética?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $integridad, 'texto' => '¿Los valores de la empresa son practicados por todos?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $integridad, 'texto' => '¿Los jefes cumplen sus promesas?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $integridad, 'texto' => '¿Las palabras de los jefes coinciden con sus acciones?', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $integridad, 'texto' => '¿Crees que la empresa despediría a las personas como última opción?', 'orden' => 5, 'created_at' => now(), 'updated_at' => now()],

            // Apoyo (4)
            ['subdimension_id' => $apoyo, 'texto' => '¿Me dan los recursos y herramientas necesarias para hacer mi trabajo?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $apoyo, 'texto' => '¿Siento que mi esfuerzo y el de mis compañeros es valorado y reconocido por mis jefes?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $apoyo, 'texto' => '¿Los jefes reconocen que pueden cometerse errores involuntarios al hacer el trabajo?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $apoyo, 'texto' => '¿En esta empresa existen buenas oportunidades de crecimiento?', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],

            // Valoración (6)
            ['subdimension_id' => $valoracion, 'texto' => '¿Este es un lugar psicológica y emocionalmente saludable para trabajar?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $valoracion, 'texto' => '¿Las instalaciones contribuyen a un buen ambiente de trabajo?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $valoracion, 'texto' => '¿A las personas se les anima a que equilibren su vida laboral y su vida personal?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $valoracion, 'texto' => '¿Tenemos beneficios especiales y únicos en esta empresa?', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $valoracion, 'texto' => '¿Los jefes demuestran su interés sincero en mí como persona, no solo como empleado?', 'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $valoracion, 'texto' => '¿Cuando es necesario puedo ausentarme para atender asuntos personales durante el horario de trabajo?', 'orden' => 6, 'created_at' => now(), 'updated_at' => now()],

            // Colaboración (2)
            ['subdimension_id' => $colaboracion, 'texto' => '¿Los jefes involucran a la gente en decisiones que afecten su trabajo o su ambiente laboral?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $colaboracion, 'texto' => '¿Los jefes fomentan y responden genuinamente a nuestras sugerencias e ideas?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],

            // Equidad (5)
            ['subdimension_id' => $equidad, 'texto' => '¿En esta empresa todos tenemos igualdad de oportunidades para recibir menciones o premios especiales?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $equidad, 'texto' => '¿A los colaboradores se les paga justamente por el trabajo que realizan?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $equidad, 'texto' => '¿Siento que recibo una parte justa de las ganancias que obtiene la empresa?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $equidad, 'texto' => '¿Me tratan bien independientemente de mi posición en la empresa?', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $equidad, 'texto' => '¿Se evalúa el desempeño de los colaboradores de manera justa?', 'orden' => 5, 'created_at' => now(), 'updated_at' => now()],

            // Ausencia de favoritismo (3)
            ['subdimension_id' => $ausenciaFavoritismo, 'texto' => '¿Los jefes evitan tener empleados favoritos?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $ausenciaFavoritismo, 'texto' => '¿Los ascensos se dan a quienes más lo merecen?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $ausenciaFavoritismo, 'texto' => '¿Los colaboradores evitan hacer "grilla" para obtener un beneficio personal?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],

            // Justicia (4)
            ['subdimension_id' => $justicia, 'texto' => '¿La gente es tratada justamente sin importar su edad?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $justicia, 'texto' => '¿La gente es tratada justamente sin importar su sexo?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $justicia, 'texto' => '¿La gente es tratada justamente sin importar su preferencia sexual?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $justicia, 'texto' => '¿Si soy tratado injustamente, sé que tendré oportunidad de ser escuchado y recibir un trato justo?', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],

            // Del Equipo (2)
            ['subdimension_id' => $delEquipo, 'texto' => '¿Los colaboradores están dispuestos a hacer un esfuerzo extra para realizar el trabajo?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $delEquipo, 'texto' => '¿Cuando veo lo que logramos, me siento orgulloso?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],

            // Del Trabajo (2)
            ['subdimension_id' => $delTrabajo, 'texto' => '¿Mi trabajo tiene un significado especial; "Para mí este no solo es un trabajo"?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $delTrabajo, 'texto' => '¿Siento que mi participación hace una diferencia en la organización?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],

            // De la Empresa (3)
            ['subdimension_id' => $delaEmpresa, 'texto' => '¿Me siento bien por la forma en la que contribuimos a la sociedad?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $delaEmpresa, 'texto' => '¿Estoy orgulloso de decirle a otros que trabajo aquí?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $delaEmpresa, 'texto' => '¿Deseo trabajar aquí por un largo tiempo?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],

            // Hospitalidad (1)
            ['subdimension_id' => $hospitalidad, 'texto' => '¿Considero que este es un lugar amigable donde disfruto venir a trabajar?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Cercanía (3)
            ['subdimension_id' => $cercania, 'texto' => '¿Puedo ser yo mismo aquí?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $cercania, 'texto' => '¿Aquí las personas se preocupan por los demás?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $cercania, 'texto' => '¿Aquí las personas celebran eventos especiales?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],

            // Sentido de Familia (4)
            ['subdimension_id' => $sentidoFamilia, 'texto' => '¿Puedo contar con la ayuda de las personas?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $sentidoFamilia, 'texto' => '¿Aquí hay un sentido de familia o equipo?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $sentidoFamilia, 'texto' => '¿Estamos todos juntos en esto?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $sentidoFamilia, 'texto' => '¿Tomando en consideración todas las preguntas, yo diría que este es un lugar excelente para trabajar?', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],

            // Seguridad (8)
            ['subdimension_id' => $seguridad, 'texto' => '¿Considero que la empresa cuenta con programas efectivos para prevenir riesgos y que mi entorno laboral y tareas son físicamente seguros?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $seguridad, 'texto' => '¿La tarea que tengo asignada tiene riesgos de accidentes?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $seguridad, 'texto' => '¿Tu trabajo exige que estés muy concentrado?', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $seguridad, 'texto' => '¿Tu trabajo exige que atiendas varios asuntos a la vez?', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $seguridad, 'texto' => '¿Por la cantidad de trabajo que tienes debes trabajar sin parar?', 'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $seguridad, 'texto' => '¿Consideras que es necesario mantener un ritmo de trabajo acelerado?', 'orden' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $seguridad, 'texto' => '¿Tengo la impresión que mi vida se va cortando?', 'orden' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $seguridad, 'texto' => '¿Empiezo a sentir que mi existencia pierde sentido, y esa sensación me pesa cada día un poco más?', 'orden' => 8, 'created_at' => now(), 'updated_at' => now()],

            // Capacitación (2)
            ['subdimension_id' => $capacitacion, 'texto' => '¿Recibo la capacitación necesaria y planificada para realizar bien mi trabajo y crecer profesionalmente?', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['subdimension_id' => $capacitacion, 'texto' => '¿El programa de inducción y acondicionamiento para realizar el trabajo es adecuado?', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('preguntas')
            ->where('subdimension_id', $seguridad)
            ->whereIn('orden', [2, 3, 4, 5, 6, 7, 8])
            ->update(['invertida' => true]);
    }
}
