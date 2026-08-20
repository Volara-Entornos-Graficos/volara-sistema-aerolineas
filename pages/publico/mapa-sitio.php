<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Mapa del sitio';

$mapa = [
    'Público' => [
        'Inicio'                    => 'index.php',
        'Buscar vuelos'             => 'pages/publico/buscar.php',
        'Resultados de búsqueda'    => 'pages/publico/resultados.php',
        'Mapa del sitio'            => 'pages/publico/mapa-sitio.php',
    ],
    'Autenticación' => [
        'Iniciar sesión'            => 'auth/login.php',
        'Registrarse'               => 'auth/registro.php',
    ],
    'Pasajero' => [
        'Mi cuenta'                 => 'pages/usuario/inicioUsuario.php',
    ],
    'Administrador' => [
        'Dashboard'                 => 'pages/admin/inicioAdmin.php',
    ],
    'CEO de Aerolínea' => [
        'Dashboard'                 => 'pages/ceo/inicioCeo.php',
    ],
];

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main>
    <div class="page-header">
        <div class="container">
            <h1>Mapa del sitio</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Mapa del sitio</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="row g-4">
                <?php foreach ($mapa as $seccion => $paginas): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="volara-card h-100">
                        <h2 class="h5 mb-3"><?= e($seccion) ?></h2>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($paginas as $nombre => $ruta): ?>
                            <li class="mb-2">
                                <a href="<?= url($ruta) ?>">
                                    <i class="bi bi-chevron-right me-1 text-muted"></i>
                                    <?= e($nombre) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
