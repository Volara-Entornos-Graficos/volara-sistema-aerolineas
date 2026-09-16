<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('pasajero');

$flightId = (int)($_GET['vuelo_id'] ?? $_POST['vuelo_id'] ?? 0);
$user = currentUser();
$flight = null;
$seats = [];
$error = null;

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT v.*, a.nombre AS aerolinea_nombre, a.codigo AS aerolinea_codigo FROM vuelos v JOIN aerolineas a ON a.id = v.aerolinea_id WHERE v.id = ? AND v.estado = 'programado' LIMIT 1");
    $stmt->execute([$flightId]);
    $flight = $stmt->fetch();

    if (!$flight) {
        $error = 'El vuelo no existe o ya no está disponible.';
    } else {
        $countStmt = $db->prepare('SELECT COUNT(*) FROM asientos WHERE vuelo_id = ?');
        $countStmt->execute([$flightId]);
        if ((int)$countStmt->fetchColumn() === 0) {
            $db->beginTransaction();
            $seatStmt = $db->prepare('INSERT INTO asientos (vuelo_id, fila, columna, clase, estado) VALUES (?, ?, ?, ?, ?)');
            $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
            $occupied = (int)$flight['asientos_total'] - (int)$flight['asientos_disponibles'];
            for ($seatNumber = 0; $seatNumber < (int)$flight['asientos_total']; $seatNumber++) {
                $seatStmt->execute([$flightId, intdiv($seatNumber, 6) + 1, $columns[$seatNumber % 6], $flight['clase'], $seatNumber < $occupied ? 'ocupado' : 'disponible']);
            }
            $db->commit();
        }
        $seatQuery = $db->prepare('SELECT id, fila, columna, clase, estado FROM asientos WHERE vuelo_id = ? ORDER BY fila, columna');
        $seatQuery->execute([$flightId]);
        $seats = $seatQuery->fetchAll();
    }
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    $error = 'No se pudo cargar el mapa de asientos.';
}

$occupiedLabels = array_map(static fn(array $seat): string => $seat['fila'] . $seat['columna'], array_filter($seats, static fn(array $seat): bool => $seat['estado'] !== 'disponible'));
$pageTitle = 'Elegir asiento';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header"><div class="container"><h1>Elegí tu asiento</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/publico/buscar.php') ?>">Buscar vuelos</a></li><li class="breadcrumb-item active">Asientos</li></ol></nav></div></section>
    <section class="section seat-selection-page"><div class="container">
        <?php if ($error): ?><div class="volara-alert alert-danger" role="alert"><?= e($error) ?></div><a href="<?= url('pages/publico/buscar.php') ?>" class="btn btn-volara">Volver a buscar</a><?php else: ?>
            <div class="seat-selection-layout">
                <section class="seat-cabin-panel" aria-labelledby="cabin-title">
                    <div class="seat-cabin-header"><div><span class="eyebrow"><?= e($flight['aerolinea_nombre']) ?> · <?= e($flight['codigo']) ?></span><h2 id="cabin-title">Seleccioná tu ubicación</h2></div><span class="seat-class-label"><?= e(ucfirst($flight['clase'])) ?></span></div>
                    <div class="seat-airplane" aria-label="Mapa de asientos del avión">
                        <div class="seat-cockpit"><i class="bi bi-airplane" aria-hidden="true"></i><span>Frente del avión</span></div>
                        <div id="seatMap" class="seat-map seat-map-modern" data-occupied='<?= e(json_encode($occupiedLabels)) ?>'>
                            <div class="seat-map-header"><span class="cabin-label">Cabina</span><div class="seat-column-labels"><span>A</span><span>B</span><span>C</span><span>D</span><span>E</span><span>F</span></div></div>
                            <?php foreach (array_chunk($seats, 6) as $rowIndex => $rowSeats): ?><div class="seat-row"><span class="seat-row-number"><?= $rowIndex + 1 ?></span><?php foreach ($rowSeats as $seat): ?><?php $label = $seat['fila'] . $seat['columna']; ?><button type="button" class="seat <?= $seat['estado'] !== 'disponible' ? 'occupied' : '' ?>" data-seat="<?= e($label) ?>" data-id="<?= (int)$seat['id'] ?>" aria-label="Asiento <?= e($label) ?><?= $seat['estado'] !== 'disponible' ? ', ocupado' : ', disponible' ?>" <?= $seat['estado'] !== 'disponible' ? 'disabled aria-disabled="true"' : '' ?>><?= e($label) ?></button><?php if ($seat['columna'] === 'C'): ?><span class="seat-aisle" aria-hidden="true"></span><?php endif; ?><?php endforeach; ?></div><?php endforeach; ?>
                        </div>
                    </div>
                    <div class="seat-legend" aria-label="Leyenda de asientos"><span class="seat-legend-item"><span class="seat available"></span>Disponible</span><span class="seat-legend-item"><span class="seat selected"></span>Seleccionado</span><span class="seat-legend-item"><span class="seat occupied"></span>Ocupado</span></div>
                </section>
                <aside class="seat-trip-summary"><div class="volara-card"><span class="eyebrow">Resumen del viaje</span><h2 class="h4 mt-2"><?= e($flight['origen_codigo']) ?> <span class="text-muted">→</span> <?= e($flight['destino_codigo']) ?></h2><p class="text-muted mb-4"><?= formatDate($flight['fecha_salida']) ?> · <?= formatTime($flight['fecha_salida']) ?> - <?= formatTime($flight['fecha_llegada']) ?></p><dl class="seat-summary-details"><div><dt>Pasajero</dt><dd><?= e($user['nombre'] . ' ' . $user['apellido']) ?></dd></div><div><dt>Asiento</dt><dd id="selectedSeatLabel">Elegí un asiento</dd></div><div><dt>Disponibles</dt><dd><?= (int)$flight['asientos_disponibles'] ?></dd></div></dl><form method="POST" action="<?= url('pages/usuario/checkout.php') ?>" id="seatForm"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="vuelo_id" value="<?= (int)$flight['id'] ?>"><input type="hidden" name="asiento_id" id="selectedSeatId"><input type="hidden" name="asiento_label" id="selectedSeatInput"><button type="submit" class="btn btn-volara w-100 btn-volara-lg" id="continueSeatButton" disabled>Continuar <i class="bi bi-arrow-right" aria-hidden="true"></i></button></form></div></aside>
            </div>
        <?php endif; ?>
    </div></section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
