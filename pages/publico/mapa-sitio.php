<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Mapa del sitio';
$role = userRole();

$mapa = [
    'Público' => [
        'Inicio'         => 'index.php',
        'Buscar vuelos'  => 'pages/publico/buscar.php',
        'Aerolíneas'     => 'pages/publico/aerolineas.php',
        'Promociones'    => 'pages/publico/promociones.php',
        'Novedades'      => 'pages/publico/novedades.php',
        'Ayuda'          => 'pages/publico/ayuda.php',
        'Contacto'       => 'pages/publico/contacto.php',
        'Mapa del sitio' => 'pages/publico/mapa-sitio.php',
    ],
];

if (!isLoggedIn()) {
    $mapa['Autenticación'] = [
        'Iniciar sesión'        => 'auth/login.php',
        'Registrarse'           => 'auth/registro.php',
        'Recuperar contraseña'  => 'auth/recuperar.php',
    ];
}

if ($role === 'pasajero') {
    $mapa['Pasajero'] = [
        'Mi cuenta'            => 'pages/usuario/inicioUsuario.php',
        'Buscar vuelos'        => 'pages/publico/buscar.php',
        'Mis reservas'         => 'pages/usuario/mis-reservas.php',
        'Historial'            => 'pages/usuario/historial.php',
        'Mi perfil'            => 'pages/usuario/perfil.php',
    ];
}

if ($role === 'ceo') {
    $mapa['CEO'] = [
        'Dashboard'   => 'pages/ceo/inicioCeo.php',
        'Vuelos'      => 'pages/ceo/vuelos.php',
        'Promociones' => 'pages/ceo/promociones.php',
        'Reportes'    => 'pages/ceo/reportes.php',
        'Mi perfil'   => 'pages/usuario/perfil.php',
    ];
}

if ($role === 'admin') {
    $mapa['Administrador'] = [
        'Dashboard'             => 'pages/admin/inicioAdmin.php',
        'Aerolíneas'            => 'pages/admin/aerolineas.php',
        'Promociones'           => 'pages/admin/promociones.php',
        'Novedades'             => 'pages/admin/novedades.php',
        'Reportes'              => 'pages/admin/reportes.php',
        'Mi perfil'             => 'pages/usuario/perfil.php',
    ];
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main id="contenido-principal" tabindex="-1">
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
            <p class="text-muted mb-4">Solo se muestran las secciones que podés usar con tu sesión actual.</p>
            <div class="row g-4">
                <?php foreach ($mapa as $seccion => $paginas): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="volara-card h-100">
                        <h2 class="h5 mb-3"><?= e($seccion) ?></h2>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($paginas as $nombre => $ruta): ?>
                            <li class="mb-2">
                                <a href="<?= url($ruta) ?>">
                                    <i class="bi bi-chevron-right me-1 text-muted" aria-hidden="true"></i>
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
