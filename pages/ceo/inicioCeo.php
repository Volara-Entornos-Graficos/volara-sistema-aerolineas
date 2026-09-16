<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('ceo');

$pageTitle = 'Panel de aerolínea';
$user = currentUser();
$airlineId = (int)($user['aerolinea_id'] ?? 0);
$stats = ['flights' => 0, 'confirmed' => 0, 'sales' => 0, 'occupancy' => 0, 'promotions' => 0];

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM vuelos WHERE aerolinea_id = ? AND estado = 'programado'");
    $stmt->execute([$airlineId]);
    $stats['flights'] = (int)$stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COUNT(*) FROM reservas r JOIN vuelos v ON v.id = r.vuelo_id WHERE v.aerolinea_id = ? AND r.estado = 'confirmada'");
    $stmt->execute([$airlineId]);
    $stats['confirmed'] = (int)$stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COALESCE(SUM(r.precio_final), 0) FROM reservas r JOIN vuelos v ON v.id = r.vuelo_id WHERE v.aerolinea_id = ? AND r.estado = 'confirmada'");
    $stmt->execute([$airlineId]);
    $stats['sales'] = (float)$stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COALESCE(ROUND(AVG((v.asientos_total - v.asientos_disponibles) * 100 / NULLIF(v.asientos_total, 0)), 1), 0) FROM vuelos v WHERE v.aerolinea_id = ?");
    $stmt->execute([$airlineId]);
    $stats['occupancy'] = (float)$stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COUNT(*) FROM promociones WHERE aerolinea_id = ? AND estado IN ('pendiente', 'vigente')");
    $stmt->execute([$airlineId]);
    $stats['promotions'] = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    $stats = ['flights' => 0, 'confirmed' => 0, 'sales' => 0, 'occupancy' => 0, 'promotions' => 0];
}
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
            <div class="row g-3 mb-5">
                <?php foreach ([
                    ['Vuelos activos', 'bi-airplane', $stats['flights']],
                    ['Reservas confirmadas', 'bi-ticket-perforated', $stats['confirmed']],
                    ['Ventas', 'bi-cash-stack', formatPrice($stats['sales'])],
                    ['Ocupación promedio', 'bi-bar-chart', number_format($stats['occupancy'], 1, ',', '.') . '%'],
                    ['Promociones', 'bi-megaphone', $stats['promotions']],
                ] as $stat): ?>
                    <div class="col-6 col-xl">
                        <article class="stat-card h-100">
                            <div class="stat-icon stat-icon-red"><i class="bi <?= e($stat[1]) ?>" aria-hidden="true"></i></div>
                            <div class="stat-value"><?= e((string)$stat[2]) ?></div>
                            <div class="stat-label"><?= e($stat[0]) ?></div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
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
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/ceo/vuelos.php') ?>">
                        <i class="bi bi-calendar2-check fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Gestionar vuelos</h2>
                        <p class="text-muted mb-0">Creá, editá y administrá los vuelos de tu aerolínea.</p>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/ceo/promociones.php') ?>">
                        <i class="bi bi-megaphone fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Gestionar promociones</h2>
                        <p class="text-muted mb-0">Creá promociones y envialas a aprobación.</p>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/ceo/reportes.php') ?>">
                        <i class="bi bi-bar-chart-line fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Reportes</h2>
                        <p class="text-muted mb-0">Analizá ventas y ocupación de tu aerolínea.</p>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
