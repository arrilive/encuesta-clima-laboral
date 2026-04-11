# Encuesta de Clima Laboral

![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Livewire 3](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Tests](https://img.shields.io/badge/tests-110%20passing-brightgreen?style=for-the-badge)

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
* **Tokens 100% anónimos:** No hay registro, emails ni contraseñas. Cada persona recibe un token aleatorio, lo que garantiza que nadie pueda atar los resultados a una identidad.
* **Formulario demográfico:** Los empleados pueden dejar su antigüedad, cargo o rango de edad al inicio. Esto es vital para que Recursos Humanos pueda segmentar luego (ej: "las personas de 5+ años de antigüedad evalúan peor el liderazgo").
* **Métricas claras:** Evaluamos 6 grandes dimensiones con 64 preguntas cerradas, rematando con 3 preguntas de desarrollo para contexto extra.
* **Fácil de pausar y retomar:** Las respuestas se guardan en tiempo real. Si a mitad del cuestionario la persona debe cerrar la app móvil porque entró a una junta, puede usar su token en la PC de escritorio más tarde y retomar exactamente donde se quedó.

### Para el administrador (Panel de Análisis)
* **Dashboard en vivo:** Métricas de operación para ver si se están alcanzando las cuotas esperadas. Monitorea cuántas encuestas se cerraron y te alerta sobre tokens "en riesgo" de quedarse en el olvido porque llevan días inactivos.
* **Reporte Drill-down (3 niveles):** Vas de lo macro a lo micro. Un gráfico radar te da la foto de las 6 Dimensiones; si haces clic, bajas a ver el detalle en barras con semáforo por Subdimensión; y de ahí, llegas a la métrica pregunta por pregunta.
* **Filtros cruzados:** Puedes mezclar hasta 6 filtros demográficos distintos para aislar segmentos críticos en tus análisis.
* **Comparativas directas:** Evaluaciones cara a cara para responder cosas como: ¿Cómo nos perciben operarios vs. gerentes?
* **Exportación amigable:** Un reporte general listo en PDF o si lo prefieres, una tabla CSV pesada para meter a Excel.
* **Lógica B2B:** Soporta múltiples corporaciones. Como super admin creas a las empresas y sus administradores. Generas tokens por volumen y se los asignas a quien corresponda.

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
| --- | --- |
| **`super_admin`** | Es el dueño del ecosistema. Puede crear y editar a las entidades cliente (Empresas), controlar sus contraseñas semilla y monitorear todos los ecosistemas sin limitación de scopes de tenant. |
| **`admin_empresa`** | Es el departamento de RRHH de una compañía específica. Solo interactúa con el ecosistema de su gente. Visualiza reportes, crea/libera tokens para sus propios empleados, y no puede ni ver de reojo otros espacios de trabajo por un Scoped model global seguro. |

## Testing Integrados

La suite de tests que he armado con PestPHP cubre 110 escenarios que respaldan cada cálculo del clima y comportamiento del panel. 

Si bajas el repositorio, haz el intento de correr la suite:
```bash
php artisan test
```

Y para comprobar que todo sigue estrictamente testeado con la lógica a prueba de fallos mínima del 80%:
```bash
php artisan test --coverage --min=80
```

## 4 Decisiones Técnicas que valen la pena destacar

1. **Privacidad by-design:** Autenticar empleados con contraseña destruía la posibilidad del 100% libre anonimato. La solución de usar batch-tokens generados de antemano eliminó esa fricción e impide vincular a nadie con las identidades reales del Active Directory de una empresa real.
2. **Scoring que todo el mundo entiende:** Las encuestas contestan sobre Likert (Ej: del 1 al 3). Matemáticamente transformé esto en backend a escalas directas de 0 al 100 puntos y monté una clasificación de semáforo simple de leer: Regular entre 25 y 50 puntos, Buen Clima entre 51 y 79, o Excelente para notas de 80 puntos en adelante. Por debajo de 25 es Deficiente.
3. **Optimización con GROUP BY en bases de datos:** Empujar todo el peso de sacar el clima laboral directo a la base de datos evadiendo a Eloquent resolviendo promedios grandes mejoró la escalabilidad y salvó picos locos de RAM cuando filtramos por 3 ó 4 datos demográficos. 
4. **Arquitectura responsiva sin pesadez SPA:** Mantuvimos el front con Livewire para acelerar los despachos asíncronos y refrescar los tableros al vuelo en el navegador simulando el flujo de una Single Page App muy bien armada, pero consumiendo poquísimo peso en Javascript total.

## Conecta conmigo

**Luis Vera**
* **LinkedIn:** [https://www.linkedin.com/in/luis-vera-430b38284/](https://www.linkedin.com/in/luis-vera-430b38284/)
* **GitHub:** [https://github.com/arrilive](https://github.com/arrilive)
