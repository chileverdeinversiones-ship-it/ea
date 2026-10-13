# Plan de modernización segura — Sistema SAFE (Guatemala)

**Estado:** diagnóstico inicial y plan. No es una autorización de despliegue.  
**Rama de trabajo:** `modernizacion/guatemala-safe`  
**Principio rector:** conservar los datos y reglas vigentes hasta que cada cambio esté respaldado por pruebas, conciliaciones y aprobación de responsables.

## 1. Alcance revisado inicialmente

El repositorio `chileverdeinversiones-ship-it/ea` contiene:
- `htdocs1/`: aplicación web con archivos PHP, estructura modular, reportes, contabilidad, tesorería y seguridad.
- `htdocs1/composer.json`: dependencias PHP antiguas, incluyendo clientes de CouchDB, traducción, PostgreSQL, SMS y almacenamiento en la nube.
- `safe-osms.sql`: volcado SQL de gran tamaño.
- `xx.functions.sql` y `xx.vistas.sql`: funciones y vistas SQL.
- `htdocs1/README.2015-01-01.txt`: documentación de versión 2015; los scripts SQL consultados incluyen comentarios de 2016.

Este es un diagnóstico parcial de archivos representativos, no una auditoría exhaustiva ni una certificación de seguridad.

## 2. Hallazgos iniciales que requieren validación

1. **Antigüedad tecnológica:** documentación y scripts tienen referencias de 2015–2016. Verificar versiones reales de PHP, motor SQL, extensiones, servidor web y dependencias antes de actualizar.
2. **Posible diferencia entre motores:** el SQL revisado usa sintaxis y objetos MySQL (por ejemplo, `DELIMITER`, funciones almacenadas y `SET GLOBAL`), mientras que Composer declara `simplon/postgres`. Confirmar cuál motor se usa realmente en producción y qué partes acceden a cada motor.
3. **Precisión monetaria:** una función de interés utiliza tipos `FLOAT` y divisor 360. El resultado depende del tipo de tasa, base de días, saldo, fechas, redondeo y contrato. No cambiar el método sin comparar con contratos, políticas y registros históricos.
4. **Consecutivos de recibos:** una función obtiene el máximo folio, suma uno e inserta el siguiente. Bajo concurrencia podría duplicar folios si no existe un mecanismo de serialización/índice adecuado. Revisar tabla, índices, transacciones y rutas de cobro.
5. **Permisos de base de datos:** el script de funciones solicita `SET GLOBAL log_bin_trust_function_creators = 1`. Revisar si es necesario; evitar permisos globales innecesarios y no ejecutar scripts de instalación directamente en producción.
6. **Seguridad de credenciales:** inspeccionar configuración, autenticación, sesiones, contraseñas, secretos y archivos de respaldo. No copiar contraseñas, tokens ni datos personales a documentación o registros de CI.
7. **Integridad y trazabilidad:** validar que las operaciones de caja, depósitos, retiros, créditos, intereses y pólizas se registren de forma atómica, tengan auditoría y puedan conciliarse.

## 3. Adaptación a Guatemala

- Moneda de presentación: quetzal (GTQ / Q), con precisión decimal definida para cada tipo de operación; no usar números de punto flotante para importes contables.
- Zona horaria de aplicación: `America/Guatemala`; almacenar y presentar fechas con reglas consistentes y documentadas.
- Formularios y reportes en español, fecha visible `dd/mm/aaaa`, formatos de teléfono y direcciones locales configurables.
- Identificación: admitir DPI/CUI y NIT cuando corresponda, sin asumir que todo socio tiene el mismo documento; validar formatos sin reemplazar verificaciones oficiales.
- Contabilidad: mapear cuentas, asientos, auxiliares y reportes contra el Manual de Normas Contables para Cooperativas aplicable publicado por INGECOP, incluida la versión 2.0 y sus reformas vigentes. La aprobación final requiere revisión del contador/auditor de la cooperativa y confirmación normativa.
- Intereses, mora, ahorro, aportaciones, comisiones, provisiones y cierres: definir por producto y contrato; registrar base de días, método, tasa, vigencia, regla de redondeo y fecha efectiva. No imponer una regla universal.
- Cumplimiento: validar con asesoría local obligaciones aplicables a la entidad, incluyendo libros/formas y requerimientos de INGECOP/INACOP; no afirmar cumplimiento automático por el mero cambio de software.

