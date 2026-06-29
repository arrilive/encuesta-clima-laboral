# Registro de Cambios

Todos los cambios notables de este proyecto se documentan en este archivo.

El formato sigue el estándar [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere al [Versionado Semántico](https://semver.org/lang/es/).

---

## [Sin publicar]

## [1.2.1] — 2026-06-27

> **Jerarquía tipográfica global, optimización de ranking y actualización del seeder de demo**

### Cambiado

- **Tipografía (admin):** Se aplica la fuente DM Sans y jerarquía tipográfica consistente en el layout de administración (`style(admin)`).
- **Tipografía (encuesta):** Se normalizan tamaños hardcodeados a utilidades de Tailwind en las vistas de la encuesta (`style(encuesta)`).
- **Tipografía (reportes):** Se aplica jerarquía tipográfica y se mejoran las etiquetas del gráfico de radar en el módulo de reportes (`style(reportes)`).
- **Seeder de demo:** Se actualiza el formato de tokens de prueba al esquema `TK-XXXX-XXXX` (`chore(seeder)`).

### Corregido

- **Rendimiento (dashboard):** Se optimiza `calcularRanking()` reemplazando la iteración por empresa con una consulta agrupada por lotes, eliminando un N+1 (`perf(dashboard)`).

---

## [1.2.0] — 2026-06-20

> **Jerarquía corporativa completa, filtros en cascada, umbral de anonimato y auditoría de seguridad y privacidad**

### Agregado

- **CRUD de corporativos, sucursales y admins:** El `super_admin` puede ahora crear, editar y eliminar corporativos, sucursales y sus administradores desde el panel de administración.
- **Filtros en cascada (reportes y dashboard):** Se pueden filtrar resultados por corporativo, sucursal y lote de forma encadenada, tanto en el dashboard como en reportes.
- **Filtro por lote:** Permite aislar períodos de medición en dashboard y reportes filtrando por lote específico.
- **Lotes de sucursal:** El componente `GenerarTokens` se extiende para soportar lotes asociados a sucursales.
- **Modo B en generación de tokens:** Nueva opción para inyectar tokens en un lote ya existente sin crear uno nuevo.
- **Umbral de anonimato:** Se implementa umbral mínimo de participantes en reportes y respuestas abiertas para proteger la identidad de los respondientes.
- **Centralización de umbrales de clima:** Los umbrales de clasificación de clima se centralizan en `ClimaBadge` y la escala se actualiza para mayor consistencia.
- **Combobox con búsqueda reactiva:** Se reemplaza el selector estático de entidad por un combobox con búsqueda en tiempo real.
- **Filtros de jerarquía en tabla de encuestas:** Se añaden filtros encadenados de corporativo/sucursal/empresa en `EncuestasTable`.
- **Modal de descripción de dimensión:** Se agrega un modal informativo con descripción contextual de cada dimensión durante la encuesta.
- **Documentación de privacidad:** Se documentan en README los pilares de anonimato, umbrales aplicados y riesgos aceptados.

### Cambiado

- **Exportación CSV eliminada:** Se elimina el botón de exportación CSV del panel de administración; se agrega en su lugar un botón de limpiar filtros.
- **Roles extraídos a enum:** Los roles del sistema se extraen a un enum dedicado y los valores de negocio se mueven a `config/encuesta.php` para centralizar configuración.
- **Naming y dead code:** Se corrigen nombres de métodos y variables, y se elimina código muerto detectado en auditoría.

### Corregido

- **Seguridad — 4 vulnerabilidades:** Corrección de cuatro vulnerabilidades detectadas en auditoría de seguridad.
- **Seguridad — aislamiento de tenant:** Se implementa aislamiento estricto de tenant para los roles `admin_corporativo` y `admin_sucursal`, impidiendo acceso cruzado entre entidades.
- **Seguridad — restricción de tokens:** La generación de tokens queda restringida exclusivamente al rol `super_admin`.
- **Privacidad — 3 problemas:** Corrección de tres issues de privacidad detectados en auditoría (filtración de datos entre tenants).
- **UX/UI — 13 problemas:** Corrección de trece issues de accesibilidad, interfaz de usuario y experiencia detectados en auditoría.
- **Rendimiento — N+1 en encuesta y reportes nivel 3:** Se eliminan consultas N+1 en el flujo de encuesta y en el tercer nivel de reportes.
- **Admin — `withCount` residual:** Se reemplaza `withCount` legacy por subqueries en `EmpresasTable`.
- **Bugs de auditoría — 4 correcciones:** Corrección de cuatro bugs en autenticación, exportación e interfaz detectados en auditoría de código.

---

## [1.1.0] — 2026-06-07

> **Flujo OTP, jerarquía corporativa en base de datos, CI/CD con GitHub Actions y mejoras de robustez**

### Agregado

- **Flujo OTP completo:** Se reemplaza el flujo de acceso v1.0 por verificación en dos pasos: solicitud de OTP por teléfono (`solicitarOtp`) y verificación (`verificarOtp`), con modal de confirmación y endpoint `verificarLlave`.
- **Limpieza de OTPs expiradas:** Nuevo comando artisan `otp:limpiar-expirados` registrado en el scheduler para purga automática de OTPs caducas.
- **Tablas de soporte OTP:** Nuevas tablas `encuesta_hashes_usados` y `otp_verificaciones` con sus modelos Eloquent (`EncuestaHash`, `OtpVerificacion`) y factories.
- **Jerarquía corporativa en BD:** Creación de tablas `corporativos`, `sucursales` y extensión de `empresas` con `corporativo_id`; los usuarios reciben `corporativo_id` y `sucursal_id`.
- **Roles extendidos:** El enum de roles se extiende con `admin_corporativo` y `admin_sucursal`; el middleware de administración y las pruebas de autorización se actualizan.
- **Vigencia de lotes:** Se expone el campo de vigencia en el formulario de generación de tokens; se mantiene compatibilidad con lotes legacy sin fecha.
- **CI/CD con GitHub Actions:** Pipeline de integración continua configurado para ejecutar tests y linting en cada push. Se actualiza Node.js a v24 y se incluye build de assets.
- **Credenciales de admin en `.env.example`:** Se documentan las credenciales por defecto del administrador en el archivo de ejemplo.

### Cambiado

- **Modelo `Encuesta` refactorizado:** Se elimina la relación directa `empresa()` y el campo `empresa_id` de encuestas; las encuestas se asocian únicamente a través del `lote_id`.
- **Tabla `token_lotes` renombrada a `lotes`:** Renombrado y extensión de estructura para soportar la jerarquía corporativa.
- **Consulta `verificarLlave` optimizada:** Se reemplaza la búsqueda de clave por `whereHas` sobre lotes vigentes.
- **`UserFactory` extendida:** Se añaden estados `adminCorporativo` y `adminSucursal` para facilitar pruebas de los nuevos roles.
- **`DemoSeeder` actualizado:** Se adapta a la arquitectura v1.1 y se sincroniza con las variables de entorno de `phpunit.xml`.

### Corregido

- **Validación de teléfono:** Se añade validación inline del número de teléfono antes de solicitar OTP, con sanitización en tiempo real vía `x-on:input`.
- **Locale de validación:** Se fija el locale a español en la validación de tokens, con mensajes de fecha personalizados.
- **FK `lote_id` en encuestas:** Se cambia `nullOnDelete` a `restrictOnDelete` para prevenir eliminación accidental de lotes con encuestas asociadas.
- **Transacción en generación de tokens:** La creación de lote e inserción de tokens se envuelve en `DB::transaction` para garantizar atomicidad.
- **Referencias residuales post-migración:** Se corrigen referencias legacy a `empresa_id` en encuestas y relaciones inconsistentes en listados.
- **Tokens para empresas inactivas:** Se bloquea la generación de tokens cuando la empresa destino está marcada como inactiva.
- **Dashboard — correcciones visuales:** Se corrige el género en la etiqueta "Completados", se simplifica el widget de tokens, se refactoriza el widget de promedio a layout horizontal, y se reemplaza el logo de Laravel por el logo del proyecto en la pantalla de login.

### Eliminado

- **Campo `empresa_id` de `encuestas`:** Eliminado de modelo y migración; la asociación ahora es exclusivamente por lote.

---

## [1.0.1] — 2026-04-12

> **Correcciones en scoring de dimensiones, cálculo de participación y UX de paginación**

### Corregido

- **Scoring de dimensiones:** El promedio de dimensión se calcula como promedio no ponderado de subdimensiones en lugar de un `AVG` directo sobre respuestas brutas, corrigiendo resultados inconsistentes (`fix(scoring)`).
- **Cálculo de participación en reportes:** Se reemplaza `completadasTotal` por `totalTokens` como denominador del porcentaje de participación, reflejando correctamente el universo total de encuestados (`fix(reportes)`).
- **Auto-scroll en paginación:** Se deshabilita el desplazamiento automático al paginar en la tabla de respuestas abiertas, evitando saltos de pantalla inesperados (`fix(reportes)`).

### Cambiado

- **Formato numérico estandarizado:** Se unifica la presentación de puntuaciones a 1 decimal en todas las vistas; se corrige la persistencia del formateador en gráficos ApexCharts (`style(views)`).

---

## [1.0.0] — 2026-04-11

> **Primera versión estable — plataforma completa de encuesta de clima laboral**

### Agregado

- **Encuesta pública con flujo completo:** Acceso por contraseña/token, formulario demográfico con auto-guardado (Livewire), bloques de preguntas por dimensión con barra de progreso, validación de preguntas sin responder y animaciones de tarjeta.
- **67 preguntas validadas:** Extracción y validación de preguntas (64 cerradas y 3 abiertas) organizadas en dimensiones y subdimensiones desde origen Excel.
- **Panel de administración:**
  - Layout con sidebar y navegación responsiva.
  - Dashboard con 4 KPIs escopados por rol: encuestas asignadas, completadas, en riesgo y tokens disponibles.
  - Widget de clima laboral con puntuación general y ranking de empresas.
  - Alertas progresivas de riesgo con acción de liberar tokens con confirmación.
- **Reportes multinivel (3 niveles de drill-down):**
  - Nivel 1: gráfico radar, KPIs, ranking de dimensiones y comparativas demográficas (barras agrupadas).
  - Nivel 2: bar chart, donut y tarjetas de subdimensión al seleccionar una dimensión.
  - Nivel 3: distribución por pregunta con barras apiladas.
  - Respuestas abiertas paginadas como componente aislado.
- **Exportación PDF:** Generación de PDF con 4 alcances configurables usando DomPDF.
- **Exportación CSV:** Exportación de datos con soporte de filtros activos.
- **Gestión de tokens:** Generación de lotes de tokens con historial; la ruta `/tokens` está protegida al rol `super_admin`.
- **Gestión de empresas:** CRUD completo de empresas con tabla filtrable y paginación personalizada.
- **`ClimaScoringService`:** Servicio centralizado de cálculo de clima con `scoresPorDimension()` y `promedioGeneral()`.
- **`DemoSeeder`:** Seeder de demostración con 93 encuestas completadas y 7 tokens en riesgo para entornos de prueba.
- **Migraciones transaccionales:** Todas las migraciones se ejecutan dentro de transacciones de base de datos.
- **Catálogos demográficos:** Migraciones y seeders para todos los catálogos demográficos (género, edad, antigüedad, etc.).
- **Internacionalización:** Traducciones al español vía `laravel-lang`.
- **Cobertura de tests:** Suite de tests con Pest alcanzando 71.2% de cobertura, incluyendo tests de autenticación, flujo de encuesta, dashboard, reportes y seeders.

### Cambiado

- **Terminología unificada:** Se normaliza "bloque" a "dimensión" en toda la base de código.
- **Routing:** Todas las referencias al dashboard se redirigen al panel de administración; se eliminan las vistas residuales de Breeze.

---

[Sin publicar]: https://github.com/arrilive/encuesta-clima-laboral/compare/v1.2.1...HEAD
[1.2.1]: https://github.com/arrilive/encuesta-clima-laboral/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/arrilive/encuesta-clima-laboral/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/arrilive/encuesta-clima-laboral/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/arrilive/encuesta-clima-laboral/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/arrilive/encuesta-clima-laboral/releases/tag/v1.0.0
