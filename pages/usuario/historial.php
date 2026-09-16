<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('pasajero');

$pageTitle = 'Historial de compras';
$user = currentUser();
$errors = [];
$history = [];

try {
    $db = getDB();
    $countStmt = $db->prepare("SELECT COUNT(*) FROM reservas WHERE usuario_id = ? AND estado IN ('confirmada', 'cancelada')");
    $countStmt->execute([$user['id']]);
    $pagination = paginate((int)$countStmt->fetchColumn(), max(1, (int)($_GET['p'] ?? 1)));

    $stmt = $db->prepare(
        "SELECT r.*, v.codigo AS vuelo_codigo, v.origen_codigo, v.destino_codigo,
                v.fecha_salida, a.nombre AS aerolinea_nombre
         FROM reservas r
         JOIN vuelos v ON v.id = r.vuelo_id
         JOIN aerolineas a ON a.id = v.aerolinea_id
         WHERE r.usuario_id = ? AND r.estado IN ('confirmada', 'cancelada')
         ORDER BY COALESCE(r.fecha_pago, r.fecha_cancelacion, r.fecha_reserva) DESC
         LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}"
    );
    $stmt->execute([$user['id']]);
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'No se pudo cargar tu historial.';
    $pagination = paginate(0, 1);
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header"><div class="container"><h1>Historial de compras</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/usuario/inicioUsuario.php') ?>">Mi cuenta</a></li><li class="breadcrumb-item active">Historial</li></ol></nav></div></section>
    <section class="section"><div class="container">
        <?php if ($errors): ?><div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <?php if (!$history): ?>
            <div class="empty-state"><div class="empty-state-icon"><i class="bi bi-clock-history"></i></div><h2 class="h4">Todavía no hay compras en tu historial</h2><p>Las reservas confirmadas o canceladas aparecerán aquí.</p><a href="<?= url('pages/usuario/mis-reservas.php') ?>" class="btn btn-volara">Ver mis reservas</a></div>
        <?php else: ?>
            <div class="reservation-list">
                <?php foreach ($history as $reservation): ?>
                    <article class="volara-card reservation-card">
                        <div class="reservation-card-top"><div><span class="eyebrow"><?= e($reservation['aerolinea_nombre']) ?> · <?= e($reservation['vuelo_codigo']) ?></span><h2 class="h4 mt-2 mb-0"><?= e($reservation['origen_codigo']) ?> <span class="text-muted">→</span> <?= e($reservation['destino_codigo']) ?></h2></div><span class="volara-badge <?= badgeClass($reservation['estado']) ?>"><?= estadoLabel($reservation['estado']) ?></span></div>
                        <div class="reservation-card-meta"><span><i class="bi bi-calendar3" aria-hidden="true"></i> <?= formatDate($reservation['fecha_salida']) ?></span><span><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i> Asiento <?= e($reservation['asiento_label'] ?: 'Sin asignar') ?></span><span><i class="bi bi-upc-scan" aria-hidden="true"></i> <?= e($reservation['codigo']) ?></span><strong><?= formatPrice((float)$reservation['precio_final']) ?></strong></div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($pagination['total_pages'] > 1): ?><nav class="volara-pagination mt-4" aria-label="Paginación del historial"><?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?><?php if ($i === $pagination['current']): ?><span class="active" aria-current="page"><?= $i ?></span><?php else: ?><a href="?p=<?= $i ?>"><?= $i ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; ?>
        <?php endif; ?>
    </div></section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
