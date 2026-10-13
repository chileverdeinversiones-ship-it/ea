<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('America/Guatemala');

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$today = new DateTimeImmutable('now', new DateTimeZone('America/Guatemala'));
$demo = [
    'portfolio' => '2,845,630.00',
    'savings' => '4,218,900.00',
    'overdue' => '186,450.00',
    'cash' => '38,740.00',
    'loans' => 284,
    'applications' => 18,
    'collections' => '24,850.00',
    'members' => 1248,
];

$modules = [
    ['id' => 'dashboard', 'label' => 'Inicio', 'icon' => '⌂', 'group' => 'GENERAL'],
    ['id' => 'clients', 'label' => 'Clientes y socios', 'icon' => '♙', 'group' => 'GENERAL'],
    ['id' => 'credit', 'label' => 'Créditos', 'icon' => '▤', 'group' => 'FINANZAS', 'count' => '18'],
    ['id' => 'cash', 'label' => 'Caja y cobros', 'icon' => 'Q', 'group' => 'FINANZAS'],
    ['id' => 'savings', 'label' => 'Captaciones', 'icon' => '◉', 'group' => 'FINANZAS'],
    ['id' => 'accounting', 'label' => 'Contabilidad', 'icon' => '▥', 'group' => 'FINANZAS'],
    ['id' => 'treasury', 'label' => 'Bancos y tesorería', 'icon' => '⇄', 'group' => 'FINANZAS'],
    ['id' => 'reports', 'label' => 'Reportes', 'icon' => '▧', 'group' => 'CONTROL'],
    ['id' => 'audit', 'label' => 'Auditoría', 'icon' => '◈', 'group' => 'CONTROL'],
    ['id' => 'settings', 'label' => 'Configuración', 'icon' => '⚙', 'group' => 'SISTEMA'],
];
?>
<!doctype html>
<html lang="es-GT">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#10243d">
<title>SAFE Moderno | Plataforma financiera</title>
<style>
:root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#17263d;background:#f4f7fb;font-synthesis:none;text-rendering:optimizeLegibility}
*{box-sizing:border-box}body{margin:0}button,input,select{font:inherit}button{cursor:pointer}
:focus-visible{outline:3px solid #53b8b0;outline-offset:2px}
.shell{min-height:100vh;display:grid;grid-template-columns:252px minmax(0,1fr)}
.sidebar{background:#10243d;color:#d6e1ef;display:flex;flex-direction:column;padding:22px 14px;gap:25px;min-height:100vh}
.brand{display:flex;align-items:center;gap:11px;padding:0 9px}.brand-mark{height:42px;width:42px;border-radius:13px;background:linear-gradient(145deg,#39b9ac,#16827f);display:grid;place-items:center;color:white;font-size:22px;font-weight:900;box-shadow:0 7px 18px #061b2a66}.brand-name{font-weight:850;letter-spacing:.07em;color:#fff;font-size:16px}.brand-sub{font-size:10px;color:#9fb2c9;margin-top:4px;letter-spacing:.09em}
.nav-group{font-size:10px;letter-spacing:.15em;font-weight:800;color:#7f96b0;padding:0 12px 8px}.nav{display:grid;gap:4px}.nav button{border:0;background:transparent;color:#b8c9dd;text-align:left;border-radius:10px;padding:11px 12px;display:flex;gap:12px;align-items:center;width:100%;font-size:13px;transition:.15s}.nav button:hover{background:#ffffff0d;color:white}.nav button.active{background:linear-gradient(90deg,#1c827f55,#1c827f12);color:#7ce0d3;box-shadow:inset 3px 0 #43c7b8}.nav-icon{width:20px;text-align:center;font-size:17px}.nav-count{margin-left:auto;font-size:10px;background:#c98d36;color:#fff;border-radius:20px;padding:3px 7px}.sidebar-bottom{margin-top:auto}.branch-card{padding:13px;background:#ffffff0b;border:1px solid #ffffff12;border-radius:12px;font-size:12px}.branch-card strong{display:block;color:white;margin-bottom:5px}.status-dot{display:inline-block;height:7px;width:7px;border-radius:50%;background:#48d2a8;margin-right:6px}
main{min-width:0}.topbar{height:76px;background:#fff;border-bottom:1px solid #e7edf4;display:flex;align-items:center;justify-content:space-between;padding:0 clamp(18px,3vw,38px);gap:18px;position:sticky;top:0;z-index:5}.top-left{display:flex;align-items:center;gap:14px;min-width:0}.hamburger{display:none;border:0;background:#f1f5f9;border-radius:9px;padding:8px 10px}.breadcrumb{font-size:12px;color:#8190a4}.page-title{font-size:17px;font-weight:800;margin-top:4px}.top-actions{display:flex;align-items:center;gap:12px}.search{position:relative}.search input{width:min(26vw,280px);border:1px solid #e1e8f0;border-radius:10px;padding:10px 12px 10px 35px;background:#f8fafc;font-size:12px}.search span{position:absolute;left:12px;top:9px;color:#8493a6}.icon-btn{height:38px;width:38px;border:1px solid #e3eaf2;border-radius:11px;background:#fff;color:#53657c;position:relative}.notification-dot{position:absolute;right:8px;top:7px;width:7px;height:7px;border-radius:50%;background:#ed7b68;border:1px solid white}.avatar{height:37px;width:37px;display:grid;place-items:center;background:#d8f2ed;color:#14766e;border-radius:12px;font-weight:800;font-size:12px}
.content{padding:27px clamp(18px,3vw,38px) 35px;max-width:1600px;margin:0 auto}.welcome{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:22px}.eyebrow{font-size:10px;font-weight:850;letter-spacing:.15em;color:#168c84;text-transform:uppercase}.welcome h1{font-size:clamp(23px,2.2vw,30px);letter-spacing:-.04em;margin:7px 0 7px;color:#14253d}.welcome p{font-size:13px;color:#78889d;margin:0}.welcome-actions{display:flex;gap:9px;flex-wrap:wrap}.btn{border:1px solid #dce5ee;border-radius:10px;background:white;padding:10px 13px;color:#40536b;font-size:12px;font-weight:750;display:inline-flex;align-items:center;gap:8px}.btn-primary{background:#177f7c;color:white;border-color:#177f7c;box-shadow:0 5px 12px #177f7c22}.btn:hover{filter:brightness(.98);transform:translateY(-1px)}
.demo-banner{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;background:#fff8e8;border:1px solid #f3dfb3;color:#76571d;font-size:11px;margin-bottom:18px}.demo-banner strong{white-space:nowrap}
.kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px}.kpi{background:#fff;border:1px solid #e6ecf3;border-radius:15px;padding:17px 18px;min-width:0;box-shadow:0 4px 15px #17385b05}.kpi-head{display:flex;align-items:center;justify-content:space-between;gap:8px;color:#718198;font-size:11px;font-weight:650}.kpi-icon{height:33px;width:33px;display:grid;place-items:center;border-radius:10px;background:#e8f7f4;color:#19877f;font-size:17px}.kpi:nth-child(2) .kpi-icon{background:#eef1ff;color:#5d68bf}.kpi:nth-child(3) .kpi-icon{background:#fff2e7;color:#c67b36}.kpi:nth-child(4) .kpi-icon{background:#e9f3ff;color:#357cc0}.kpi-value{font-size:clamp(20px,2vw,27px);font-weight:850;letter-spacing:-.04em;margin:13px 0 8px;white-space:nowrap}.kpi-foot{font-size:10px;color:#8b99aa;display:flex;align-items:center;gap:5px}.trend{color:#168d6f;font-weight:800}.trend.warn{color:#c27628}
.section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:26px 0 13px}.section-head h2{font-size:15px;margin:0;letter-spacing:-.02em}.section-head p{font-size:11px;color:#8a98aa;margin:5px 0 0}.link-btn{border:0;background:transparent;color:#16847f;font-size:11px;font-weight:800}
.main-grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(280px,1fr);gap:15px}.panel{background:#fff;border:1px solid #e5ebf2;border-radius:15px;padding:18px;min-width:0}.panel-title{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.panel-title h3{font-size:13px;margin:0}.panel-title p{font-size:10px;color:#8b99aa;margin:5px 0 0}.period{font-size:10px;border:1px solid #e3eaf2;border-radius:8px;background:#fff;padding:7px;color:#65758b}
.chart{width:100%;height:192px;margin-top:12px;display:block}.chart-grid{stroke:#e9eff5;stroke-dasharray:4 5}.chart-label{font-size:9px;fill:#9aa7b6}.chart-area{fill:url(#areaFill)}.chart-line{fill:none;stroke:#188e86;stroke-width:3;stroke-linecap:round;stroke-linejoin:round}.chart-line2{fill:none;stroke:#9ca8e9;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:5 5}.legend{display:flex;gap:15px;margin-top:6px;font-size:10px;color:#8290a2}.legend i{display:inline-block;width:7px;height:7px;border-radius:50%;margin-right:5px;background:#188e86}.legend span:nth-child(2) i{background:#9ca8e9}
.progress-list{display:grid;gap:19px;margin-top:21px}.progress-row{display:grid;gap:8px}.progress-meta{display:flex;align-items:center;justify-content:space-between;font-size:11px}.progress-meta span:first-child{color:#697b90}.progress-meta strong{font-size:11px}.track{height:7px;border-radius:20px;background:#edf2f7;overflow:hidden}.fill{height:100%;border-radius:20px;background:#188e86}.fill.orange{background:#e3a14e}.fill.blue{background:#6c82d9}.fill.red{background:#dc7b72}
.quick-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.quick-action{border:1px solid #e4ebf2;background:#fff;border-radius:13px;padding:15px;text-align:left;min-height:115px;transition:.15s}.quick-action:hover{border-color:#86cbc5;box-shadow:0 6px 18px #164f4c0b;transform:translateY(-2px)}.quick-icon{height:34px;width:34px;border-radius:10px;display:grid;place-items:center;background:#e9f7f4;color:#167e78;font-size:17px;margin-bottom:12px}.quick-action strong{display:block;font-size:12px}.quick-action small{display:block;color:#8b99aa;font-size:10px;margin-top:5px;line-height:1.4}
.bottom-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,1fr);gap:15px;margin-top:15px}.table-wrap{overflow-x:auto;margin-top:14px}table{width:100%;border-collapse:collapse;text-align:left;white-space:nowrap}th{font-size:9px;color:#92a0b0;font-weight:800;letter-spacing:.07em;text-transform:uppercase;padding:10px 9px;border-bottom:1px solid #edf1f6}td{font-size:11px;color:#52647b;padding:12px 9px;border-bottom:1px solid #f0f3f7}tbody tr:last-child td{border-bottom:0}.person{display:flex;align-items:center;gap:9px}.person-avatar{height:31px;width:31px;display:grid;place-items:center;border-radius:10px;background:#edf3fb;color:#5874a4;font-size:10px;font-weight:800}.person strong{display:block;color:#293b52;font-size:11px}.person small{display:block;color:#9aa6b4;font-size:9px;margin-top:3px}.pill{display:inline-flex;padding:5px 8px;border-radius:30px;font-size:9px;font-weight:800}.pill.green{background:#e5f7ef;color:#16825e}.pill.orange{background:#fff1dc;color:#ae6c19}.pill.blue{background:#eaf1ff;color:#4f6cc1}.pill.red{background:#ffebe9;color:#bc554c}
.activity{display:grid;gap:17px;margin-top:18px}.activity-item{display:flex;gap:10px;align-items:flex-start}.activity-icon{height:30px;width:30px;border-radius:9px;background:#e7f6f2;color:#19867d;display:grid;place-items:center;flex:none}.activity-copy{flex:1;min-width:0;font-size:11px;color:#5c6f84;line-height:1.5}.activity-copy strong{color:#293c52}.activity-copy small{display:block;color:#9aa6b4;font-size:9px;margin-top:3px}
.module-page{background:white;border:1px solid #e4ebf2;border-radius:16px;padding:clamp(20px,4vw,36px);min-height:390px}.module-page h2{font-size:22px;margin:8px 0}.module-page p{color:#718198;max-width:720px;line-height:1.65;font-size:13px}.module-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;margin-top:22px}.module-card{border:1px solid #e4ebf2;border-radius:12px;padding:16px}.module-card strong{font-size:12px}.module-card p{font-size:11px;margin:7px 0 0}
.toast{position:fixed;right:22px;bottom:22px;z-index:20;max-width:360px;padding:13px 16px;background:#10243d;color:white;border-radius:12px;box-shadow:0 10px 35px #0d223b33;font-size:12px;display:none}.toast.show{display:block;animation:rise .2s ease-out}@keyframes rise{from{transform:translateY(8px);opacity:.3}to{transform:none;opacity:1}}
.mobile-overlay{display:none}
@media(max-width:1100px){.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.main-grid,.bottom-grid{grid-template-columns:1fr}.search input{width:200px}}
@media(max-width:760px){.shell{display:block}.sidebar{position:fixed;left:-270px;top:0;bottom:0;width:252px;z-index:15;transition:left .2s;overflow-y:auto}.sidebar.open{left:0}.mobile-overlay.open{display:block;position:fixed;inset:0;background:#0b1d33a8;z-index:10}.hamburger{display:block}.topbar{height:66px;padding:0 15px}.search{display:none}.content{padding:21px 14px}.welcome{display:block}.welcome-actions{margin-top:15px}.kpis{gap:10px}.kpi{padding:13px}.kpi-value{font-size:20px}.kpi-head{font-size:10px}.kpi-foot{line-height:1.5}.quick-grid{gap:9px}.quick-action{padding:12px}.panel{padding:14px}.user-label{display:none}}
@media(max-width:390px){.kpis{grid-template-columns:1fr 1fr}.kpi-value{font-size:18px}.quick-grid{grid-template-columns:1fr 1fr}.welcome h1{font-size:23px}}
</style>
</head>
<body>
<div class="shell">
<aside class="sidebar" id="sidebar">
  <div class="brand"><div class="brand-mark">S</div><div><div class="brand-name">SAFE MODERNO</div><div class="brand-sub">PLATAFORMA FINANCIERA</div></div></div>
  <nav class="nav" aria-label="Navegación principal">
    <div class="nav-group">GENERAL</div>
    <?php foreach ($modules as $module): ?>
      <?php if ($module['group'] === 'FINANZAS'): ?><div class="nav-group" style="margin-top:15px">FINANZAS</div><?php elseif ($module['group'] === 'CONTROL'): ?><div class="nav-group" style="margin-top:15px">CONTROL Y AUDITORÍA</div><?php elseif ($module['group'] === 'SISTEMA'): ?><div class="nav-group" style="margin-top:15px">SISTEMA</div><?php endif; ?>
      <button type="button" class="nav-item<?= $module['id'] === 'dashboard' ? ' active' : '' ?>" data-module="<?= e($module['id']) ?>" data-label="<?= e($module['label']) ?>"><span class="nav-icon"><?= e($module['icon']) ?></span><span><?= e($module['label']) ?></span><?php if (isset($module['count'])): ?><span class="nav-count"><?= e($module['count']) ?></span><?php endif; ?></button>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-bottom"><div class="branch-card"><strong><span class="status-dot"></span>Entorno de demostración</strong><span style="color:#8fa4bc;font-size:10px">Sin conexión a la base financiera</span></div></div>
</aside>
<div class="mobile-overlay" id="overlay"></div>
<main>
<header class="topbar">
  <div class="top-left"><button class="hamburger" id="menuToggle" aria-label="Abrir menú">☰</button><div><div class="breadcrumb">SAFE / <span id="crumb">General</span></div><div class="page-title" id="pageTitle">Panel principal</div></div></div>
  <div class="top-actions">
    <label class="search"><span>⌕</span><input id="globalSearch" type="search" placeholder="Buscar módulo o función..." aria-label="Buscar módulo o función"></label>
    <button class="icon-btn" id="helpButton" aria-label="Ayuda" title="Ayuda">?</button>
    <button class="icon-btn" id="notificationButton" aria-label="Notificaciones" title="Notificaciones">♧<span class="notification-dot"></span></button>
    <div class="avatar" aria-label="Usuario de demostración">AD</div><div class="user-label"><div style="font-size:11px;font-weight:800">Administración</div><div style="font-size:10px;color:#8a98aa;margin-top:3px">Perfil de demostración</div></div>
  </div>
</header>
<div class="content" id="mainContent">
  <section class="welcome">
    <div><div class="eyebrow">Panel de control · Guatemala</div><h1>Buenos días, equipo SAFE <span style="font-size:22px">✦</span></h1><p><?= e($today->format('d/m/Y')) ?> · <?= e($today->format('H:i')) ?> hora de Guatemala · Resumen de demostración</p></div>
    <div class="welcome-actions"><button class="btn" id="reportsButton">↗ Exportar resumen</button><button class="btn btn-primary" data-module="credit-new">＋ Nueva solicitud</button></div>
  </section>
  <div class="demo-banner"><strong>ⓘ MODO DEMOSTRACIÓN</strong><span>Las cifras son ficticias para diseño visual. No representan saldos reales y no se pueden registrar operaciones desde esta pantalla.</span></div>
  <section class="kpis" aria-label="Indicadores">
    <article class="kpi"><div class="kpi-head"><span>Cartera de créditos</span><span class="kpi-icon">▤</span></div><div class="kpi-value">Q<?= e($demo['portfolio']) ?></div><div class="kpi-foot"><span class="trend">↗ 8.4%</span><span>frente al mes anterior · demo</span></div></article>
    <article class="kpi"><div class="kpi-head"><span>Captaciones totales</span><span class="kpi-icon">◉</span></div><div class="kpi-value">Q<?= e($demo['savings']) ?></div><div class="kpi-foot"><span class="trend">↗ 4.2%</span><span>frente al mes anterior · demo</span></div></article>
    <article class="kpi"><div class="kpi-head"><span>Cartera en mora</span><span class="kpi-icon">⌁</span></div><div class="kpi-value">Q<?= e($demo['overdue']) ?></div><div class="kpi-foot"><span class="trend warn">6.55%</span><span>de la cartera · indicador demo</span></div></article>
    <article class="kpi"><div class="kpi-head"><span>Disponibilidad de caja</span><span class="kpi-icon">Q</span></div><div class="kpi-value">Q<?= e($demo['cash']) ?></div><div class="kpi-foot"><span><?= e((string)$demo['loans']) ?> créditos activos</span><span>· dato ficticio</span></div></article>
  </section>
  <div class="section-head"><div><h2>Vista financiera</h2><p>Indicadores de referencia para la gestión de la cooperativa</p></div><button class="link-btn" data-module="reports">Ver reportes completos →</button></div>
  <section class="main-grid">
    <article class="panel">
      <div class="panel-title"><div><h3>Evolución de cartera y captaciones</h3><p>Últimos 6 meses · valores ilustrativos</p></div><select class="period" id="chartPeriod" aria-label="Período del gráfico"><option>Últimos 6 meses</option><option>Últimos 12 meses</option></select></div>
      <svg class="chart" viewBox="0 0 640 205" role="img" aria-label="Gráfico ilustrativo de evolución de cartera y captaciones">
        <defs><linearGradient id="areaFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#188e86" stop-opacity=".18"/><stop offset="100%" stop-color="#188e86" stop-opacity="0"/></linearGradient></defs>
        <line class="chart-grid" x1="40" y1="28" x2="620" y2="28"/><line class="chart-grid" x1="40" y1="72" x2="620" y2="72"/><line class="chart-grid" x1="40" y1="116" x2="620" y2="116"/><line class="chart-grid" x1="40" y1="160" x2="620" y2="160"/>
        <text class="chart-label" x="0" y="31">Q5M</text><text class="chart-label" x="0" y="75">Q4M</text><text class="chart-label" x="0" y="119">Q3M</text><text class="chart-label" x="0" y="163">Q2M</text>
        <path class="chart-area" d="M40 143 L150 126 L260 112 L370 86 L480 69 L600 43 L600 174 L40 174 Z"/>
        <path class="chart-line" d="M40 143 L150 126 L260 112 L370 86 L480 69 L600 43"/>
        <path class="chart-line2" d="M40 158 L150 146 L260 132 L370 121 L480 100 L600 83"/>
        <text class="chart-label" x="40" y="194">MAY</text><text class="chart-label" x="150" y="194">JUN</text><text class="chart-label" x="260" y="194">JUL</text><text class="chart-label" x="370" y="194">AGO</text><text class="chart-label" x="480" y="194">SEP</text><text class="chart-label" x="590" y="194">OCT</text>
      </svg>
      <div class="legend"><span><i></i>Cartera de créditos</span><span><i></i>Captaciones</span></div>
    </article>
    <article class="panel">
      <div class="panel-title"><div><h3>Indicadores de cartera</h3><p>Seguimiento de riesgo · demo</p></div><button class="link-btn" data-module="credit">Detalle →</button></div>
      <div class="progress-list">
        <div class="progress-row"><div class="progress-meta"><span>Cartera vigente</span><strong>78.4%</strong></div><div class="track"><div class="fill" style="width:78.4%"></div></div></div>
        <div class="progress-row"><div class="progress-meta"><span>Cartera en riesgo temprano</span><strong>11.2%</strong></div><div class="track"><div class="fill orange" style="width:11.2%"></div></div></div>
        <div class="progress-row"><div class="progress-meta"><span>Cartera vencida</span><strong>6.55%</strong></div><div class="track"><div class="fill red" style="width:6.55%"></div></div></div>
        <div class="progress-row"><div class="progress-meta"><span>En análisis / aprobación</span><strong><?= e((string)$demo['applications']) ?> solicitudes</strong></div><div class="track"><div class="fill blue" style="width:42%"></div></div></div>
      </div>
    </article>
  </section>
  <div class="section-head"><div><h2>Accesos rápidos</h2><p>Las tareas frecuentes a un clic</p></div></div>
  <section class="quick-grid" aria-label="Accesos rápidos">
    <button class="quick-action" data-module="credit-new"><span class="quick-icon">＋</span><strong>Nueva solicitud</strong><small>Iniciar expediente de crédito</small></button>
    <button class="quick-action" data-module="cash"><span class="quick-icon">Q</span><strong>Recibir pago</strong><small>Cobro por monto entregado</small></button>
    <button class="quick-action" data-module="clients"><span class="quick-icon">♙</span><strong>Registrar socio</strong><small>Crear expediente de cliente</small></button>
    <button class="quick-action" data-module="savings"><span class="quick-icon">◉</span><strong>Captación</strong><small>Depósito, retiro o consulta</small></button>
  </section>
  <section class="bottom-grid">
    <article class="panel">
      <div class="panel-title"><div><h3>Actividad reciente</h3><p>Movimientos de muestra; no son transacciones reales</p></div><button class="link-btn" data-module="audit">Ver bitácora →</button></div>
      <div class="table-wrap"><table><thead><tr><th>Socio / referencia</th><th>Operación</th><th>Monto</th><th>Estado</th></tr></thead><tbody>
        <tr><td><div class="person"><span class="person-avatar">MR</span><div><strong>María R. López</strong><small>CR-2026-0148</small></div></div></td><td>Abono de crédito</td><td>Q1,250.00</td><td><span class="pill green">Aplicado · demo</span></td></tr>
        <tr><td><div class="person"><span class="person-avatar">JC</span><div><strong>José C. Pérez</strong><small>AH-000842</small></div></div></td><td>Depósito ahorro</td><td>Q500.00</td><td><span class="pill blue">Muestra</span></td></tr>
        <tr><td><div class="person"><span class="person-avatar">AL</span><div><strong>Ana L. Méndez</strong><small>CR-2026-0152</small></div></div></td><td>Solicitud nueva</td><td>Q25,000.00</td><td><span class="pill orange">En análisis</span></td></tr>
        <tr><td><div class="person"><span class="person-avatar">DP</span><div><strong>Daniel P. Ruiz</strong><small>CAJ-000391</small></div></div></td><td>Arqueo de caja</td><td>Q8,450.00</td><td><span class="pill red">Pendiente · demo</span></td></tr>
      </tbody></table></div>
    </article>
    <article class="panel">
      <div class="panel-title"><div><h3>Centro de alertas</h3><p>Ejemplos de tareas pendientes</p></div><span class="pill orange">3 alertas demo</span></div>
      <div class="activity">
        <div class="activity-item"><div class="activity-icon">⌁</div><div class="activity-copy"><strong>Seguimiento de cartera vencida</strong><br>Revisar expediente y promesa de pago de ejemplo.<small>Créditos · prioridad alta</small></div></div>
        <div class="activity-item"><div class="activity-icon" style="background:#fff2df;color:#b5792d">✓</div><div class="activity-copy"><strong>Solicitudes pendientes de revisión</strong><br>Verificar documentos, capacidad de pago y las 5 C.<small>Créditos · requiere asignación</small></div></div>
        <div class="activity-item"><div class="activity-icon" style="background:#edf1ff;color:#6174c4">▥</div><div class="activity-copy"><strong>Cierre y conciliación</strong><br>Conciliar caja y movimientos antes de contabilizar.<small>Contabilidad · control operativo</small></div></div>
      </div>
    </article>
  </section>
  <footer style="padding-top:25px;color:#9aa6b5;font-size:10px">SAFE Moderno Guatemala · Prototipo de interfaz · <?= e($today->format('Y')) ?> · Las operaciones financieras aún no están habilitadas.</footer>
</div>
</main>
</div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script>
(function(){
 const sidebar=document.getElementById('sidebar'),overlay=document.getElementById('overlay'),toast=document.getElementById('toast');
 let toastTimer;
 const moduleInfo={
 dashboard:{title:'Panel principal',desc:'Indicadores generales, alertas y accesos rápidos.'},
 clients:{title:'Clientes y socios',desc:'Expedientes, identificación, documentos, beneficiarios, referencias y estado de afiliación.'},
 credit:{title:'Gestión integral de créditos',desc:'Solicitudes, análisis de las 5 C, capacidad de pago, comité, avalúos, garantías, desembolsos, plan de pagos, mora y recuperación.'},
 'credit-new':{title:'Nueva solicitud de crédito',desc:'Flujo de captura y evaluación que se implementará con validaciones y permisos antes de habilitar desembolsos.'},
 cash:{title:'Caja y cobros',desc:'Registrar el monto recibido, previsualizar la distribución autorizada y emitir recibos trazables. La persistencia financiera aún no está conectada.'},
 savings:{title:'Captaciones y aportaciones',desc:'Ahorro a la vista, depósitos a plazo si aplican, aportaciones, retiros, intereses, vencimientos y beneficiarios.'},
 accounting:{title:'Contabilidad general',desc:'Catálogo de cuentas, partidas de doble entrada, diario, mayor, balance de comprobación, estados financieros y cierres.'},
 treasury:{title:'Bancos y tesorería',desc:'Cuentas bancarias, transferencias, conciliación, flujo de efectivo y control de movimientos.'},
 reports:{title:'Reportes e indicadores',desc:'Cartera, mora, vencimientos, captaciones, liquidez, resultados, caja y reportes para gerencia.'},
 audit:{title:'Auditoría y control',desc:'Bitácora, reversos autorizados, segregación de funciones, cierres y seguimiento de cambios.'},
 settings:{title:'Configuración institucional',desc:'Sucursales, cajas, productos, tasas, calendarios, usuarios, roles y parámetros con vigencia.'}
 };
 function notify(msg){toast.textContent=msg;toast.classList.add('show');clearTimeout(toastTimer);toastTimer=setTimeout(()=>toast.classList.remove('show'),3800)}
 function closeMenu(){sidebar.classList.remove('open');overlay.classList.remove('open')}
 function openModule(id){
  if(!moduleInfo[id]){notify('Esta función está en la hoja de ruta; no se habilitarán operaciones sin validaciones y permisos.');return}
  document.querySelectorAll('.nav-item').forEach(b=>b.classList.toggle('active',b.dataset.module===id));
  document.getElementById('pageTitle').textContent=moduleInfo[id].title;
  document.getElementById('crumb').textContent=id==='dashboard'?'General':(id==='credit-new'?'Créditos':moduleInfo[id].title);
  closeMenu();
  if(id==='dashboard'){location.hash='';document.getElementById('mainContent').innerHTML=dashboardMarkup;wireDynamic();return}
  location.hash=id;
  const c=document.getElementById('mainContent');
  c.innerHTML='<section class="module-page"><div class="eyebrow">MÓDULO · SAFE MODERNO</div><h2>'+escapeHtml(moduleInfo[id].title)+'</h2><p>'+escapeHtml(moduleInfo[id].desc)+'</p><div class="demo-banner" style="margin-top:20px"><strong>EN CONSTRUCCIÓN</strong><span>Esta vista es una maqueta de navegación. No guarda datos, no calcula saldos reales y no procesa pagos.</span></div><div class="module-cards">'+cardsFor(id)+'</div><div style="margin-top:22px"><button class="btn" data-module="dashboard">← Volver al panel</button></div></section>';
  c.querySelectorAll('[data-module]').forEach(b=>b.addEventListener('click',()=>openModule(b.dataset.module)));
 }
 function escapeHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
 function cardsFor(id){
  const groups={
   clients:[['Expedientes','Datos personales y de negocio, DPI/CUI y NIT según corresponda.'],['Documentos','Identidad, dirección, ingresos, consentimientos y referencias.'],['Socios','Afiliación, aportaciones, beneficiarios y estado.']],
   credit:[['Solicitud y análisis','Monto, destino, plazo, flujo de ingresos y capacidad de pago.'],['Las 5 C','Carácter, capacidad, capital, colateral y condiciones.'],['Avalúos y garantías','Titularidad, valuación, cobertura, documentos y vencimientos.'],['Plan de pagos','Interés sobre saldos, cuotas, calendario y versión aprobada.'],['Comité y desembolso','Límites de aprobación, segregación y desembolso autorizado.'],['Mora y recuperación','Alertas, gestión de cobro, acuerdos y reestructuración.']],
   'credit-new':[['Datos de solicitud','Socio, producto, monto, destino y plazo.'],['Expediente','Identidad, ingresos, egresos y documentos requeridos.'],['Evaluación','Capacidad de pago, las 5 C y recomendación.']],
   cash:[['Cobro por monto','Ingresar el monto recibido y mostrar desglose previo.'],['Recibo y auditoría','Número único, reimpresión trazable y reverso autorizado.'],['Cierre de caja','Arqueo, diferencias, autorización y conciliación.']],
   savings:[['Cuentas y aportaciones','Aperturas, productos, saldos y beneficiarios.'],['Movimientos','Depósitos, retiros y transferencias autorizadas.'],['Intereses y vencimientos','Reglas por producto y calendario de vencimientos.']],
   accounting:[['Libro diario y mayor','Partidas balanceadas y consulta por período.'],['Estados financieros','Balance, resultados y balance de comprobación.'],['Cierres','Períodos, bloqueos, conciliación y ajustes autorizados.']],
   treasury:[['Bancos','Cuentas, movimientos y transferencias.'],['Conciliación','Cruce de extractos con operaciones contabilizadas.'],['Liquidez','Posición diaria, proyección y alertas.']],
   reports:[['Cartera','Saldo, vencimientos, mora, concentración y recuperación.'],['Captaciones','Saldos, movimientos, retiros y vencimientos.'],['Gerencia','Liquidez, resultados, gastos y tendencias.']],
   audit:[['Bitácora','Actor, fecha, acción, referencia y correlación.'],['Autorizaciones','Separación entre solicitante, aprobador y ejecutor.'],['Reversos','Corrección mediante movimientos compensatorios.']],
   settings:[['Productos','Tasas, plazos, frecuencia y vigencia de reglas.'],['Usuarios y roles','Permisos mínimos y revisión de accesos.'],['Institución','Sucursales, cajas, calendario y parámetros locales.']]
  };
  return (groups[id]||[]).map(x=>'<article class="module-card"><strong>'+escapeHtml(x[0])+'</strong><p>'+escapeHtml(x[1])+'</p></article>').join('')
 }
 const dashboardMarkup=document.getElementById('mainContent').innerHTML;
 function wireDynamic(){
  document.querySelectorAll('#mainContent [data-module]').forEach(b=>b.addEventListener('click',()=>openModule(b.dataset.module)));
 }
 document.querySelectorAll('.nav-item').forEach(b=>b.addEventListener('click',()=>openModule(b.dataset.module)));
 document.querySelectorAll('[data-module]:not(.nav-item)').forEach(b=>b.addEventListener('click',()=>openModule(b.dataset.module)));
 document.getElementById('menuToggle').addEventListener('click',()=>{sidebar.classList.add('open');overlay.classList.add('open')});
 overlay.addEventListener('click',closeMenu);
 document.getElementById('helpButton').addEventListener('click',()=>notify('SAFE Moderno está en desarrollo. Esta interfaz usa datos ficticios y no procesa operaciones reales.'));
 document.getElementById('notificationButton').addEventListener('click',()=>notify('Las alertas mostradas son ejemplos de diseño, no tareas reales.'));
 document.getElementById('reportsButton').addEventListener('click',()=>notify('La exportación estará disponible cuando se implemente el módulo de reportes conectado a datos verificados.'));
 const search=document.getElementById('globalSearch');
 search.addEventListener('keydown',e=>{if(e.key==='Enter'){const q=search.value.trim().toLocaleLowerCase('es');const match=Object.entries(moduleInfo).find(([id,m])=>(m.title+' '+m.desc+' '+id).toLocaleLowerCase('es').includes(q));if(match)openModule(match[0]);else notify('No se encontró un módulo con ese término.')}})
 if(location.hash&&moduleInfo[location.hash.slice(1)])openModule(location.hash.slice(1));
})();
</script>
</body>
</html>
