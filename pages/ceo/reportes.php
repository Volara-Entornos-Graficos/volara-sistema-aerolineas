<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('ceo');

$pageTitle = 'Reportes de aerolínea';
$user = currentUser();
$airlineId = (int)($user['aerolinea_id'] ?? 0);
$type = $_GET['tipo'] ?? 'ventas';
if (!in_array($type, ['ventas', 'ocupacion'], true)) $type = 'ventas';
$rows = [];
$summary = ['total' => 0, 'count' => 0];

try {
    $db = getDB();
    if ($type === 'ventas') {
        $stmt = $db->prepare("SELECT DATE(r.fecha_pago) AS fecha, COUNT(r.id) AS reservas, SUM(r.precio_final) AS total
                              FROM reservas r JOIN vuelos v ON v.id = r.vuelo_id
                              WHERE v.aerolinea_id = ? AND r.estado = 'confirmada'
                              GROUP BY DATE(r.fecha_pago) ORDER BY fecha DESC LIMIT 30");
        $stmt->execute([$airlineId]); $rows = $stmt->fetchAll();
        $summary['total'] = array_sum(array_column($rows, 'total')); $summary['count'] = array_sum(array_column($rows, 'reservas'));
    } else {
        $stmt = $db->prepare("SELECT codigo, origen_codigo, destino_codigo, asientos_total, asientos_disponibles,
                                     ROUND((asientos_total - asientos_disponibles) * 100 / NULLIF(asientos_total, 0), 1) AS ocupacion
                              FROM vuelos WHERE aerolinea_id = ? ORDER BY fecha_salida DESC");
        $stmt->execute([$airlineId]); $rows = $stmt->fetchAll();
        $summary['count'] = count($rows); $summary['total'] = $rows ? array_sum(array_column($rows, 'ocupacion')) / count($rows) : 0;
    }
} catch (PDOException $e) { $rows = []; }

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main><section class="page-header"><div class="container"><h1>Reportes de aerolínea</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/ceo/inicioCeo.php') ?>">Panel CEO</a></li><li class="breadcrumb-item active">Reportes</li></ol></nav></div></section><section class="section"><div class="container"><div class="report-toolbar volara-card mb-4"><div class="d-flex flex-wrap gap-2"><a class="btn <?= $type === 'ventas' ? 'btn-volara' : 'btn-volara-outline' ?>" href="?tipo=ventas">Ventas</a><a class="btn <?= $type === 'ocupacion' ? 'btn-volara' : 'btn-volara-outline' ?>" href="?tipo=ocupacion">Ocupación</a></div></div><div class="row g-3 mb-4"><div class="col-md-6"><article class="stat-card"><div class="stat-icon stat-icon-red"><i class="bi bi-bar-chart" aria-hidden="true"></i></div><div class="stat-value"><?= $type === 'ventas' ? formatPrice((float)$summary['total']) : number_format((float)$summary['total'], 1, ',', '.') . '%' ?></div><div class="stat-label"><?= $type === 'ventas' ? 'Ventas confirmadas' : 'Ocupación promedio' ?></div></article></div><div class="col-md-6"><article class="stat-card"><div class="stat-icon stat-icon-dark"><i class="bi bi-list-check" aria-hidden="true"></i></div><div class="stat-value"><?= (int)$summary['count'] ?></div><div class="stat-label"><?= $type === 'ventas' ? 'Reservas confirmadas' : 'Vuelos analizados' ?></div></article></div></div><div class="volara-card"><div class="volara-table-responsive"><table class="volara-table"><caption class="visually-hidden">Reporte de <?= e($type) ?></caption><thead><tr><?php if ($type === 'ventas'): ?><th scope="col">Fecha</th><th scope="col">Reservas</th><th scope="col">Total</th><?php else: ?><th scope="col">Vuelo</th><th scope="col">Ruta</th><th scope="col">Asientos ocupados</th><th scope="col">Ocupación</th><?php endif; ?></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><?php if ($type === 'ventas'): ?><td><?= formatDate($row['fecha']) ?></td><td><?= (int)$row['reservas'] ?></td><td><strong><?= formatPrice((float)$row['total']) ?></strong></td><?php else: ?><td><strong><?= e($row['codigo']) ?></strong></td><td><?= e($row['origen_codigo']) ?> → <?= e($row['destino_codigo']) ?></td><td><?= (int)$row['asientos_total'] - (int)$row['asientos_disponibles'] ?> / <?= (int)$row['asientos_total'] ?></td><td><?= number_format((float)$row['ocupacion'], 1, ',', '.') ?>%</td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div></div></div></section></main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
