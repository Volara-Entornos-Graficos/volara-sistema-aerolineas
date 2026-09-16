<footer class="volara-footer" role="contentinfo">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 footer-brand">
                <h5 class="footer-heading"><?= APP_NAME ?></h5>
                <p class="mb-0">Sistema de gestión de vuelos y promociones para aerolíneas.</p>
                <p>Tu plataforma de confianza para buscar, reservar y gestionar vuelos de manera simple y segura.</p>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="footer-heading">Navegación</h6>
                <ul class="footer-links">
                    <li><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li><a href="<?= url('pages/publico/buscar.php') ?>">Buscar vuelos</a></li>
                    <li><a href="<?= url('pages/publico/novedades.php') ?>">Novedades</a></li>
                    <li><a href="<?= url('pages/publico/mapa-sitio.php') ?>">Mapa del sitio</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="footer-heading">Ayuda</h6>
                <ul class="footer-links">
                    <li><a href="<?= url('pages/publico/buscar.php') ?>">Buscar vuelos</a></li>
                    <li><a href="<?= url('pages/publico/mapa-sitio.php') ?>">Mapa del sitio</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="footer-heading">Legal</h6>
                <ul class="footer-links">
                    <li><a href="<?= url('pages/publico/mapa-sitio.php') ?>">Información del sitio</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="footer-heading">Cuenta</h6>
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
                    <?php endif; ?>
                </ul>
            </div>
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