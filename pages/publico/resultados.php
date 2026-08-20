<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Resultados de búsqueda';

$origen     = trim($_GET['origen'] ?? '');
$destino    = trim($_GET['destino'] ?? '');
$fechaIda   = $_GET['fecha_ida'] ?? '';
$fechaVuelta = $_GET['fecha_vuelta'] ?? '';
$pasajeros  = max(1, (int)($_GET['pasajeros'] ?? 1));
$clase      = $_GET['clase'] ?? '';
$page       = max(1, (int)($_GET['p'] ?? 1));

$vuelos = [];
$pagination = null;

if ($origen && $destino && $fechaIda) {
    try {
        $db = getDB();
        $where = ["v.estado = 'programado'", "v.asientos_disponibles >= ?", "DATE(v.fecha_salida) >= ?"];
        $params = [$pasajeros, $fechaIda];

        if ($origen) {
            $where[] = "(v.origen LIKE ? OR v.origen_codigo LIKE ?)";
            $params[] = "%$origen%";
            $params[] = "%$origen%";
        }
        if ($destino) {
            $where[] = "(v.destino LIKE ? OR v.destino_codigo LIKE ?)";
            $params[] = "%$destino%";
            $params[] = "%$destino%";
        }
        if ($clase) {
            $where[] = "v.clase = ?";
            $params[] = $clase;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $db->prepare("SELECT COUNT(*) FROM vuelos v WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pagination = paginate($total, $page);

        $sql = "SELECT v.*, a.nombre AS aerolinea_nombre, a.codigo AS aerolinea_codigo,
                       p.descuento_porcentaje, p.titulo AS promo_titulo
                FROM vuelos v
                JOIN aerolineas a ON a.id = v.aerolinea_id
                LEFT JOIN promociones p ON p.aerolinea_id = a.id AND p.estado = 'vigente'
                WHERE $whereSql
                ORDER BY v.fecha_salida ASC
                LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $vuelos = $stmt->fetchAll();
    } catch (PDOException $e) {
        setFlash('danger', 'Error al buscar vuelos. Verificá la conexión a la base de datos.');
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main>
    <div class="page-header">
        <div class="container">
            <h1>Resultados de búsqueda</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('pages/publico/buscar.php') ?>">Buscar</a></li>
                    <li class="breadcrumb-item active">Resultados</li>
                </ol>
            </nav>
            <?php if ($origen && $destino): ?>
            <p class="text-muted mb-0 mt-2">
                <?= e($origen) ?> → <?= e($destino) ?>
                <?php if ($fechaIda): ?> · <?= formatDate($fechaIda) ?><?php endif; ?>
                · <?= $pasajeros ?> pasajero<?= $pasajeros > 1 ? 's' : '' ?>
            </p>
            <?php endif; ?>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="volara-alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <?php if (empty($vuelos)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-airplane"></i></div>
                    <h2 class="h4">No se encontraron vuelos</h2>
                    <p>Probá con otras fechas o destinos.</p>
                    <a href="<?= url('pages/publico/buscar.php') ?>" class="btn btn-volara mt-3">
                        Nueva búsqueda
                    </a>
                </div>
            <?php else: ?>
                <p class="text-muted mb-4"><?= $pagination['total'] ?> vuelo<?= $pagination['total'] !== 1 ? 's' : '' ?> encontrado<?= $pagination['total'] !== 1 ? 's' : '' ?></p>

                <div class="row g-4">
                    <?php foreach ($vuelos as $vuelo):
                        $descuento = (float)($vuelo['descuento_porcentaje'] ?? 0);
                        $precioFinal = $descuento > 0
                            ? $vuelo['precio'] * (1 - $descuento / 100)
                            : $vuelo['precio'];
                    ?>
                    <div class="col-12">
                        <article class="flight-card">
                            <?php if ($descuento > 0): ?>
                                <span class="volara-badge badge-promo promo-badge">
                                    -<?= (int)$descuento ?>% <?= e($vuelo['promo_titulo'] ?? 'Promo') ?>
                                </span>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="text-muted small"><?= e($vuelo['aerolinea_nombre']) ?></span>
                                    <span class="volara-badge badge-neutral ms-2"><?= e($vuelo['codigo']) ?></span>
                                </div>
                                <span class="volara-badge badge-neutral"><?= ucfirst(e($vuelo['clase'])) ?></span>
                            </div>

                            <div class="flight-route">
                                <div class="flight-airport">
                                    <div class="code"><?= e($vuelo['origen_codigo']) ?></div>
                                    <div class="time"><?= formatTime($vuelo['fecha_salida']) ?></div>
                                    <div class="city"><?= e($vuelo['origen']) ?></div>
                                </div>
                                <div class="flight-line">
                                    <div class="duration"><?= flightDuration($vuelo['fecha_salida'], $vuelo['fecha_llegada']) ?></div>
                                    <div class="plane-icon"><i class="bi bi-airplane"></i></div>
                                </div>
                                <div class="flight-airport">
                                    <div class="code"><?= e($vuelo['destino_codigo']) ?></div>
                                    <div class="time"><?= formatTime($vuelo['fecha_llegada']) ?></div>
                                    <div class="city"><?= e($vuelo['destino']) ?></div>
                                </div>
                            </div>

                            <div class="flight-meta">
                                <div class="flight-meta-item">
                                    <i class="bi bi-calendar3 me-1"></i> <?= formatDate($vuelo['fecha_salida']) ?>
                                </div>
                                <div class="flight-meta-item">
                                    <i class="bi bi-person me-1"></i>
                                    <strong><?= (int)$vuelo['asientos_disponibles'] ?></strong> asientos
                                </div>
                                <span class="volara-badge <?= badgeClass($vuelo['estado']) ?>">
                                    <?= estadoLabel($vuelo['estado']) ?>
                                </span>
                                <div class="flight-price">
                                    <?php if ($descuento > 0): ?>
                                        <div class="original"><?= formatPrice((float)$vuelo['precio']) ?></div>
                                    <?php endif; ?>
                                    <div class="amount"><?= formatPrice($precioFinal) ?></div>
                                </div>
                                <a href="<?= url('pages/publico/detalle-vuelo.php?id=' . $vuelo['id']) ?>"
                                   class="btn btn-volara btn-volara-sm">
                                    Seleccionar vuelo
                                </a>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($pagination && $pagination['total_pages'] > 1): ?>
                <nav class="volara-pagination" aria-label="Paginación de resultados">
                    <?php if ($pagination['has_prev']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['p' => $page - 1])) ?>" aria-label="Anterior">&laquo;</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active" aria-current="page"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['p' => $i])) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($pagination['has_next']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['p' => $page + 1])) ?>" aria-label="Siguiente">&raquo;</a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
