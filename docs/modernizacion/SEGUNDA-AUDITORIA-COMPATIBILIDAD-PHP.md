# Segunda ronda de auditoría estática: compatibilidad PHP y bloqueo de staging

**Fecha:** 2026-10-13  
**Rama:** `modernizacion/guatemala-safe`  
**Alcance:** revisión estática de archivos fuente versionados; no se ejecutó SAFE ni se accedió al hosting o a la base de datos. No se modificó código funcional ni datos.

## Estado del diagnóstico operativo

- Versión PHP web: desconocida.
- Versión PHP CLI: desconocida/no disponible.
- Versión MySQL/MariaDB: desconocida.
- Respaldo restaurado y validado en staging: pendiente.
- Producción: sin cambios realizados por esta auditoría.

**Bloqueo:** no iniciar pruebas funcionales con datos ni migraciones hasta comprobar que staging usa directorio, credenciales y base separados de producción y que el respaldo se restauró en la base correcta.

## Hallazgos de compatibilidad encontrados por inspección de código

### H-01 — Uso de la extensión antigua `mysql_*`
**Prioridad: crítica para una futura actualización de PHP.**

Se encontraron llamadas a la API `mysql_*` en:
- `htdocs1/core/core.deprecated.inc.php` (por ejemplo, alrededor de las líneas 56, 164 y otras).
- `htdocs1/core/core.init.inc.php` (por ejemplo, alrededor de las líneas 617–652).
- `htdocs1/core/core.creditos.inc.php` (por ejemplo, alrededor de la línea 1440).

La extensión antigua `mysql_*` fue eliminada en PHP 7. Si esos caminos de código se ejecutan, una actualización directa a PHP 7/8 puede provocar errores fatales. La cantidad de referencias y su uso real requieren un inventario más amplio. No reemplazar en masa: primero caracterizar consultas, conexiones, transacciones y resultados en pruebas.

### H-02 — Evaluación dinámica de fórmulas con `eval()`
**Prioridad: alta, requiere análisis de flujo de datos.**

Hay llamadas activas a `eval()` en módulos centrales, incluidos `htdocs1/core/core.creditos.inc.php` (por ejemplo, líneas 3051–3052, 4537–4538 y 7616). Parecen evaluar fórmulas de productos o parámetros. No se ha demostrado que un usuario externo pueda controlarlas, por lo que no se declara una vulnerabilidad confirmada. Hay que determinar quién puede crear/modificar esas fórmulas, dónde se almacenan y si pueden incluir código PHP arbitrario. El objetivo futuro es sustituirlas por un evaluador de expresiones limitado y validado, con pruebas financieras de equivalencia.

### H-03 — Funciones de cifrado heredadas
**Prioridad: alta para compatibilidad y seguridad.**

Se detectaron llamadas `mcrypt_*` en `htdocs1/core/core.deprecated.inc.php`. La extensión mcrypt dejó de formar parte del núcleo de PHP en versiones modernas. Antes de sustituirla, identificar qué datos cifra, formato, algoritmo, modo, IV, padding y cómo se migrarían los valores existentes. No rotar, descifrar ni reescribir datos originales durante esta etapa.

### H-04 — Configuración de Linux no versionada
**Prioridad: alta para reproducibilidad y protección de secretos.**

`core.config.inc.php` construye el nombre `core.config.os.<SO>.inc.php` según `PHP_OS`. En el árbol versionado solo se encontró `htdocs1/core/core.config.os.win.inc.php`; no se encontró `core.config.os.lin.inc.php`. Puede existir en el servidor y estar excluido intencionalmente por contener secretos, pero no se puede confirmar desde el repositorio. En staging debe crearse una configuración propia fuera del directorio público y no subir credenciales al repositorio.

### H-05 — Base de código extensa y legado
**Prioridad: alta para planificación de pruebas.**

El árbol contiene aproximadamente 2,751 archivos PHP. Una inspección selectiva no equivale a un análisis completo. Debe hacerse un inventario automatizado de llamadas obsoletas, puntos de entrada web, consultas concatenadas, permisos, manejo de errores y dependencias antes de estimar la migración.

### H-06 — Dependencias y proceso de pruebas histórico
**Prioridad: alta para la reproducibilidad.**

El proyecto contiene `composer.lock` y `composer.json`, además de un pipeline histórico con imagen `php:7.1.1`. Ese pipeline no confirma la versión de cPanel ni garantiza que las dependencias aún se instalen de forma segura. Primero se debe revisar el lockfile, licencias y avisos de seguridad en una copia; no ejecutar `composer update` sobre el sistema activo.

## Secuencia segura recomendada

1. En cPanel, confirmar la versión PHP asignada al dominio en MultiPHP Manager; no cambiarla.
2. Confirmar versión de MySQL/MariaDB desde phpMyAdmin.
3. Crear subdominio y carpeta de staging independientes, con acceso restringido.
4. Crear base de staging nueva y usuario SQL limitado a esa base.
5. Restaurar el respaldo en staging y verificar nombre de base, conteos y consistencia. Si no se puede verificar la restauración, detenerse.
6. Usar datos sintéticos o anonimizar la copia antes de exponer el sitio de pruebas.
7. Ejecutar primero pruebas de humo sin operaciones financieras reales. Registrar errores en logs protegidos.
8. Construir matriz de compatibilidad por versión PHP; probar una versión a la vez, sin cambiar producción.
9. Crear pruebas de regresión de créditos, interés corriente/moratorio, pagos anticipados/parciales, ahorro, caja, recibos, pólizas y saldos.
10. Autorizar cualquier cambio de producción solo con aprobación técnica y contable, copia verificada y plan de reversión ensayado.

## Evidencia que falta para avanzar

- Captura o texto de la versión PHP del dominio (sin información de cuenta).
- Versión MySQL/MariaDB.
- Confirmación de que el subdominio no comparte document root con producción.
- Confirmación de que la base de staging tiene otro nombre y otro usuario.
- Resultado de restauración y comprobaciones básicas de integridad.
- Errores de logs de staging, redactados para eliminar rutas privadas, contraseñas, tokens y datos de socios.

## Límites de este resultado

Esto es una auditoría estática selectiva del repositorio; no es una prueba de penetración, un análisis completo de dependencias ni una validación contable. No se ejecutó ningún archivo PHP, no se importó SQL, no se probaron credenciales y no se modificó producción.
