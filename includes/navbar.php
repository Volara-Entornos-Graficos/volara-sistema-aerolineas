<?php
$user = currentUser();
$initials = $user ? strtoupper(substr($user['nombre'], 0, 1) . substr($user['apellido'], 0, 1)) : '';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$currentPage = basename($requestPath);
$isActive = static fn(array $pages): string => in_array($currentPage, $pages, true) ? 'active' : '';
$isPath = static fn(string $needle): bool => str_contains($requestPath, $needle);
?>
<nav class="navbar navbar-expand-xl volara-navbar" aria-label="Navegación principal">
    <div class="container">
        <a class="navbar-brand" href="<?= url('index.php') ?>" aria-label="VOLARA — Inicio">
            <img src="<?= asset('img/logo/volara-mark-256.png') ?>"
                 alt="Logo de VOLARA, sistema de reservas de vuelos"
                 width="54"
                 height="54">
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#volaraNav"
                aria-controls="volaraNav" aria-expanded="false"
                aria-label="Abrir o cerrar el menú de navegación">
            <i class="bi bi-list fs-4" aria-hidden="true"></i>
        </button>

        <div class="collapse navbar-collapse" id="volaraNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $isActive(['index.php', 'inicio.php']) ?>"
                       href="<?= url('index.php') ?>"
                       <?= $isActive(['index.php', 'inicio.php']) ? 'aria-current="page"' : '' ?>>
                        Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive(['buscar.php', 'resultados.php', 'detalle-vuelo.php']) ?>"
                       href="<?= url('pages/publico/buscar.php') ?>"
                       <?= $isActive(['buscar.php', 'resultados.php']) ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <span>Buscar vuelos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isPath('/pages/publico/aerolineas.php') ? 'active' : '' ?>"
                       href="<?= url('pages/publico/aerolineas.php') ?>">
                        Aerolíneas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isPath('/pages/publico/promociones.php') ? 'active' : '' ?>"
                       href="<?= url('pages/publico/promociones.php') ?>">
                        Promociones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isPath('/pages/publico/novedades.php') ? 'active' : '' ?>"
                       href="<?= url('pages/publico/novedades.php') ?>">
                        Novedades
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive(['ayuda.php']) ?>"
                       href="<?= url('pages/publico/ayuda.php') ?>">
                        Ayuda
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <?php if ($user): ?>
                    <?php if ($user['rol'] === 'pasajero'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive(['inicioUsuario.php']) ?>"
                               href="<?= url('pages/usuario/inicioUsuario.php') ?>">
                                <i class="bi bi-person-circle me-1" aria-hidden="true"></i> Mi cuenta
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive(['mis-reservas.php']) ?>"
                               href="<?= url('pages/usuario/mis-reservas.php') ?>">
                                <i class="bi bi-ticket-perforated me-1" aria-hidden="true"></i> Mis reservas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive(['historial.php']) ?>"
                               href="<?= url('pages/usuario/historial.php') ?>">
                                <i class="bi bi-clock-history me-1" aria-hidden="true"></i> Historial
                            </a>
                        </li>
                    <?php elseif ($user['rol'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive(['inicioAdmin.php']) ?>" href="<?= url('pages/admin/inicioAdmin.php') ?>">
                                <i class="bi bi-speedometer2 me-1" aria-hidden="true"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isPath('/pages/admin/aerolineas.php') ? 'active' : '' ?>" href="<?= url('pages/admin/aerolineas.php') ?>">Aerolíneas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isPath('/pages/admin/promociones.php') ? 'active' : '' ?>" href="<?= url('pages/admin/promociones.php') ?>">Promociones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isPath('/pages/admin/novedades.php') ? 'active' : '' ?>" href="<?= url('pages/admin/novedades.php') ?>">Novedades</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isPath('/pages/admin/reportes.php') ? 'active' : '' ?>" href="<?= url('pages/admin/reportes.php') ?>">Reportes</a>
                        </li>
                    <?php elseif ($user['rol'] === 'ceo'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive(['inicioCeo.php']) ?>" href="<?= url('pages/ceo/inicioCeo.php') ?>">
                                <i class="bi bi-speedometer2 me-1" aria-hidden="true"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isPath('/pages/ceo/vuelos.php') ? 'active' : '' ?>" href="<?= url('pages/ceo/vuelos.php') ?>">Vuelos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isPath('/pages/ceo/promociones.php') ? 'active' : '' ?>" href="<?= url('pages/ceo/promociones.php') ?>">Promociones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isPath('/pages/ceo/reportes.php') ? 'active' : '' ?>" href="<?= url('pages/ceo/reportes.php') ?>">Reportes</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown user-menu">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#" role="button" data-bs-toggle="dropdown"
                           aria-expanded="false" aria-label="Abrir menú de <?= e($user['nombre']) ?>">
                            <span class="user-avatar" aria-hidden="true"><?= e($initials) ?></span>
                            <span class="user-name"><?= e($user['nombre']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <a class="dropdown-item" href="<?= url('pages/usuario/perfil.php') ?>">
                                    <i class="bi bi-person me-2" aria-hidden="true"></i> Mi perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= url('auth/logout.php') ?>">
                                    <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('auth/login.php') ?>">
                            <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i> Iniciar sesión
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
