<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$flightId = (int)($_GET['id'] ?? 0);
$flight = null;
$promotion = null;
$error = null;

try {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT v.*, a.nombre AS aerolinea_nombre, a.codigo AS aerolinea_codigo,
                p.id AS promocion_id, p.titulo AS promocion_titulo,
                p.descuento_porcentaje
         FROM vuelos v
         JOIN aerolineas a ON a.id = v.aerolinea_id
         LEFT JOIN promociones p ON p.aerolinea_id = a.id
             AND p.estado = 'vigente'
             AND (p.fecha_inicio IS NULL OR p.fecha_inicio <= CURDATE())
             AND (p.fecha_fin IS NULL OR p.fecha_fin >= CURDATE())
         WHERE v.id = ? AND v.estado = 'programado'
         LIMIT 1"
    );
    $stmt->execute([$flightId]);
    $flight = $stmt->fetch();
    if (!$flight) {
        $error = 'El vuelo no existe o ya no está disponible.';
    }
} catch (PDOException $e) {
    $error = 'No se pudo cargar el detalle del vuelo.';
}

$pageTitle = $flight ? 'Detalle ' . $flight['codigo'] : 'Detalle de vuelo';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header"><div class="container"><h1>Detalle del vuelo</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/publico/buscar.php') ?>">Buscar vuelos</a></li><li class="breadcrumb-item active">Detalle</li></ol></nav></div></section>
    <section class="section"><div class="container">
        <?php if ($error): ?>
            <div class="volara-alert alert-danger" role="alert"><?= e($error) ?></div>
            <a href="<?= url('pages/publico/buscar.php') ?>" class="btn btn-volara">Volver a buscar</a>
        <?php else: ?>
            <?php $discount = (float)($flight['descuento_porcentaje'] ?? 0); $finalPrice = (float)$flight['precio'] * (1 - $discount / 100); ?>
            <div class="flight-detail-layout">
                <article class="volara-card flight-detail-card">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><span class="text-muted small"><?= e($flight['aerolinea_nombre']) ?> · <?= e($flight['aerolinea_codigo']) ?></span><h2 class="h3 mb-0 mt-1"><?= e($flight['codigo']) ?></h2></div><span class="volara-badge <?= badgeClass($flight['clase']) ?>"><?= estadoLabel($flight['clase']) ?></span></div>
                    <div class="flight-detail-route"><div><strong><?= e($flight['origen_codigo']) ?></strong><span><?= e($flight['origen']) ?></span><time><?= formatTime($flight['fecha_salida']) ?></time></div><div class="flight-detail-line"><i class="bi bi-airplane" aria-hidden="true"></i><span><?= flightDuration($flight['fecha_salida'], $flight['fecha_llegada']) ?></span></div><div><strong><?= e($flight['destino_codigo']) ?></strong><span><?= e($flight['destino']) ?></span><time><?= formatTime($flight['fecha_llegada']) ?></time></div></div>
                    <dl class="flight-detail-info"><div><dt>Salida</dt><dd><?= formatDate($flight['fecha_salida']) ?></dd></div><div><dt>Clase</dt><dd><?= estadoLabel($flight['clase']) ?></dd></div><div><dt>Asientos</dt><dd><?= (int)$flight['asientos_disponibles'] ?> disponibles</dd></div><div><dt>Avión</dt><dd><?= e($flight['avion_modelo'] ?: 'A confirmar') ?></dd></div></dl>
                </article>
                <aside class="volara-card flight-summary-card"><span class="eyebrow">Tu viaje</span><h2 class="h4 mt-2">Elegí tu asiento</h2><p class="text-muted">Seleccioná un lugar disponible para continuar con la reserva.</p><?php if ($discount > 0): ?><div class="volara-alert alert-success py-2"><i class="bi bi-tag" aria-hidden="true"></i> <?= number_format($discount, 0) ?>% de descuento aplicado</div><?php endif; ?><div class="flight-summary-price"><span>Precio final</span><strong><?= formatPrice($finalPrice) ?></strong><?php if ($discount > 0): ?><del><?= formatPrice((float)$flight['precio']) ?></del><?php endif; ?></div><?php if (isLoggedIn()): ?><a href="<?= url('pages/usuario/seleccion-asiento.php?vuelo_id=' . $flight['id']) ?>" class="btn btn-volara w-100 btn-volara-lg"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i> Elegir asiento</a><?php else: ?><a href="<?= url('auth/login.php') ?>" class="btn btn-volara w-100 btn-volara-lg">Iniciá sesión para reservar</a><?php endif; ?></aside>
            </div>
        <?php endif; ?>
    </div></section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
