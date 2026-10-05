<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Aerolíneas';
$airlines = [];
$error = null;

try {
    $db = getDB();
    $stmt = $db->query(
        "SELECT a.id, a.codigo, a.nombre, a.descripcion, a.estado,
                COUNT(v.id) AS vuelos_activos
         FROM aerolineas a
         LEFT JOIN vuelos v ON v.aerolinea_id = a.id AND v.estado = 'programado' AND v.fecha_salida > NOW()
         WHERE a.estado = 'activa'
         GROUP BY a.id
         ORDER BY a.nombre"
    );
    $airlines = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'No se pudieron cargar las aerolíneas.';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main id="contenido-principal" tabindex="-1">
    <section class="page-header">
        <div class="container">
            <h1>Aerolíneas</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Aerolíneas</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <?php if ($error): ?>
                <div class="volara-alert alert-danger" role="alert"><?= e($error) ?></div>
            <?php elseif (!$airlines): ?>
                <div class="empty-state">
                    <div class="empty-state-icon" aria-hidden="true"><i class="bi bi-building"></i></div>
                    <h2 class="h4">No hay aerolíneas publicadas</h2>
                    <p>Volvé más tarde para consultar las compañías disponibles.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($airlines as $airline): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="volara-card h-100">
                            <p class="eyebrow mb-1"><?= e($airline['codigo']) ?></p>
                            <h2 class="h4"><?= e($airline['nombre']) ?></h2>
                            <p class="text-muted"><?= e($airline['descripcion'] ?: 'Aerolínea asociada a VOLARA.') ?></p>
                            <p class="mb-0"><strong><?= (int)$airline['vuelos_activos'] ?></strong> vuelos próximos</p>
                            <a class="btn btn-volara-outline btn-volara-sm mt-3" href="<?= url('pages/publico/buscar.php') ?>">Buscar vuelos</a>
                        </article>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
