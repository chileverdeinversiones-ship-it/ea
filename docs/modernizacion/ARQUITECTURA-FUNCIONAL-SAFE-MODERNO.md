# SAFE Moderno Guatemala — visión funcional y arquitectura objetivo

**Estado:** propuesta de diseño; no es código desplegable ni autorización para modificar producción.  
**Objetivo:** construir una aplicación nueva que conserve los flujos de negocio útiles del SAFE existente, con pruebas de equivalencia antes de migrar.

## 1. Principios no negociables

- Producción y la base original permanecen intactas durante análisis y desarrollo.
- Desarrollar en rama independiente y desplegar únicamente a un staging aislado con base de datos separada y restauración verificada.
- Nunca copiar credenciales del repositorio a la nueva aplicación. Secretos fuera del directorio público y rotación si estuvieron expuestos.
- Toda operación monetaria genera un movimiento trazable, con usuario, fecha/hora Guatemala, sucursal, caja, referencia y estado.
- No borrar ni editar movimientos contabilizados: corregir mediante reversos y movimientos compensatorios autorizados.
- Dinero con DECIMAL, no FLOAT/DOUBLE; montos presentados en quetzales (Q), con precisión definida por producto.
- Fechas locales America/Guatemala y reglas explícitas de días de interés, vencimiento, feriados y cierres.
- Acceso por rol y permiso mínimo; bitácora de consultas/cambios sensibles, límites de sesión, CSRF, validación del servidor y consultas preparadas.
- Cálculos financieros aislados en servicios probables y cubiertos por pruebas; no usar eval() para fórmulas de usuarios.
- Compatibilidad exacta con PHP depende de cPanel, extensiones, proveedor y dependencias. Diseñar para PHP 8.5 si el hosting lo soporta y las dependencias pasan pruebas; PHP 8.4 es alternativa temporal soportada, no se cambia producción para probar.

## 2. Módulos funcionales

### Plataforma y seguridad
- Inicio de sesión, recuperación segura, roles/permisos, usuarios, sucursales, cajas, parámetros institucionales.
- Bitácora inmutable, aprobaciones duales, alertas, tareas programadas y copias de seguridad verificables.
- Parámetros por fecha de vigencia y trazabilidad de quién aprobó cada cambio.

### Clientes y socios
- Personas individuales y jurídicas, identificación según aplique (DPI/CUI, NIT), datos de contacto, actividad económica, dirección, referencias, beneficiarios y expediente documental.
- Detección de duplicados, estados de expediente, consentimientos y controles de acceso a documentos.
- No almacenar más información sensible de la necesaria.

### Créditos: ciclo completo
1. Solicitud, producto, monto, destino, plazo, frecuencia, fuente de pago y documentos.
2. Evaluación de las 5 C: carácter, capacidad, capital, colateral/garantía y condiciones. Cada criterio registra evidencia, puntuación configurable, observaciones y responsable.
3. Análisis de capacidad de pago, ingresos/gastos, endeudamiento, flujo de caja y escenarios de estrés.
4. Avalúo: bien, propietario, valuador, fecha, valor comercial/realizable, evidencia, vigencia y revisión.
5. Garantías: tipo, titular, descripción, valor, gravámenes, documentos, cobertura y liberación formal.
6. Flujo de aprobación por límites, comité y segregación de funciones.
7. Simulación y plan de pago con versión congelada al aprobar: principal, tasa, base de días, periodicidad, comisiones, seguros/cargos autorizados, redondeos y fechas.
8. Desembolso aprobado, cálculo de comisión, retenciones autorizadas, comprobante y asiento contable.
9. Gestión de cuotas vencidas, mora, pagos extraordinarios, abonos a capital, cancelación, refinanciamiento y reestructuración con historial.
10. Seguimiento, alertas de vencimiento, promesas de pago, gestión de cobro y reportes de cartera.

### Caja y pagos por monto libre
El cajero busca cliente/crédito, consulta saldos a una fecha efectiva y escribe el monto total recibido. El sistema previsualiza la distribución antes de confirmar. El orden propuesto es:
1. Mora vencida exigible y calculada según política del producto.
2. Interés ordinario/devengado pendiente hasta la fecha efectiva de pago.
3. Capital pendiente.
4. Excedente después de liquidar lo anterior: aplicar a capital como abono extraordinario o dejar como saldo a favor únicamente si la política aprobada lo permite y el cliente elige/autoriza esa opción.

Reglas importantes:
- Si el efectivo no alcanza para cubrir mora e intereses, registrar el pago recibido, distribuirlo según la regla parcial aprobada y dejar saldo pendiente; nunca inventar que se pagó una cuota completa.
- No aceptar un pago mayor al saldo total sin definir cómo se trata el excedente.
- Mostrar antes de confirmar: recibido, mora, intereses, capital, saldo a favor (si permitido), saldo restante y fecha/hora.
- Guardar el detalle de aplicación por concepto y por crédito; recibo numerado, impresión/PDF, reimpresión auditada, anulación por reverso autorizado y cierre de caja.
- Transacción atómica, bloqueo/concurrencia del crédito, idempotencia para evitar doble cobro, y referencia única. No generar recibos con MAX()+1 sin bloqueo/contador seguro.
- Política final (incluyendo prioridad de gastos, seguros, cargos, mora, interés y capital) debe aprobarse institucionalmente y validarse con la normativa/contratos aplicables antes de operar.

