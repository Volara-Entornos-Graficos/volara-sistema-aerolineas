<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('pasajero');

$pageTitle = 'Mi cuenta';
$user = currentUser();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
	<section class="page-header">
		<div class="container">
			<h1>Hola, <?= e($user['nombre']) ?></h1>
			<p class="mb-0">Desde acá podés continuar buscando tu próximo vuelo.</p>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="row g-4">
				<div class="col-md-6 col-lg-4">
					<a class="volara-card h-100 d-block" href="<?= url('pages/publico/buscar.php') ?>">
						<i class="bi bi-search fs-3 text-danger"></i>
						<h2 class="h5 mt-3">Buscar vuelos</h2>
						<p class="text-muted mb-0">Encontrá opciones para tu próximo viaje.</p>
					</a>
				</div>
				<div class="col-md-6 col-lg-4">
					<a class="volara-card h-100 d-block" href="<?= url('pages/publico/mapa-sitio.php') ?>">
						<i class="bi bi-diagram-3 fs-3 text-danger"></i>
						<h2 class="h5 mt-3">Mapa del sitio</h2>
						<p class="text-muted mb-0">Conocé todas las secciones disponibles.</p>
					</a>
				</div>
				<div class="col-md-6 col-lg-4">
					<a class="volara-card h-100 d-block" href="<?= url('pages/usuario/mis-reservas.php') ?>">
						<i class="bi bi-ticket-perforated fs-3 text-danger"></i>
						<h2 class="h5 mt-3">Mis reservas</h2>
						<p class="text-muted mb-0">Consultá, confirmá o cancelá tus reservas.</p>
					</a>
				</div>
				<div class="col-md-6 col-lg-4">
					<a class="volara-card h-100 d-block" href="<?= url('pages/usuario/historial.php') ?>">
						<i class="bi bi-clock-history fs-3 text-danger"></i>
						<h2 class="h5 mt-3">Historial</h2>
						<p class="text-muted mb-0">Revisá tus compras y reservas anteriores.</p>
					</a>
				</div>
				<div class="col-md-6 col-lg-4">
					<a class="volara-card h-100 d-block" href="<?= url('pages/usuario/perfil.php') ?>">
						<i class="bi bi-person-gear fs-3 text-danger"></i>
						<h2 class="h5 mt-3">Mi perfil</h2>
						<p class="text-muted mb-0">Actualizá tus datos y contraseña.</p>
					</a>
				</div>
			</div>
		</div>
	</section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
