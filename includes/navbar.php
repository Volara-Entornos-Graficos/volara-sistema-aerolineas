<?php
$user = currentUser();
$initials = $user ? strtoupper(substr($user['nombre'], 0, 1) . substr($user['apellido'], 0, 1)) : '';
?>
<nav class="navbar navbar-expand-lg volara-navbar" aria-label="Navegación principal">
    <div class="container">
        <a class="navbar-brand" href="<?= url('index.php') ?>" aria-label="VOLARA — Inicio">
            <img src="<?= asset('img/logo/Volara-Sistema de aerolineas.png') ?>"
                 alt="Logo VOLARA — Sistema de aerolíneas"
                 height="40">
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#volaraNav"
                aria-controls="volaraNav" aria-expanded="false"
                aria-label="Abrir menú de navegación">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="volaraNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= isActivePage('inicio') ?>"
                       href="<?= url('index.php') ?>">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                       href="<?= url('pages/publico/buscar.php') ?>">Buscar vuelos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                       href="<?= url('pages/publico/aerolineas.php') ?>">Aerolíneas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                       href="<?= url('pages/publico/novedades.php') ?>">Novedades</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <?php if ($user): ?>
                    <?php if ($user['rol'] === 'pasajero'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('pages/usuario/mis-reservas.php') ?>">
                                <i class="bi bi-ticket-perforated me-1"></i> Mis reservas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('pages/usuario/historial.php') ?>">
                                <i class="bi bi-clock-history me-1"></i> Historial
                            </a>
                        </li>
                    <?php elseif ($user['rol'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('pages/admin/dashboard.php') ?>">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                    <?php elseif ($user['rol'] === 'ceo'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('pages/ceo/dashboard.php') ?>">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown user-menu">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#" role="button" data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <span class="user-avatar" aria-hidden="true"><?= e($initials) ?></span>
                            <span><?= e($user['nombre']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= url('pages/usuario/perfil.php') ?>">
                                    <i class="bi bi-person me-2"></i> Mi perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= url('auth/logout.php') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('auth/login.php') ?>">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-volara btn-volara-sm" href="<?= url('auth/registro.php') ?>">
                            Registrarse
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
