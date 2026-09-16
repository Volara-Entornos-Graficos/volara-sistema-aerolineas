<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('ceo');

$pageTitle = 'Gestión de promociones';
$user = currentUser();
$airlineId = (int)($user['aerolinea_id'] ?? 0);
$errors = [];
$editPromotion = null;
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $airlineId > 0) {
    $promotionId = (int)($_POST['promocion_id'] ?? 0);
    $title = trim($_POST['titulo'] ?? '');
    $description = trim($_POST['descripcion'] ?? '');
    $discount = (float)($_POST['descuento_porcentaje'] ?? 0);
    $startDate = $_POST['fecha_inicio'] ?: null;
    $endDate = $_POST['fecha_fin'] ?: null;

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif ($action === 'guardar') {
        if (mb_strlen($title) < 3 || mb_strlen($title) > 120) $errors[] = 'El título debe tener entre 3 y 120 caracteres.';
        if ($discount <= 0 || $discount > 100) $errors[] = 'El descuento debe estar entre 0,01% y 100%.';
        if ($startDate && $endDate && $endDate < $startDate) $errors[] = 'La fecha de fin debe ser posterior al inicio.';

        if (!$errors) {
            try {
                $db = getDB();
                if ($promotionId > 0) {
                    $check = $db->prepare("SELECT estado FROM promociones WHERE id = ? AND aerolinea_id = ?");
                    $check->execute([$promotionId, $airlineId]);
                    $existing = $check->fetch();
                    if (!$existing) {
                        $errors[] = 'La promoción no existe o no pertenece a tu aerolínea.';
                    } elseif ($existing['estado'] === 'vigente') {
                        $errors[] = 'Una promoción vigente no puede editarse directamente.';
                    } else {
                        $stmt = $db->prepare(
                            "UPDATE promociones SET titulo = ?, descripcion = ?, descuento_porcentaje = ?, fecha_inicio = ?, fecha_fin = ?, estado = 'pendiente'
                             WHERE id = ? AND aerolinea_id = ?"
                        );
                        $stmt->execute([$title, $description ?: null, $discount, $startDate, $endDate, $promotionId, $airlineId]);
                        setFlash('success', 'Promoción actualizada y enviada nuevamente a aprobación.');
                        redirect('pages/ceo/promociones.php');
                    }
                } else {
                    $stmt = $db->prepare(
                        "INSERT INTO promociones (aerolinea_id, titulo, descripcion, descuento_porcentaje, estado, fecha_inicio, fecha_fin)
                         VALUES (?, ?, ?, ?, 'pendiente', ?, ?)"
                    );
                    $stmt->execute([$airlineId, $title, $description ?: null, $discount, $startDate, $endDate]);
                    setFlash('success', 'Promoción creada y enviada a aprobación.');
                    redirect('pages/ceo/promociones.php');
                }
            } catch (PDOException $e) {
                $errors[] = 'No se pudo guardar la promoción.';
            }
        }
        $editPromotion = compact('promotionId', 'title', 'description', 'discount', 'startDate', 'endDate');
    } elseif ($action === 'eliminar' && $promotionId > 0) {
        try {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM promociones WHERE id = ? AND aerolinea_id = ? AND estado <> 'vigente'");
            $stmt->execute([$promotionId, $airlineId]);
            if ($stmt->rowCount() === 1) {
                setFlash('success', 'Promoción eliminada correctamente.');
                redirect('pages/ceo/promociones.php');
            }
            $errors[] = 'No se puede eliminar una promoción vigente o inexistente.';
        } catch (PDOException $e) {
            $errors[] = 'No se pudo eliminar la promoción.';
        }
    }
}

try {
    $db = getDB();
    if (isset($_GET['editar']) && $airlineId > 0) {
        $stmt = $db->prepare("SELECT * FROM promociones WHERE id = ? AND aerolinea_id = ? AND estado <> 'vigente'");
        $stmt->execute([(int)$_GET['editar'], $airlineId]);
        $editPromotion = $stmt->fetch() ?: null;
    }
    $stmt = $db->prepare('SELECT * FROM promociones WHERE aerolinea_id = ? ORDER BY created_at DESC');
    $stmt->execute([$airlineId]);
    $promotions = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'No se pudieron cargar las promociones.';
    $promotions = [];
}

