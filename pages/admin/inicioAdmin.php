<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

$pageTitle = 'Panel de administración';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header">
        <div class="container">
            <h1>Panel de administración</h1>
            <p class="mb-0">Gestioná el contenido general de VOLARA desde un solo lugar.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/publico/buscar.php') ?>">
                        <i class="bi bi-search fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Consultar vuelos</h2>
                        <p class="text-muted mb-0">Revisá la oferta de vuelos publicada.</p>
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
