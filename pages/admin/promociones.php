<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

$pageTitle = 'Aprobación de promociones';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $promotionId = (int)($_POST['promocion_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif ($promotionId < 1 || !in_array($action, ['aprobar', 'rechazar'], true)) {
        $errors[] = 'La acción solicitada no es válida.';
    } else {
        try {
            $db = getDB();
            $db->beginTransaction();
            $promotionStmt = $db->prepare("SELECT * FROM promociones WHERE id = ? AND estado = 'pendiente' FOR UPDATE");
            $promotionStmt->execute([$promotionId]);
            $promotion = $promotionStmt->fetch();

            if (!$promotion) {
                $errors[] = 'La promoción ya fue procesada o no existe.';
            } elseif ($action === 'aprobar') {
                $activeStmt = $db->prepare("SELECT COUNT(*) FROM promociones WHERE aerolinea_id = ? AND estado = 'vigente' FOR UPDATE");
                $activeStmt->execute([$promotion['aerolinea_id']]);
                if ((int)$activeStmt->fetchColumn() > 0) {
                    $errors[] = 'La aerolínea ya tiene una promoción vigente. Primero debe finalizarla.';
                } elseif ($promotion['fecha_inicio'] && $promotion['fecha_fin'] && $promotion['fecha_fin'] < $promotion['fecha_inicio']) {
                    $errors[] = 'La vigencia de la promoción no es válida.';
                } else {
                    $stmt = $db->prepare("UPDATE promociones SET estado = 'vigente' WHERE id = ?");
                    $stmt->execute([$promotionId]);
                    setFlash('success', 'Promoción aprobada y publicada.');
                }
            } else {
                $stmt = $db->prepare("UPDATE promociones SET estado = 'denegada' WHERE id = ?");
                $stmt->execute([$promotionId]);
                setFlash('warning', 'Promoción rechazada.');
            }

            if ($db->inTransaction()) {
                if ($errors) $db->rollBack();
                else $db->commit();
            }
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $errors[] = 'No se pudo procesar la promoción.';
        }
    }
}

try {
    $db = getDB();
    $promotions = $db->query(
        "SELECT p.*, a.nombre AS aerolinea_nombre, a.codigo AS aerolinea_codigo
         FROM promociones p
         JOIN aerolineas a ON a.id = p.aerolinea_id
         WHERE p.estado = 'pendiente'
         ORDER BY p.created_at ASC"
    )->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'No se pudieron cargar las promociones pendientes.';
    $promotions = [];
}

$flash = getFlash();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header"><div class="container"><h1>Aprobación de promociones</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/admin/inicioAdmin.php') ?>">Panel admin</a></li><li class="breadcrumb-item active">Promociones</li></ol></nav></div></section>
    <section class="section"><div class="container">
        <?php if ($errors): ?><div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <?php if ($flash): ?><div class="volara-alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
        <div class="volara-card"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="h4 mb-1">Promociones pendientes</h2><p class="text-muted mb-0">Revisá las propuestas antes de publicarlas.</p></div><span class="volara-badge badge-neutral"><?= count($promotions) ?> pendientes</span></div>
            <?php if (!$promotions): ?><div class="empty-state py-4"><div class="empty-state-icon"><i class="bi bi-megaphone"></i></div><p class="mb-0">No hay promociones pendientes.</p></div><?php else: ?><div class="volara-table-responsive"><table class="volara-table"><caption class="visually-hidden">Promociones pendientes de aprobación</caption><thead><tr><th scope="col">Aerolínea</th><th scope="col">Promoción</th><th scope="col">Descuento</th><th scope="col">Vigencia</th><th scope="col">Acciones</th></tr></thead><tbody><?php foreach ($promotions as $promotion): ?><tr><td><strong><?= e($promotion['aerolinea_codigo']) ?></strong><small class="d-block text-muted"><?= e($promotion['aerolinea_nombre']) ?></small></td><td><strong><?= e($promotion['titulo']) ?></strong><small class="d-block text-muted"><?= e($promotion['descripcion'] ?? '') ?></small></td><td><?= number_format((float)$promotion['descuento_porcentaje'], 2, ',', '.') ?>%</td><td><?= $promotion['fecha_inicio'] ? formatDate($promotion['fecha_inicio']) : 'Sin inicio' ?><?php if ($promotion['fecha_fin']): ?><small class="d-block text-muted">hasta <?= formatDate($promotion['fecha_fin']) ?></small><?php endif; ?></td><td><div class="table-actions"><form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="promocion_id" value="<?= (int)$promotion['id'] ?>"><input type="hidden" name="action" value="aprobar"><button type="submit" class="btn btn-volara btn-volara-sm"><i class="bi bi-check-lg" aria-hidden="true"></i> Aprobar</button></form><form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="promocion_id" value="<?= (int)$promotion['id'] ?>"><input type="hidden" name="action" value="rechazar"><button type="submit" class="btn btn-outline-danger btn-volara-sm" data-confirm="¿Rechazar esta promoción?"><i class="bi bi-x-lg" aria-hidden="true"></i> Rechazar</button></form></div></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </div>
    </div></section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
