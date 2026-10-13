# Auditoría técnica inicial — Sistema SAFE

**Fecha:** 2026-10-13  
**Repositorio:** `chileverdeinversiones-ship-it/ea`  
**Rama:** `modernizacion/guatemala-safe`  
**Alcance:** inspección estática de archivos seleccionados. No se conectó a servidores ni bases de datos; no se ejecutaron operaciones financieras.

## Resumen ejecutivo

SAFE parece ser una aplicación PHP heredada, organizada en módulos y con acceso a MySQL/MariaDB. La documentación y el pipeline señalan PHP 7.1.1; no se puede asumir que sea la versión de producción. La base incluye archivos de configuración, dependencias vendorizadas y una API adicional. Se recomienda tratar cualquier actualización como proyecto de migración, no como simple cambio de versión.

## Hallazgos priorizados

| ID | Prioridad | Evidencia observada | Acción recomendada |
|---|---|---|---|
| SAFE-A01 | Crítica por verificar | `htdocs1/api/index.php` contiene credenciales literales de ejemplo para una base `simplerisk` y rutas de prueba. | Confirmar si son solo datos demo; retirar credenciales del código y aislar o deshabilitar la API si no se usa. Si alguna vez fueron credenciales reales, rotarlas. No reutilizarlas. |
| SAFE-A02 | Alta | El endpoint API contiene una ruta POST de inserción con SQL construido por concatenación y una ruta GET de prueba. | Deshabilitar rutas de demostración; autenticar/autorizarlas; usar parámetros preparados, validación de esquema, límites y registros sin datos sensibles. |
| SAFE-A03 | Alta | `htdocs1/core/core.config.inc.php` inicia sesión con `@session_start()`; no se observa configuración de cookies seguras en el fragmento revisado. | Centralizar inicio de sesión, eliminar supresión de errores, regenerar ID tras autenticar y configurar `Secure`, `HttpOnly`, `SameSite` y modo estricto según HTTPS y compatibilidad. Revisar el flujo completo antes de cambiar. |
| SAFE-A04 | Alta | `htdocs1/core/core.db.inc.php` contiene consultas SQL concatenadas con variables, por ejemplo fechas y claves numéricas. | Inventariar todos los puntos de consulta; clasificar identificadores/valores; migrar a parámetros preparados donde corresponda y validar permisos. La inspección parcial no determina por sí sola explotabilidad. |
| SAFE-A05 | Alta | El workflow `htdocs1/bitbucket-pipelines.yml` fija `php:7.1.1`, descarga Composer mediante `curl` y ejecuta PHPUnit sobre `core/`. | Reproducir primero el pipeline histórico; después añadir checks en una matriz de PHP compatible y fijar/verificar el instalador de Composer. No cambiar la versión productiva sin pruebas de compatibilidad. |
| SAFE-A06 | Alta | `core.security.inc.php` define reglas de permisos por archivos/nivel y algunos accesos por tipo de usuario. | Crear matriz de permisos por rol y pruebas negativas para cada módulo sensible; confirmar denegación por defecto y autorización en servidor, no solo en menú/UI. |
| SAFE-A07 | Alta | Función SQL de interés usa `FLOAT` y divisor 360; folio de recibo se calcula como `MAX()+1` en el SQL compartido. | Especificar reglas por producto; usar aritmética decimal para dinero; proteger folios mediante secuencia/contador transaccional y restricción única. No recalcular ni alterar saldos históricos hasta conciliar. |
| SAFE-A08 | Media | Composer declara librerías antiguas y el repositorio incluye un directorio `vendor`. | Generar inventario de paquetes, versiones y CVE; distinguir dependencias usadas de copias antiguas; actualizar una dependencia a la vez con pruebas y lockfile. |
| SAFE-A09 | Media | `core.config.os.win.inc.php` contiene rutas locales de desarrollador y usuario/contraseña de demostración. | Confirmar que nunca se despliega ese archivo en hosting; mantener configuración fuera del webroot/repositorio, restringir permisos y usar secretos de entorno/hosting. |
| SAFE-A10 | Media | La API usa una conexión directa a localhost y la base `simplerisk`, distinta del nombre de conexión principal configurable. | Confirmar si la API forma parte del producto activo; si no, aislarla o retirarla tras inventario. Si sí, documentar su arquitectura y credenciales separadas. |

## Qué está confirmado y qué no

**Confirmado por lectura de archivos:** estructura PHP modular; dependencia de MySQL en configuración y SQL; pipeline histórico con PHP 7.1.1; código de API con rutas de prueba y credenciales literales de ejemplo; función de intereses con `FLOAT`; función de folios con `MAX()+1`.

**No confirmado:** versión PHP del cPanel productivo; si el archivo de configuración Windows o la API se despliegan; motor y versión exacta de producción; si las credenciales de ejemplo siguen activas; controles aplicados por el servidor web; alcance completo de SQL dinámico; cumplimiento normativo de la cooperativa.

## Secuencia de remediación segura

1. En hosting, obtener PHP CLI y PHP del sitio por separado, extensiones activas, versión MySQL/MariaDB y versión de Composer. No compartir contraseñas ni archivos de configuración con secretos.
2. Crear respaldo fuera del webroot y probar restauración en una base aislada.
3. Identificar document root real, configuración cargada y endpoints expuestos.
4. Deshabilitar endpoints de prueba confirmados como innecesarios; no hacerlo a ciegas si hay consumidores productivos.
5. Montar staging protegido por autenticación/IP, con copia anonimizada y sin envío real de SMS/correos.
6. Ejecutar línea base funcional y pruebas de regresión; solo entonces actualizar runtime y librerías.
7. Revisión humana de seguridad y aprobación contable antes de cualquier cambio de producción.

## No hacer todavía

- No ejecutar `safe-osms.sql`, `xx.functions.sql` ni `xx.vistas.sql` en producción.
- No aplicar normalización masiva de nombres ni conversiones de columnas sin copia y análisis de colisiones.
- No cambiar tasas, base 360/365, redondeos, vencimientos o reglas de mora en datos existentes.
- No borrar archivos, rutas ni módulos por parecer antiguos hasta confirmar que no se usan.
- No guardar exportaciones con información real de socios en el repositorio ni en los logs de CI.
