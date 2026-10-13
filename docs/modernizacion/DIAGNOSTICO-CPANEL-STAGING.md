# Diagnóstico en cPanel y preparación de staging

**Para:** Sistema SAFE / cooperativa en Guatemala  
**Fecha:** 2026-10-13  
**Estado:** instrucciones de diagnóstico, no ejecutadas en el hosting.  
**Restricción:** conservar producción y todos los datos originales.

## Contexto técnico observado en el repositorio

- El pipeline histórico de `htdocs1/bitbucket-pipelines.yml` utiliza la imagen `php:7.1.1`. Esto solo describe el entorno de CI histórico, no la versión PHP asignada al dominio en cPanel.
- La aplicación carga configuración desde `core.config.inc.php` y un archivo dependiente de `PHP_OS`; el archivo de configuración Windows está en el repositorio. Confirmar qué archivo carga Linux/cPanel y no publicar su contenido.
- `htdocs1/api/index.php` contiene rutas de demostración y credenciales literales para una base llamada `simplerisk`. No llamar a esas rutas durante el diagnóstico. Confirmar si el endpoint está publicado o en uso antes de deshabilitarlo.
- Composer declara librerías antiguas; no ejecutar `composer update` ni cambiar PHP en producción como primer paso.

## A. Capturar las versiones sin exponer secretos

En cPanel, registrar en una hoja de trabajo privada:

1. Dominio principal y document root que aparece en **Domains**.
2. Versión PHP seleccionada para ese dominio en **MultiPHP Manager**.
3. Extensiones PHP habilitadas: `mysqli`, `pdo_mysql`, `mbstring`, `json`, `openssl`, `curl`, `gd`, `xml`, `zip`, `session` y cualquier otra requerida por SAFE.
4. Versión de MySQL/MariaDB desde **phpMyAdmin** (pantalla inicial) o la información del proveedor.
5. Si está disponible **Terminal**, ejecutar solo comandos informativos desde el directorio correcto:
   - `php -v`
   - `php --ini`
   - `php -m`
   - `composer --version`
   - `git rev-parse --short HEAD` (si el checkout tiene Git)
6. No asumir que la versión CLI coincide con la versión PHP del sitio web. Registrar ambas si son diferentes.
7. No ejecutar `phpinfo()` en una URL pública ni subir un archivo de diagnóstico con credenciales.

No enviar al chat ni al repositorio contraseñas, tokens, contenido de archivos `core.config.os.*.inc.php`, volcados completos ni datos personales de socios.

## B. Crear un staging separado

1. Crear un subdominio de prueba, por ejemplo `staging-safe.tudominio`, protegido por contraseña o restricción IP. Usar un nombre real elegido por el administrador.
2. Crear una base de datos distinta, por ejemplo `safe_staging`, y un usuario separado con permisos únicamente sobre esa base.
3. Copiar los archivos a una carpeta independiente del sitio productivo. No apuntar el subdominio al document root de producción.
4. Restaurar el respaldo en la base de staging, nunca sobre la base original. Verificar nombre de base y usuario antes de importar.
5. Anonimizar datos personales y credenciales: nombres, DPI/CUI, NIT, teléfonos, correos, direcciones, documentos, hashes/contraseñas y tokens. Usar datos sintéticos para pruebas cuando sea posible.
6. Configurar el archivo de entorno de staging fuera del directorio público, con credenciales exclusivas de staging. No copiar credenciales de producción.
7. Desactivar correo/SMS, facturación, pagos, tareas programadas y cualquier integración externa o apuntarla a sandbox. Evitar que pruebas envíen avisos reales o muevan dinero.
8. Restringir la salida de red si la plataforma lo permite; habilitar logs solo con redacción de datos sensibles.
9. Mantener staging no indexado por buscadores y con acceso restringido; no usarlo para operación real.

## C. Establecer una línea base

Antes de modificar código, guardar exportaciones de reportes de la copia anonimizada:

- Conteo de socios/cuentas y estado de registros.
- Saldos de ahorro y aportaciones por cuenta/socio sintético.
- Cartera de créditos, capital, interés pendiente y días vencidos.
- Totales de caja, recibos y movimientos del período de prueba.
- Pólizas, balance de comprobación y estado de resultados.
- Totales por sucursal y fecha de corte.

Las cifras se deben conciliar contra reportes del sistema actual. Guardar la evidencia fuera del repositorio, sin información personal. No registrar los importes reales de producción en issues públicos.

## D. Plan de compatibilidad PHP

1. Primero ejecutar el sistema en staging con la misma versión que hoy tiene asignada el dominio, para reproducir el comportamiento.
2. Registrar errores de sintaxis, funciones eliminadas, advertencias, extensiones faltantes y errores de consultas. No ocultarlos con `@` ni desactivar la visualización solo para declarar que pasó.
3. Determinar versiones candidatas modernas soportadas por el proveedor, revisando compatibilidad de librerías, sintaxis, extensiones y SQL. No saltar directamente a la última versión.
4. Probar una versión candidata por vez en staging, conservando snapshots/backup de staging para volver atrás.
5. Ejecutar las pruebas financieras, de permisos y de regresión del documento `PLAN-PRUEBAS-STAGING.md`.
6. No cambiar PHP de producción hasta que el responsable técnico y el responsable contable aprueben resultados y plan de reversión.

**Nota:** PHP 5.6 y anteriores están fuera de soporte de seguridad. Si se confirma esa versión, el riesgo es alto; la solución segura es aislar y planificar una migración probada, no prolongar indefinidamente la exposición ni actualizar a ciegas.

## E. Confirmaciones que debe devolver el administrador

Completar únicamente valores técnicos, nunca secretos:

- PHP del sitio web:
- PHP CLI:
- MySQL/MariaDB versión:
- Extensiones requeridas disponibles:
- Document root de producción confirmado (sí/no):
- Subdominio de staging aislado (sí/no):
- Base de staging distinta verificada (sí/no):
- Respaldo restaurado con éxito en staging (sí/no):
- Integraciones externas desactivadas/sandbox (sí/no):
- Errores iniciales observados:

No continuar con migraciones de esquema ni cálculos financieros si staging comparte la base de datos con producción.
