# Encuesta de Clima Laboral

![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Livewire 3](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Tests](https://img.shields.io/badge/tests-190%20passing-brightgreen?style=for-the-badge)

## ¿De qué trata este proyecto?

**Encuesta de Clima Laboral** es una aplicación web para que las empresas gestionen sus encuestas de ambiente corporativo de forma 100% anónima.

Nació para solucionar un problema muy típico de Recursos Humanos: conseguir que los equipos den feedback real y honesto sobre el liderazgo y la empresa sin tener miedo a represalias. Creamos un entorno seguro e inrastreable para el empleado, y del otro lado, proveemos a los directivos de un panel administrativo con datos, gráficos y cruces demográficos listos para tomar decisiones informadas.

## Stack Tecnológico

| Tecnología | Rol en el proyecto |
| --- | --- |
| **Laravel 12** | Corazón del backend y lógica de negocio. |
| **Livewire 3** | Framework para hacer la UI dinámica y reactiva sin salir de PHP. |
| **Alpine.js** | Micro-interacciones y manipulación del DOM en el navegador. |
| **ApexCharts** | Gráficas analíticas modernas. |
| **TailwindCSS 3** | Estilos, diseño responsivo y customización rápida. |
| **MariaDB** | La base de datos relacional. |
| **PHP 8.4** | Lenguaje principal del stack. |
| **Pest** | Suite de testing automatizado (tests súper legibles). |
| **Laravel Pint** | Linter/Formatter para mantener el código limpio y estandarizado. |

## Funcionalidades Core

### Para el empleado (Flujo de la Encuesta)
* **Verificación OTP y anonimato garantizado:** El empleado valida su número de WhatsApp con un código de un solo uso (6 dígitos, máximo 3 intentos, expira en 10 minutos). El número nunca se almacena en texto plano de forma permanente: se calcula un hash SHA-256 irreversible para controlar que nadie participe dos veces en el mismo lote, y los registros temporales se eliminan automáticamente cada hora. En ningún punto del sistema existe un vínculo entre la identidad del empleado y sus respuestas.
* **Formulario demográfico:** Los empleados pueden dejar su antigüedad, cargo o rango de edad al inicio. Esto es vital para que Recursos Humanos pueda segmentar luego (ej: "las personas de 5+ años de antigüedad evalúan peor el liderazgo").
* **Métricas claras:** Evaluamos 6 grandes dimensiones con 64 preguntas cerradas, rematando con 3 preguntas de desarrollo para contexto extra.
* **Fácil de pausar y retomar:** Las respuestas se guardan en tiempo real. Si a mitad del cuestionario la persona debe cerrar la app móvil porque entró a una junta, puede usar su token en la PC de escritorio más tarde y retomar exactamente donde se quedó.

### Para el administrador (Panel de Análisis)
* **Dashboard en vivo:** Métricas de operación para ver si se están alcanzando las cuotas esperadas. Monitorea cuántas encuestas se cerraron y te alerta sobre tokens "en riesgo" de quedarse en el olvido porque llevan días inactivos.
* **Reporte Drill-down (3 niveles):** Vas de lo macro a lo micro. Un gráfico radar te da la foto de las 6 Dimensiones; si haces clic, bajas a ver el detalle en barras con semáforo por Subdimensión; y de ahí, llegas a la métrica pregunta por pregunta.
* **Filtros cruzados:** Puedes mezclar hasta 6 filtros demográficos distintos para aislar segmentos críticos en tus análisis.
* **Comparativas directas:** Evaluaciones cara a cara para responder cosas como: ¿Cómo nos perciben operarios vs. gerentes?
* **Exportación amigable:** Un reporte general listo en PDF.
* **Jerarquía organizacional multitenant:** El sistema modela estructuras reales con tres niveles: Corporativo → Empresa → Sucursal. Cada nivel es opcional — una empresa pequeña puede operar sin corporativo ni sucursales, mientras que un grupo empresarial puede tener múltiples empresas con múltiples sedes, cada una con sus propios resultados y llave de acceso.

## Arquitectura detrás de cámara

Aprovechamos el stack TALL, enfocándonos fuertemente en el rendimiento y la separación de responsabilidades:

* **Pura reactividad con Livewire:** Mantuve la aplicación ligera delegando componentes pesados de gráficas a módulos Livewire que se comunican transparentemente a través de atributos `#[Reactive]` y event dispatching.
* **Controladores que solo controlan (`ClimaScoringService`):** Toda la lógica matemática, los promedios y conversiones de escalas Likert las extraje completamente a clases de Servicio. El código de mapeo quedó limpio y es un placer hacerle unit testing.
* **SQL en vez de colecciones pesadas:** Uno de los problemas más clásicos acá era el (N+1) iterando sobre las filas traídas por el ORM para sacar los promedios. Refactoricé agresivamente esos cuellos de botella con `JOIN` y `GROUP BY`, sacando los `AVG` (promedios) directo del motor relacional de MariaDB, logrando cargas analíticas en microsegundos.

## Instalación

Poner a rodar este repo en local te tomará solo un par de minutos, si ya tienes PHP 8.4, Composer, MariaDB, Node y tu entorno corriendo:

1. Primero lo de siempre, clona el proyecto:
   ```bash
   git clone https://github.com/arrilive/encuesta-clima-laboral.git
   cd encuesta-clima-laboral
   ```
2. Instala todos los vendors y dependencias de NPM:
   ```bash
   composer install
   npm install
   ```
3. Clona tu `.env` inicial y prende la App Key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *(Pausa: Abre tu archivo `.env` y enlaza el acceso a tu base de datos local en las variables `DB_*`).*
4. Dinos cómo se ve armando la estructura y los datos dummy para jugar:
   ```bash
   php artisan migrate --seed
   ```
5. ¡Listo! Arranca el servidor de Laravel (y la integración con Vite en otra consola):
   ```bash
   php artisan serve
   ```
   *(No olvides correr un `npm run dev` en paralelo para compilar TailwindCSS a medida que mueves cosas).*

## El sistema de Roles

| Rol | ¿Qué puede hacer en la app? |
|---|---|
| **`super_admin`** | Dueño del ecosistema. Crea y gestiona corporativos, empresas y sucursales. Genera tokens por volumen y los asigna a cada lote. Acceso total sin restricción de scope. |
| **`admin_corporativo`** | Visibilidad agregada de todas las empresas y sucursales bajo su corporativo. Puede comparar resultados entre empresas. No genera tokens. |
| **`admin_empresa`** | El departamento de RRHH de una empresa específica. Visualiza reportes de su empresa y sus sucursales. Scoped globalmente — no puede ver otros espacios de trabajo. |
| **`admin_sucursal`** | Restringido a los reportes de su sucursal específica. Ideal para empresas con múltiples sedes que quieren gestión granular. |

## Testing Integrados

La suite de tests que he armado con PestPHP cubre 190 escenarios que respaldan cada cálculo del clima y comportamiento del panel. 

Si bajas el repositorio, haz el intento de correr la suite:
```bash
php artisan test
```

## 5 Decisiones Técnicas que valen la pena destacar

1. **Privacidad by-design con OTP y hash unidireccional:** La verificación por número de WhatsApp garantiza que cada empleado participe una sola vez por lote, sin comprometer el anonimato. El número nunca se vincula a las respuestas: un hash SHA-256 salteado registra la participación, y el número desaparece de la base de datos en cuanto se valida el OTP. Un job programado limpia automáticamente los registros temporales cada hora.
2. **Scoring que todo el mundo entiende:** Las encuestas contestan sobre Likert (Ej: del 1 al 3). Matemáticamente transformé esto en backend a escalas directas de 0 al 100 puntos y monté una clasificación de semáforo simple de leer: En atención entre 45 y 59 puntos, Buen Clima entre 60 y 74, o Excelente desde 75 puntos en adelante. Por debajo de 45 es En riesgo.
3. **Optimización con GROUP BY en bases de datos:** Empujar todo el peso de sacar el clima laboral directo a la base de datos evadiendo a Eloquent resolviendo promedios grandes mejoró la escalabilidad y salvó picos locos de RAM cuando filtramos por 3 ó 4 datos demográficos. 
4. **Arquitectura responsiva sin pesadez SPA:** Mantuvimos el front con Livewire para acelerar los despachos asíncronos y refrescar los tableros al vuelo en el navegador simulando el flujo de una Single Page App muy bien armada, pero consumiendo poquísimo peso en Javascript total.
5. **Mantenimiento de `email_verified_at` como deuda técnica:** La columna `email_verified_at` en la tabla de usuarios (`users`) es una herencia del scaffolding de Laravel Breeze. Dado que el sistema no utiliza la verificación de correo electrónico en ningún flujo y no es crítico removerla, se mantiene en la base de datos y modelo de forma documentada como deuda técnica aceptada, evitando romper la compatibilidad con las vistas y las pruebas de Breeze.

## Privacidad y Anonimato

El sistema fue diseñado desde cero para que sea técnicamente imposible rastrear qué persona respondió qué en la encuesta. A continuación explicamos cómo funciona y qué datos se manejan en cada paso.

### ¿Cómo se garantiza el anonimato?

El flujo de verificación está diseñado para que el número de teléfono del empleado **nunca quede vinculado a sus respuestas**:

1. El empleado ingresa su número de WhatsApp para recibir un código de verificación (OTP).
2. El sistema calcula una huella digital irreversible (hash SHA-256) del número combinado con un código secreto del servidor — esta huella solo sirve para verificar que la persona no haya participado antes en ese lote.
3. Una vez validado el OTP, el número de teléfono se elimina permanentemente de la base de datos. Lo que queda es únicamente la huella digital anónima.
4. El empleado recibe un token de acceso (ej. `TK-A3F9-2K81`) que es su única identidad en el sistema. Nadie — ni el administrador, ni el sistema — puede reconstruir qué número generó ese token.

### ¿Qué datos se almacenan y por cuánto tiempo?

| Dato | ¿Se almacena? | ¿Por cuánto tiempo? |
|---|---|---|
| Número de teléfono | Solo durante la verificación OTP | Máximo 10 minutos |
| Código OTP | Solo el hash (nunca el código real) | Máximo 10 minutos |
| Huella del número (hash) | Sí, anónima | Hasta que el lote expira |
| Token de acceso | Sí | Mientras el lote esté activo |
| Respuestas a preguntas | Sí, sin nombre ni identidad | Indefinidamente (datos históricos) |
| Datos demográficos | Sí, sin nombre ni identidad | Indefinidamente (datos históricos) |

### Umbrales de protección

Para evitar que los filtros demográficos revelen identidades en grupos pequeños, el sistema bloquea los resultados cuando el segmento tiene menos participantes del mínimo requerido:

- **Reportes numéricos:** se necesitan al menos **5 respuestas** para mostrar puntajes y gráficas.
- **Comentarios abiertos:** se necesitan al menos **10 respuestas** para mostrar las respuestas de texto libre.

Cuando no se alcanza el umbral, el sistema muestra un mensaje de privacidad en lugar de los resultados.

### Riesgos residuales aceptados

Ningún sistema es 100% hermético. Los siguientes riesgos han sido identificados y conscientemente aceptados dado el perfil de uso del sistema:

- **Correlación temporal:** un administrador con acceso a los logs del servidor podría correlacionar el momento en que se generó un token con la hora de llegada de un OTP. Mitigación: los logs de servidor no son accesibles desde el panel administrativo.
- **Correlación de comentarios abiertos:** el estilo de redacción en preguntas abiertas puede revelar la autoría a lectores con conocimiento del equipo. El umbral de 10 respuestas reduce este riesgo pero no lo elimina.
- **SIM virtual:** el control de unicidad se basa en el número de teléfono. Un actor malintencionado con acceso a múltiples SIMs virtuales podría participar más de una vez. El costo y esfuerzo de este fraude es suficientemente alto para el contexto de uso del sistema.

## Conecta conmigo

**Luis Vera**
* **LinkedIn:** [https://www.linkedin.com/in/luis-vera-430b38284/](https://www.linkedin.com/in/luis-vera-430b38284/)
* **GitHub:** [https://github.com/arrilive](https://github.com/arrilive)