Referencias oficiales para validación:
- INGECOP — Manual contable, versión 2.0: https://ingecop.gob.gt/wp-content/uploads/1686/87/MANUAL-CONTABLE-VERSION-2-0.pdf
- INGECOP — Acuerdo IGC-068-2023 de reforma: https://ingecop.gob.gt/wp-content/uploads/2023/11/ACUERDO-068-2023-REFORMA-AL-ACUERDO-IGC-040-2023-MANUAL-DE-NORMAS-CONTABLES-PARA-COOPERATIVAS-DE-AHORRO-Y-CREDITO-Y-PARA-COOPERATIVAS-DE-AHORRO-Y-CREDITO-CON-ACTIVIDADES-INTEGRALES-Y-DE-SERVICIOS-VARIOS-VERSION-.pdf
- INACOP — Leyes y reglamentos: https://www.inacop.gob.gt/leyes-y-reglamentos/

## 4. Fases propuestas

### Fase 0 — Resguardo y descubrimiento
- Crear respaldo íntegro de archivos y base de datos; cifrarlo y verificar restauración.
- Registrar commit base, versiones del runtime, extensiones, motor SQL, tamaño de datos y tareas programadas.
- Detectar secretos expuestos y revisar acceso al repositorio. Si se encuentran credenciales reales, rotarlas fuera del repositorio.
- Mapear módulos, tablas, funciones, vistas, integraciones y procesos diarios.

### Fase 1 — Línea base funcional
- Preparar una copia anonimizada de producción en ambiente aislado.
- Obtener resultados de referencia para saldos de socios, caja, ahorros, créditos, recibos, cartera vencida, pólizas, balance y estado de resultados.
- Documentar fórmulas actuales y diferencias conocidas; los saldos de apertura deben conciliar exactamente.

### Fase 2 — Pruebas automatizadas y controles
- Pruebas unitarias de tasas, cuotas, devengo diario, mora, pagos parciales, anticipados, cancelaciones y años bisiestos.
- Pruebas de integración de caja, ahorro, desembolso, cobro, pólizas y cierre diario.
- Pruebas de concurrencia para folios y movimientos simultáneos.
- Pruebas de permisos por rol, sesiones, CSRF, validación de entradas, SQL parametrizado y auditoría.
- Criterio de salida: ninguna diferencia monetaria no explicada, pruebas críticas aprobadas y restauración de respaldo demostrada.

### Fase 3 — Modernización incremental
- Separar reglas de negocio, acceso a datos y presentación sin reescribir todo de una vez.
- Actualizar PHP y dependencias en pasos compatibles, con análisis estático y pruebas por versión.
- Mejorar interfaz adaptable, accesibilidad, mensajes claros, búsquedas, formularios y paneles.
- Introducir cambios de esquema mediante migraciones versionadas, idempotentes y reversibles cuando sea posible.

### Fase 4 — Piloto y producción
- Ejecutar ensayo completo en staging con copia anonimizada.
- Conciliar saldos antes/después, medir rendimiento y obtener aprobación funcional, contable y técnica.
- Despliegue gradual con ventana de mantenimiento, respaldo verificado, plan de reversión y responsable asignado.
- No ejecutar migraciones destructivas ni modificar producción automáticamente desde CI.
- Vigilar errores, tiempos de respuesta, cierres y conciliaciones tras el despliegue.

## 5. Puertas obligatorias antes de producción

- [ ] Respaldo y restauración probados.
- [ ] Inventario de runtime, motor de base de datos y dependencias aprobado.
- [ ] Fórmulas financieras documentadas y aprobadas por responsable.
- [ ] Pruebas unitarias, integración, concurrencia y seguridad aprobadas.
- [ ] Conciliación de saldos y reportes sin diferencias inexplicadas.
- [ ] Revisión del Manual contable INGECOP y normativa aplicable completada.
- [ ] Plan de reversión y responsables confirmados.
- [ ] Aprobación explícita del responsable de la cooperativa.

## 6. Límites del trabajo actual

Este documento no modifica tablas, datos ni código funcional. No se ha ejecutado ninguna operación contra producción. Antes de programar cambios financieros, se necesita conocer la configuración real de despliegue y disponer de datos de prueba anonimizados y resultados esperados validados.
