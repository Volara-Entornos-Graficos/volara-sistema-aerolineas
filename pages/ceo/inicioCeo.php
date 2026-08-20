<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('ceo');

$pageTitle = 'Panel de aerolínea';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header">
        <div class="container">
            <h1>Panel de aerolínea</h1>
            <p class="mb-0">Consultá la información disponible para tu aerolínea.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/publico/buscar.php') ?>">
                        <i class="bi bi-airplane fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Consultar vuelos</h2>
                        <p class="text-muted mb-0">Revisá los vuelos programados disponibles.</p>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/publico/mapa-sitio.php') ?>">
                        <i class="bi bi-diagram-3 fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Mapa del sitio</h2>
                        <p class="text-muted mb-0">Accedé a las secciones disponibles del sistema.</p>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
