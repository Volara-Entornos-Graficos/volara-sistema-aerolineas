<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Promociones';
$promotions = [];
$error = null;

try {
    $db = getDB();
    $stmt = $db->query(
        "SELECT p.titulo, p.descripcion, p.descuento_porcentaje, p.fecha_inicio, p.fecha_fin,
                a.nombre AS aerolinea_nombre, a.codigo AS aerolinea_codigo
         FROM promociones p
         JOIN aerolineas a ON a.id = p.aerolinea_id
         WHERE p.estado = 'vigente'
           AND (p.fecha_inicio IS NULL OR p.fecha_inicio <= CURDATE())
           AND (p.fecha_fin IS NULL OR p.fecha_fin >= CURDATE())
           AND a.estado = 'activa'
         ORDER BY p.descuento_porcentaje DESC"
    );
    $promotions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'No se pudieron cargar las promociones.';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main id="contenido-principal" tabindex="-1">
    <section class="page-header">
        <div class="container">
            <h1>Promociones vigentes</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Promociones</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <?php if ($error): ?>
                <div class="volara-alert alert-danger" role="alert"><?= e($error) ?></div>
            <?php elseif (!$promotions): ?>
                <div class="empty-state">
                    <div class="empty-state-icon" aria-hidden="true"><i class="bi bi-tag"></i></div>
                    <h2 class="h4">No hay promociones vigentes</h2>
                    <p>Cuando una aerolínea publique una oferta aprobada, va a aparecer aquí.</p>
                    <a href="<?= url('pages/publico/buscar.php') ?>" class="btn btn-volara">Buscar vuelos</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($promotions as $promo): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="volara-card h-100">
                            <span class="volara-badge badge-promo"><?= number_format((float)$promo['descuento_porcentaje'], 0) ?>% OFF</span>
                            <h2 class="h4 mt-3"><?= e($promo['titulo']) ?></h2>
                            <p class="text-muted"><?= e($promo['aerolinea_nombre']) ?> · <?= e($promo['aerolinea_codigo']) ?></p>
                            <p><?= e($promo['descripcion'] ?: 'Descuento aplicable a vuelos de esta aerolínea.') ?></p>
                            <p class="small text-muted mb-0">
                                Vigencia:
                                <?= $promo['fecha_inicio'] ? formatDate($promo['fecha_inicio']) : 'desde hoy' ?>
                                —
                                <?= $promo['fecha_fin'] ? formatDate($promo['fecha_fin']) : 'sin fecha de fin' ?>
                            </p>
                            <a class="btn btn-volara btn-volara-sm mt-3" href="<?= url('pages/publico/buscar.php') ?>">Usar en una búsqueda</a>
                        </article>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
