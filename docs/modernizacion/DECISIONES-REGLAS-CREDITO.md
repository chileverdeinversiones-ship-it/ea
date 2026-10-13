# Decisiones de producto — motor de crédito SAFE Moderno

**Decisiones confirmadas por la institución para orientar el diseño:**
- Tipo institucional declarado: cooperativa de ahorro y crédito.
- Interés ordinario: sobre saldos de capital.
- Abono extraordinario a capital: se puede proponer recalcular la cuota o reducir el plazo, pero debe existir autorización conforme al rol y política aplicable.

## Reglas del dominio

### Interés sobre saldos
- El interés se calcula sobre el capital pendiente durante el período definido por el producto, no sobre el monto original.
- El motor debe recibir una fecha efectiva de cálculo/pago y la base de días autorizada por producto (por ejemplo, 360 o 365); no se debe asumir que todos los productos usan la misma base.
- Cada resultado debe guardar tasa aplicada, base de días, fechas desde/hasta, capital base, días devengados, redondeo y versión de regla.
- La tasa y la base deben tener vigencia. Un cambio futuro no puede reescribir el historial de períodos ya calculados.
- Antes de activar producción, comparar casos del sistema actual, contratos y tablas de amortización aprobadas. El interés diario, inclusividad de fechas, feriados, vencimientos y redondeo todavía deben confirmarse.

### Abonos extraordinarios a capital
1. Simular el pago y separar mora/interés/capital según la política aprobada.
2. Si queda abono a capital, mostrar dos alternativas cuando el producto lo permita: mantener cuota y reducir plazo; o mantener plazo y recalcular cuota.
3. Registrar la alternativa elegida, motivo, usuario solicitante, autorización independiente, fecha efectiva y versión del plan resultante.
4. Rechazar la recalculación si falta autorización, hay cierre incompatible o la política del producto no la permite.
5. Generar una nueva versión del plan de pagos. Conservar el plan anterior y la razón del cambio.
6. El ajuste no puede generar un saldo negativo ni modificar silenciosamente movimientos contables ya registrados.

### Aplicación de pagos por monto recibido
Orden de diseño preliminar: mora exigible → interés ordinario pendiente → capital → excedente pendiente de decisión.
- Si el pago es insuficiente, distribuir solo el dinero efectivamente recibido y dejar visible cada saldo no cubierto.
- No asumir que el excedente se aplica a capital o se devuelve automáticamente. La institución debe definir la regla y obtener la autorización/consentimiento necesario.
- El cobro, sus distribuciones, movimientos de saldo, recibo y asientos contables deberán persistir en una única transacción de base de datos.
- Doble clic/reintento nunca puede duplicar un cobro; cada intento debe usar una clave de idempotencia y número de recibo único.

## Pendientes obligatorios antes de cerrar el motor
- Confirmar la base de días y el tratamiento de la fecha inicial/final.
- Confirmar cálculo de mora, período de gracia, tope, días no hábiles y si la mora se calcula sobre cuota vencida o capital.
- Confirmar prioridad de otros cargos/seguros/comisiones, si existen.
- Definir reglas de redondeo, cuotas irregulares, desembolsos parciales, pagos anticipados y reestructuraciones.
- Confirmar autorización por rol y límites monetarios.
- Validar el tratamiento con contratos, políticas aprobadas y asesoría contable/legal aplicable a la cooperativa.

## Estado
Este documento orienta la implementación. No es una certificación regulatoria ni una autorización para cobrar bajo estas reglas. La implementación actual del directorio `safe-modern/` solo contiene una función pura preliminar para repartir el monto recibido y pruebas unitarias; todavía no calcula intereses ni escribe en base de datos.
