# Plan de pruebas aisladas — Sistema SAFE

Estas pruebas son una especificación inicial. Se deben ejecutar en staging con una copia restaurada y anonimizada, nunca en producción. Los valores esperados de contabilidad deben ser aprobados por el responsable financiero antes de convertirlos en pruebas automáticas.

## Preparación del entorno

- [ ] Documentar versión de PHP del sitio y de CLI, extensiones, motor SQL y configuración efectiva.
- [ ] Respaldar archivos, base de datos y configuración; guardar el respaldo fuera del document root y verificar restauración.
- [ ] Crear base separada de staging y usuario SQL con privilegios mínimos.
- [ ] Anonimizar DPI/CUI, NIT, teléfonos, correos, direcciones, documentos y datos de autenticación.
- [ ] Desactivar integraciones externas (correo/SMS/pasarelas) o usar credenciales de sandbox.
- [ ] Registrar commit y versión del esquema antes de probar.

## Pruebas de caracterización financiera

Los casos deben respetar el método actualmente aprobado para cada producto. No asumir que todos los créditos usan una sola base de días.

1. Interés con saldo cero, saldo positivo, saldo negativo inválido y fechas en los límites.
2. Tasa anual, mensual o diaria según contrato; validar conversión y periodicidad explícitamente.
3. Bases 360/365 y año bisiesto, solo para productos donde corresponda.
4. Pago puntual, atrasado, parcial, anticipado, pago extraordinario y cancelación total.
5. Mora, días vencidos y cambios de período; límites en 1, 7, 30, 60, 90, 120 y 180 días.
6. Cuotas con redondeo de centavos y ajuste de última cuota.
7. Ahorro: depósito, retiro, interés, cuenta inactiva, saldo mínimo y reversión autorizada.
8. Aportaciones: alta, abono, retiro permitido y conciliación del saldo por socio.
9. Caja: apertura, cobro, egreso, anulación con permiso, cierre y diferencia de caja.
10. Contabilidad: pólizas balanceadas, período cerrado, reversión, auxiliares, balance y estado de resultados.
11. Folios: dos operaciones simultáneas deben producir números únicos sin pérdida ni duplicación.
12. Reintento tras error: una operación financiera no debe duplicarse si el cliente vuelve a enviar la solicitud.

## Seguridad y regresión

- Acceso anónimo a cada módulo sensible: denegado.
- Usuario de caja intentando modificar usuarios, tasas o catálogo contable: denegado.
- Usuario de una sucursal consultando datos de otra: según matriz de permisos aprobada.
- SQL injection en búsquedas y formularios: entrada tratada como dato.
- CSRF para acciones que cambian saldos o configuran productos.
- Cookie de sesión segura en HTTPS; cambio de ID al autenticar y cierre efectivo de sesión.
- Archivos de configuración, backups, logs y directorios temporales no descargables desde la web.
- API de demostración inaccesible en producción o protegida según contrato de API documentado.
- No exponer contraseñas, tokens, DPI, NIT ni saldos en mensajes de error o logs.
- Pruebas de carga para caja y cobros concurrentes; verificar bloqueos, tiempos y consistencia.

## Criterios de aprobación

- Todos los movimientos del conjunto de prueba cuadran con el resultado esperado aprobado.
- Ningún saldo inicial cambia por una actualización técnica.
- Pólizas balanceadas y auxiliares conciliados.
- No hay duplicados en folios ni movimientos por concurrencia/reintentos.
- Pruebas críticas de autorización y seguridad aprobadas.
- Restauración de respaldo demostrada y plan de reversión probado.
- Aprobación escrita del responsable contable, operaciones y tecnología.

## Registro por caso

Para cada prueba conservar: ID, producto/método, datos sintéticos de entrada, resultado esperado, resultado obtenido, tolerancia aprobada, evidencia, responsable, fecha y estado. No incluir datos reales de socios.
