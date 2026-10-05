<footer class="volara-footer" role="contentinfo">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 footer-brand"> <a href="<?= url('index.php') ?>" class="text-white text-decoration-none">
                <h2 class="footer-heading"> <?= APP_NAME ?> </h2>
                </a>
                <p class="mb-2">Sistema de gestión y reserva de vuelos. Buscá, compará y reservá de forma simple.</p>
                <p class="mb-1"><a href="mailto:volara.aerolinea@gmail.com">volara.aerolinea@gmail.com</a></p>
                <p class="mb-0">Rosario, Santa Fe, Argentina</p>
                <p class="mb-0">Entornos Gráficos — UTN FR Rosario</p>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="footer-heading">Navegación</h2>
                <ul class="footer-links">
                    <li><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li><a href="<?= url('pages/publico/buscar.php') ?>">Buscar vuelos</a></li>
                    <li><a href="<?= url('pages/publico/aerolineas.php') ?>">Aerolíneas</a></li>
                    <li><a href="<?= url('pages/publico/promociones.php') ?>">Promociones</a></li>
                    <li><a href="<?= url('pages/publico/novedades.php') ?>">Novedades</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="footer-heading">Ayuda</h2>
                <ul class="footer-links">
                    <li><a href="<?= url('pages/publico/ayuda.php') ?>">Preguntas frecuentes</a></li>
                    <li><a href="<?= url('pages/publico/contacto.php') ?>">Contacto</a></li>
                    <li><a href="<?= url('pages/publico/mapa-sitio.php') ?>">Mapa del sitio</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="footer-heading">Cuenta</h2>
                <ul class="footer-links">
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?= url(dashboardUrl()) ?>">Mi cuenta</a></li>
                        <li><a href="<?= url('pages/usuario/perfil.php') ?>">Mi perfil</a></li>
                        <?php if (userRole() === 'pasajero'): ?>
                            <li><a href="<?= url('pages/usuario/mis-reservas.php') ?>">Mis reservas</a></li>
                            <li><a href="<?= url('pages/usuario/historial.php') ?>">Historial</a></li>
                        <?php endif; ?>
                        <li><a href="<?= url('auth/logout.php') ?>">Cerrar sesión</a></li>
                    <?php else: ?>
                        <li><a href="<?= url('auth/login.php') ?>">Iniciar sesión</a></li>
                        <li><a href="<?= url('auth/registro.php') ?>">Registrarse</a></li>
                        <li><a href="<?= url('auth/recuperar.php') ?>">Recuperar contraseña</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <?php if (userRole() === 'admin' || userRole() === 'ceo'): ?>
                <div class="col-6 col-lg-2">
                    <h2 class="footer-heading"><?= userRole() === 'admin' ? 'Administración' : 'Gestión' ?></h2>
                    <ul class="footer-links">
                        <?php if (userRole() === 'admin'): ?>
                            <li><a href="<?= url('pages/admin/aerolineas.php') ?>">Aerolíneas</a></li>
                            <li><a href="<?= url('pages/admin/promociones.php') ?>">Aprobar promociones</a></li>
                            <li><a href="<?= url('pages/admin/novedades.php') ?>">Novedades</a></li>
                            <li><a href="<?= url('pages/admin/reportes.php') ?>">Reportes</a></li>
                        <?php else: ?>
                            <li><a href="<?= url('pages/ceo/vuelos.php') ?>">Vuelos</a></li>
                            <li><a href="<?= url('pages/ceo/promociones.php') ?>">Promociones</a></li>
                            <li><a href="<?= url('pages/ceo/reportes.php') ?>">Reportes</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos los derechos reservados.</span>
            <span>Entornos Gráficos — UTN FR Rosario</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/main.js') ?>?v=<?= filemtime(APP_ROOT . '/assets/js/main.js') ?>"></script>
<?php if (isset($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= asset($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>

</html>