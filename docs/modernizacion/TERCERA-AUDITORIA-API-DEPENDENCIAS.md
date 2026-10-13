# Tercera ronda de auditoría estática: configuración, API y dependencias

**Fecha:** 2026-10-13  
**Rama:** `modernizacion/guatemala-safe`  
**Alcance:** inspección de archivos versionados. No se ejecutaron endpoints ni código PHP. No se accedió a cPanel, a producción ni a bases de datos. No se cambiaron código funcional ni datos.

## Estado operativo recibido

- PHP del dominio: pendiente de identificar.
- PHP CLI: desconocido/no disponible.
- MySQL/MariaDB: el administrador indica que la versión ya fue identificada, pero aún no se ha compartido el número.
- Subdominio y carpeta separados: pendiente.
- Base y usuario separados: pendiente.
- Restauración de respaldo en staging: pendiente de verificar.

**Decisión:** el staging todavía no está habilitado para pruebas funcionales con información. No desplegar, importar ni ejecutar scripts hasta validar aislamiento y restauración.

## Hallazgos nuevos

### H-07 — API con rutas de demostración y credenciales embebidas
**Prioridad: crítica hasta confirmar exposición y uso.**

El archivo `htdocs1/api/index.php` incluye rutas que parecen de prueba y una conexión a una base de datos de ejemplo con credenciales literales dentro del código. Una ruta POST define sentencias SQL y otra ruta GET devuelve un parámetro de la URL; la ruta raíz también intenta consultar una tabla. No se llamó a ninguna ruta y no se ha confirmado si la API está desplegada o es accesible públicamente.

Acciones seguras:
1. Confirmar con el administrador si existe tráfico o integración legítima que dependa de `/api/`.
2. Revisar logs de acceso en el servidor, sin publicar IPs o tokens.
3. En staging, probar cada ruta de forma controlada y sin datos reales.
4. Si el endpoint no tiene uso autorizado, acordar una desactivación controlada y documentada; no borrarlo a ciegas.
5. Retirar credenciales del código y usar variables de entorno/configuración fuera del directorio público. Si esas credenciales alguna vez se usaron en un servidor accesible, rotarlas de manera coordinada tras identificar dependencias.
6. Aplicar autenticación, autorización, validación de entrada, consultas parametrizadas y respuestas de error seguras en cualquier API que se mantenga.

No se incluyen en este informe los valores de las credenciales halladas.

### H-08 — Dependencias Composer sin restricción explícita de plataforma
**Prioridad: alta para reproducibilidad.**

`htdocs1/composer.json` declara dependencias de cliente CouchDB, traducción, PostgreSQL, SMS y almacenamiento en nube, pero no declara una versión mínima/máxima de PHP ni extensiones requeridas. El lockfile existe. Esto no demuestra que todas esas dependencias se usen en producción ni que sean compatibles con PHP actual.

Acciones:
- Inventariar los usos reales antes de actualizar o eliminar dependencias.
- Revisar el lockfile, fechas de mantenimiento, licencias y avisos de seguridad en un entorno aislado.
- Confirmar extensiones PHP y compatibilidad con cada versión candidata.
- No ejecutar `composer update` ni instalar paquetes nuevos en producción.
- Preparar una instalación reproducible con `composer install` solo en un checkout de pruebas y con versión de PHP documentada.

### H-09 — Pipeline histórico no representa necesariamente cPanel
**Prioridad: media/alta.**

`htdocs1/bitbucket-pipelines.yml` utiliza una imagen histórica `php:7.1.1` y ejecuta pruebas sobre `core/`. Esto no confirma la versión del dominio ni prueba la aplicación completa. El pipeline también instala herramientas en tiempo de ejecución, por lo que puede dejar de ser reproducible si cambian los repositorios externos.

Acciones:
- Documentar la versión de PHP de la web y de CLI por separado.
- Identificar si la suite indicada existe, qué cubre y si pasa en una imagen congelada.
- Crear una matriz de versiones para pruebas; no cambiar producción para descubrir compatibilidad.
- Mantener las pruebas financieras y de autorización como puertas de aceptación, no solo la ausencia de errores de sintaxis.

### H-10 — Configuración de ejemplo y rutas locales en archivo versionado
**Prioridad: alta de higiene de secretos y despliegue.**

`htdocs1/core/core.config.os.win.inc.php` contiene rutas locales de desarrollo y valores de conexión de demostración. No deben usarse en hosting Linux. El archivo específico de Linux no aparece en el árbol versionado consultado; podría estar presente en el servidor o excluido intencionalmente.

Acciones:
- No copiar el archivo Windows a cPanel.
- Confirmar el mecanismo real de configuración del servidor sin mostrar secretos.
- Mantener configuración de staging y producción separadas.
- Revisar reglas del servidor para impedir acceso HTTP a archivos de configuración, copias de seguridad, logs y archivos de desarrollo.
- Evitar subir configuraciones con credenciales al repositorio.

## Próximas tareas de diagnóstico sin riesgo

1. Completar la versión exacta de MySQL/MariaDB en una nota privada.
2. Identificar PHP web desde MultiPHP Manager, sin modificar la selección.
3. Verificar que el subdominio tenga document root distinto al de producción.
4. Crear base y usuario nuevos, con permisos solo sobre la base de staging.
5. Restaurar respaldo en esa base y comprobar que producción no fue seleccionada.
6. Usar datos sintéticos o anonimizar la copia antes de hacer pruebas.
7. Revisar logs de staging y probar páginas de solo lectura primero.
8. Después de tener una línea base, diseñar pruebas controladas para API, autenticación, permisos, SQL y operaciones financieras.

## Límites y clasificación

- Los hallazgos son indicios confirmados en el código versionado, no confirmación de exposición en producción.
- No se hicieron peticiones HTTP, intentos de autenticación, escaneos del servidor ni consultas SQL.
- No se ejecutó el código ni se validó una vulnerabilidad de extremo a extremo.
- No se alteraron datos, esquemas, configuración de hosting ni la rama de producción.
