<?php

declare(strict_types=1);

use SafeModern\Domain\Credit\PaymentAllocator;

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('America/Guatemala');

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

$appEnv = getenv('APP_ENV') ?: 'production';
$isDevelopment = $appEnv === 'local';

http_response_code(200);
?>
<!doctype html>
<html lang="es-GT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SAFE Moderno | Guatemala</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background:#f3f6fa; color:#15233b; }
        * { box-sizing:border-box; }
        body { margin:0; }
        header { background:#10233f; color:white; padding:1.2rem clamp(1rem,4vw,3rem); display:flex; align-items:center; justify-content:space-between; gap:1rem; }
        .brand { font-weight:800; letter-spacing:.04em; }
        .brand small { display:block; font-weight:400; color:#b8c8dc; letter-spacing:0; margin-top:.25rem; }
        main { max-width:1150px; margin:2rem auto; padding:0 1rem 2rem; }
        .intro { background:linear-gradient(125deg,#16385e,#167d86); color:white; border-radius:1.25rem; padding:clamp(1.4rem,4vw,2.5rem); }
        .intro h1 { margin:.4rem 0; font-size:clamp(1.7rem,4vw,2.6rem); }
        .intro p { max-width:700px; color:#e0eef4; line-height:1.6; }
        .eyebrow { text-transform:uppercase; font-size:.75rem; font-weight:700; letter-spacing:.12em; color:#c1f4e9; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; margin-top:1.3rem; }
        .card { background:white; border:1px solid #e1e8f0; border-radius:1rem; padding:1.2rem; box-shadow:0 5px 18px #1227440a; }
        .card h2 { font-size:1rem; margin:.5rem 0; }
        .card p { color:#5b6b81; font-size:.9rem; line-height:1.5; margin-bottom:0; }
        .icon { display:grid; place-items:center; width:2.5rem; height:2.5rem; border-radius:.75rem; background:#e8f5f4; color:#14757a; font-size:1.2rem; }
        .note { margin-top:1.25rem; padding:1rem 1.2rem; border-left:4px solid #d49a28; background:#fff8e9; border-radius:.5rem; line-height:1.5; }
        footer { color:#718096; font-size:.8rem; padding:1.5rem 0; }
    </style>
</head>
<body>
<header>
    <div class="brand">SAFE <span style="color:#70d5c7">MODERNO</span><small>Cooperativa de ahorro y crédito · Guatemala</small></div>
    <div aria-label="Estado de desarrollo">Base de aplicación</div>
</header>
<main>
    <section class="intro">
        <div class="eyebrow">Nueva plataforma financiera</div>
        <h1>Más control. Más claridad. Mejor servicio.</h1>
        <p>Una base nueva para integrar clientes, créditos, caja, captaciones y contabilidad con trazabilidad de cada operación y reglas financieras verificables.</p>
    </section>
    <section class="grid" aria-label="Módulos planificados">
        <article class="card"><div class="icon">♙</div><h2>Clientes y socios</h2><p>Expediente, identificación, documentos y control de datos duplicados.</p></article>
        <article class="card"><div class="icon">↗</div><h2>Créditos</h2><p>Solicitud, 5 C, capacidad de pago, avalúos, garantías y plan de amortización.</p></article>
        <article class="card"><div class="icon">Q</div><h2>Caja</h2><p>Pagos por monto recibido, aplicación detallada y recibos auditables.</p></article>
        <article class="card"><div class="icon">▤</div><h2>Captaciones</h2><p>Ahorros, aportaciones, depósitos y movimientos según producto.</p></article>
        <article class="card"><div class="icon">≋</div><h2>Contabilidad</h2><p>Partida doble, auxiliares, cierres y reportes financieros conciliados.</p></article>
        <article class="card"><div class="icon">◈</div><h2>Seguridad y auditoría</h2><p>Permisos, segregación de funciones, bitácora y reversos autorizados.</p></article>
    </section>
    <aside class="note"><strong>Entorno inicial:</strong> esta pantalla es un prototipo de presentación. No tiene inicio de sesión ni ejecuta operaciones financieras. No conectar ni publicar contra producción.</aside>
    <footer>SAFE Moderno · Zona horaria America/Guatemala · Moneda de presentación Q</footer>
</main>
</body>
</html>