### Captaciones y aportaciones
- Apertura y mantenimiento de cuentas, aportaciones de socios, ahorro a la vista, depósitos a plazo si se ofrecen, depósitos/retiros/transferencias internas, intereses, vencimientos, beneficiarios y retiros anticipados.
- Reglas por producto, límites y autorizaciones, retenciones si aplican, conciliación, comprobantes y asientos contables.
- Evitar retirar fondos de cuentas bloqueadas, comprometidas o con validación pendiente.

### Contabilidad, tesorería y reportes
- Catálogo de cuentas, pólizas/partidas, libro diario y mayor, balance de comprobación, balance general, estado de resultados, auxiliares de cartera y captaciones, cierres de período y trazabilidad.
- Asientos automáticos parametrizados desde créditos, caja, captaciones, gastos y bancos; validar que débitos = créditos antes de contabilizar.
- Bancos, conciliaciones, cuentas por pagar/cobrar, gastos, presupuesto, flujo de caja y reportes gerenciales.
- Parametrizar catálogo y reportes según el marco contable y requisitos regulatorios que efectivamente apliquen a la institución; no presumir obligaciones sin confirmar su naturaleza jurídica.

## 3. Diseño técnico propuesto para cPanel

- Backend: PHP 8.5 como objetivo inicial si el proveedor lo ofrece y todas las extensiones/dependencias pasan la matriz de compatibilidad. En caso contrario, probar PHP 8.4 en staging y documentar el motivo; no forzar versiones en producción.
- Base de datos: MySQL/MariaDB con versión confirmada, InnoDB, claves foráneas donde proceda, DECIMAL para dinero, índices y migraciones versionadas.
- Acceso a datos: PDO con consultas preparadas; transacciones para operaciones financieras; ninguna consulta concatenada con datos del usuario.
- UI: interfaz web responsive, navegación clara, formularios con validación, búsqueda rápida, tablas filtrables y accesibilidad.
- Estructura: capas separadas de presentación, aplicación, dominio/cálculos y persistencia; controladores pequeños, servicios de negocio y repositorios.
- Dependencias: composer.lock versionado, auditoría de dependencias y versiones fijadas; nunca ejecutar composer update en producción.
- Entornos: local/desarrollo → staging aislado → validación de usuarios → despliegue controlado. Document root público limitado a public/; secretos y archivos de respaldo fuera de public_html cuando el hosting lo permita.
- Observabilidad: errores detallados solo en staging; producción con logs sin contraseñas, DPI completo ni datos financieros sensibles.

## 4. Datos, migración y equivalencia

No copiar la base viva ni ejecutar scripts sobre producción. Primero:
1. Inventariar tablas, vistas, procedimientos, funciones, triggers, relaciones, codificación, índices y tamaño.
2. Confirmar versiones reales de PHP, MySQL/MariaDB, extensiones y acceso de terminal/Composer.
3. Crear staging y base/usuario independientes; restaurar un backup y verificar recuentos, integridad y capacidad de volver a restaurarlo.
4. Anonimizar datos personales en staging; no trabajar con datos reales innecesarios.
5. Documentar diccionario de datos y mapa de módulos actuales.
6. Crear pruebas de referencia con casos ficticios y, si está autorizado, resultados históricos anonimizados.
7. Migrar por módulo con conciliación de saldos de apertura, totales de cartera, captaciones y contabilidad.
8. No cortar al nuevo sistema hasta superar pruebas funcionales, financieras, de seguridad, rendimiento y recuperación.

## 5. Pruebas obligatorias del motor de crédito y caja

- Crédito nuevo, aprobado, rechazado, desembolsado, vencido, cancelado y reestructurado.
- Planes con distintos montos, tasas, plazos, periodicidades, feriados, atrasos y pagos anticipados.
- Pago menor que mora; pago igual a mora; pago que cubre mora e interés; pago parcial de interés; pago que llega a capital; abono extraordinario; cancelación exacta; excedente; doble clic/reintento concurrente.
- Comparar desglose antes/después, saldo de capital, interés acumulado, mora, plan reprogramado y asiento contable.
- Verificar redondeos, días del año/mes, fecha efectiva, cambio de mes/año y cierres.
- Ningún usuario de caja puede aprobar su propia operación fuera de sus permisos; anulación/reverso siempre deja evidencia.
- En cada asiento: débitos = créditos. Caja física y movimientos del sistema deben conciliar.

## 6. Fases sugeridas

A. Descubrimiento del sistema actual y reglas financieras reales.  
B. Base técnica, autenticación, permisos, bitácora y configuración.  
C. Clientes/socios y catálogos.  
D. Motor de crédito, plan de pagos y pruebas financieras.  
E. Caja y pagos por monto libre.  
F. Captaciones y aportaciones.  
G. Contabilidad integrada y conciliaciones.  
H. Reportes, seguridad, pruebas de carga, capacitación y migración ensayada.  
I. Piloto controlado y salida a producción solo con aprobación explícita.

## 7. Decisiones pendientes antes de programar cálculos reales

- Tipo legal de institución y marco contable/regulatorio aplicable.
- Reglas actuales de tasa, base de días, mora, comisiones, seguros/cargos, orden de aplicación y redondeos por producto.
- Si un abono a capital recalcula cuota, plazo o ambos, y quién lo autoriza.
- Productos de captación realmente ofrecidos y reglas de retiro anticipado.
- Versión y extensiones de PHP/MySQL/MariaDB disponibles en el hosting.
- Módulos y reportes actuales que deben mantenerse idénticos.

**Este documento define una meta de diseño, no certifica cumplimiento legal ni sustituye la revisión de las políticas aprobadas de la institución.**
