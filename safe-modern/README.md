# SAFE Moderno Guatemala — aplicación nueva

Este directorio contiene el **inicio de una aplicación nueva**. No sustituye el SAFE actual y no debe instalarse sobre producción.

## Alcance inicial
- Estructura mínima compatible con PHP moderno y Composer.
- Primer servicio puro de distribución de pagos por monto libre: mora → interés pendiente → capital → excedente identificado.
- Importes monetarios representados en centavos enteros dentro del motor de distribución, evitando aritmética binaria de punto flotante.
- Esquema inicial aislado para revisión; todavía no se conecta a la base SAFE ni incluye un flujo operativo completo.
- Pruebas automatizadas para los casos de distribución.

## Requisitos propuestos
- PHP 8.4 o posterior; objetivo PHP 8.5 si cPanel lo ofrece y se valida toda la matriz de extensiones.
- Extensiones PDO y PDO MySQL.
- Composer 2 en desarrollo/CI. No se necesita Composer en cada petición de producción.
- MySQL/MariaDB con InnoDB y soporte de claves foráneas.

## Desarrollo local
1. Copiar `.env.example` a un archivo local fuera del directorio público; no subir secretos.
2. Instalar dependencias en un entorno de desarrollo con `composer install`.
3. Ejecutar pruebas con `composer test`.
4. Revisar migraciones en una base de datos desechable, nunca en la base original.
5. No hay conexión ni migración automática de la base legacy en este esqueleto.

## Estado de implementación
Esto es un primer bloque técnico, no un sistema financiero listo para producción. Faltan autenticación completa, autorización, persistencia transaccional, productos financieros configurables, cálculo diario de interés/mora, planes de amortización, aprobaciones, contabilidad de partida doble, captaciones, caja integral, migración y pruebas de aceptación.

## Política preliminar de abonos extraordinarios
Un abono a capital **no recalcula por sí solo** cuota o plazo. Debe existir una solicitud/selección, autorización por rol, registro de la decisión (reducir cuota, reducir plazo o alternativa permitida por producto), nueva versión del plan de pagos y asiento de ajuste. El plan anterior permanece como historial.

## Seguridad
No cargar, incluir ni reutilizar los archivos de configuración de Windows de la aplicación antigua. No ejecutar SQL del repositorio antiguo contra producción. No almacenar credenciales en Git. Toda reversión de un movimiento contabilizado deberá ser un movimiento compensatorio autorizado.
