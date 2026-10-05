<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

http_response_code(404);
$pageTitle = 'Página no encontrada';
$pageDescription = 'La página solicitada no existe en VOLARA.';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main id="contenido-principal" tabindex="-1">
    <section class="page-header">
        <div class="container">
            <h1>No encontramos esa página</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Error 404</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="empty-state">
                <div class="empty-state-icon" aria-hidden="true"><i class="bi bi-signpost-split"></i></div>
                <h2 class="h4">El enlace no existe o ya no está disponible</h2>
                <p>Revisá la dirección o usá estas opciones para continuar.</p>
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                    <a href="<?= url('index.php') ?>" class="btn btn-volara">Volver al inicio</a>
                    <a href="<?= url('pages/publico/buscar.php') ?>" class="btn btn-volara-outline">Buscar vuelos</a>
                    <a href="<?= url('pages/publico/mapa-sitio.php') ?>" class="btn btn-volara-outline">Mapa del sitio</a>
                    <a href="<?= url('pages/publico/contacto.php') ?>" class="btn btn-volara-outline">Contacto</a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
