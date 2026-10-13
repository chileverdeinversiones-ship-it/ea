# SAFE Moderno — alcance y estado real

## Qué es esta versión
Aplicación nueva en construcción para una cooperativa de ahorro y crédito o microfinanciera en Guatemala. La rama de desarrollo está aislada de la rama principal. El prototipo web contiene datos ficticios y no procesa transacciones.

## Implementado en el esqueleto actual
- Panel web responsive con navegación lateral y vistas de módulos de demostración.
- Zona horaria de presentación America/Guatemala y moneda de presentación Q.
- Servicio puro de distribución de pagos: mora, interés pendiente, capital y excedente reportado aparte.
- Servicio puro de devengo de interés ordinario simple sobre saldo de capital, con tasa anual en puntos básicos, base 360/365/366 y redondeo al centavo.
- Pruebas unitarias para distribución y devengo.
- Esquema inicial de tablas; no conectado a una base.
- Pipeline de pruebas configurado para PHP 8.4 y 8.5.

## No implementado todavía
- Inicio de sesión de producción, recuperación de contraseña, autorización por permisos, CSRF y gestión de sesiones.
- Persistencia de pagos, idempotencia real de punta a punta, bloqueo de filas y numeración segura de recibos.
- Motor completo de amortización para cuotas fijas/variables, calendario, días irregulares, feriados, cuotas vencidas, seguros/cargos y reestructuraciones.
- Flujos de solicitud, las 5 C, análisis de capacidad, comité, avalúos, garantías y desembolso.
- Recalcular cuota o plazo con autorización y conservar versiones de planes.
- Módulos de caja operativa, captaciones, aportaciones, bancos y conciliación.
- Contabilidad de partida doble y asientos automáticos.
- Reportes reales, exportación, respaldos y restauración.
- Migración, conciliación y equivalencia de los datos del SAFE antiguo.
- Pruebas de integración, seguridad, carga, recuperación y aceptación de usuarios.

## Reglas confirmadas para orientar el desarrollo
- Institución declarada: cooperativa de ahorro y crédito.
- Interés ordinario sobre saldo de capital.
- Abonos extraordinarios: opción de reducir cuota o plazo solo según autorización.
- Aplicación preliminar de pagos: mora exigible → interés pendiente → capital → excedente separado hasta definir la política del excedente.

## Riesgos y condiciones previas
- La base de días, fechas inclusivas, cálculo de mora, gracia, cargos, redondeos, política de excedentes y contabilidad deben aprobarse y contrastarse con contratos/productos reales.
- Los datos de tablero son ejemplos visuales, no indicadores reales.
- El esquema SQL debe probarse primero en una base desechable aislada. Nunca ejecutarlo sobre producción.
- PHP 8.5 solo será objetivo de despliegue si el cPanel real lo soporta y pasan las pruebas de dependencias y extensiones.
- La institución debe confirmar el marco contable y regulatorio que le aplica según su naturaleza jurídica.
- No habilitar operaciones reales hasta completar controles, revisión independiente, conciliación y autorización formal.