$flash = getFlash();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header"><div class="container"><h1>Gestión de promociones</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/ceo/inicioCeo.php') ?>">Panel CEO</a></li><li class="breadcrumb-item active">Promociones</li></ol></nav></div></section>
    <section class="section"><div class="container">
        <?php if ($errors): ?><div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <?php if ($flash): ?><div class="volara-alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
        <div class="row g-4 align-items-start">
            <div class="col-lg-4"><div class="volara-card"><h2 class="h4 mb-3"><?= $editPromotion ? 'Editar promoción' : 'Nueva promoción' ?></h2><form method="POST" data-validate novalidate><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="guardar"><input type="hidden" name="promocion_id" value="<?= (int)($editPromotion['id'] ?? $editPromotion['promotionId'] ?? 0) ?>"><div class="form-group"><label class="volara-label" for="titulo">Título *</label><input class="volara-input" id="titulo" name="titulo" maxlength="120" required value="<?= e($editPromotion['titulo'] ?? $editPromotion['title'] ?? '') ?>"></div><div class="form-group"><label class="volara-label" for="descripcion">Descripción</label><textarea class="volara-input" id="descripcion" name="descripcion" rows="4"><?= e($editPromotion['descripcion'] ?? $editPromotion['description'] ?? '') ?></textarea></div><div class="form-group"><label class="volara-label" for="descuento_porcentaje">Descuento (%) *</label><input class="volara-input" type="number" id="descuento_porcentaje" name="descuento_porcentaje" min="0.01" max="100" step="0.01" required value="<?= e((string)($editPromotion['descuento_porcentaje'] ?? $editPromotion['discount'] ?? '')) ?>"></div><div class="row g-3"><div class="col-md-6"><label class="volara-label" for="fecha_inicio">Desde</label><input class="volara-input" type="date" id="fecha_inicio" name="fecha_inicio" value="<?= e($editPromotion['fecha_inicio'] ?? $editPromotion['startDate'] ?? '') ?>"></div><div class="col-md-6"><label class="volara-label" for="fecha_fin">Hasta</label><input class="volara-input" type="date" id="fecha_fin" name="fecha_fin" value="<?= e($editPromotion['fecha_fin'] ?? $editPromotion['endDate'] ?? '') ?>"></div></div><div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-volara"><i class="bi bi-send" aria-hidden="true"></i> Enviar a aprobación</button><?php if ($editPromotion): ?><a href="<?= url('pages/ceo/promociones.php') ?>" class="btn btn-volara-outline">Cancelar</a><?php endif; ?></div></form></div></div>
            <div class="col-lg-8"><div class="volara-card"><h2 class="h4 mb-1">Promociones de mi aerolínea</h2><p class="text-muted mb-3">Las nuevas promociones requieren aprobación administrativa.</p><div class="volara-table-responsive"><table class="volara-table"><caption class="visually-hidden">Promociones de mi aerolínea</caption><thead><tr><th scope="col">Promoción</th><th scope="col">Descuento</th><th scope="col">Vigencia</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead><tbody><?php foreach ($promotions as $promotion): ?><tr><td><strong><?= e($promotion['titulo']) ?></strong><small class="d-block text-muted"><?= e($promotion['descripcion'] ?? '') ?></small></td><td><?= number_format((float)$promotion['descuento_porcentaje'], 2, ',', '.') ?>%</td><td><?= $promotion['fecha_inicio'] ? formatDate($promotion['fecha_inicio']) : 'Sin inicio' ?><?php if ($promotion['fecha_fin']): ?><small class="d-block text-muted">hasta <?= formatDate($promotion['fecha_fin']) ?></small><?php endif; ?></td><td><span class="volara-badge <?= badgeClass($promotion['estado']) ?>"><?= estadoLabel($promotion['estado']) ?></span></td><td><div class="table-actions"><?php if ($promotion['estado'] !== 'vigente'): ?><a class="table-action-btn" href="?editar=<?= (int)$promotion['id'] ?>" aria-label="Editar <?= e($promotion['titulo']) ?>" title="Editar"><i class="bi bi-pencil" aria-hidden="true"></i></a><form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="promocion_id" value="<?= (int)$promotion['id'] ?>"><button type="submit" class="table-action-btn danger" data-confirm="¿Eliminar esta promoción?" aria-label="Eliminar <?= e($promotion['titulo']) ?>" title="Eliminar"><i class="bi bi-trash" aria-hidden="true"></i></button></form><?php endif; ?></div></td></tr><?php endforeach; ?></tbody></table></div></div></div>
        </div>
    </div></section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
