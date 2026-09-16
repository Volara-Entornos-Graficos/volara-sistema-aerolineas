<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('pasajero');

$pageTitle = 'Confirmar reserva';
$errors = [];
$reservation = null;
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $errors[] = 'La sesión de la reserva venció. Volvé a elegir el asiento.';
}

$flightId = (int)($_POST['vuelo_id'] ?? 0);
$seatId = (int)($_POST['asiento_id'] ?? 0);
$seatLabel = trim($_POST['asiento_label'] ?? '');

if (!$errors && ($flightId < 1 || $seatId < 1 || $seatLabel === '')) {
    $errors[] = 'Seleccioná un asiento disponible para continuar.';
}

if (!$errors) {
    try {
        $db = getDB();
        $db->beginTransaction();

        $flightStmt = $db->prepare(
            "SELECT v.*, p.id AS promocion_id, p.descuento_porcentaje
             FROM vuelos v
             LEFT JOIN promociones p ON p.aerolinea_id = v.aerolinea_id
                 AND p.estado = 'vigente'
                 AND (p.fecha_inicio IS NULL OR p.fecha_inicio <= CURDATE())
                 AND (p.fecha_fin IS NULL OR p.fecha_fin >= CURDATE())
             WHERE v.id = ? AND v.estado = 'programado'
             FOR UPDATE"
        );
        $flightStmt->execute([$flightId]);
        $flight = $flightStmt->fetch();

        $seatStmt = $db->prepare('SELECT id, fila, columna, estado FROM asientos WHERE id = ? AND vuelo_id = ? FOR UPDATE');
        $seatStmt->execute([$seatId, $flightId]);
        $seat = $seatStmt->fetch();

        if (!$flight || !$seat || $seat['estado'] !== 'disponible') {
            $errors[] = 'El vuelo o el asiento ya no están disponibles.';
        } elseif ((int)$flight['asientos_disponibles'] < 1) {
            $errors[] = 'No quedan asientos disponibles para este vuelo.';
        } elseif ($seat['fila'] . $seat['columna'] !== $seatLabel) {
            $errors[] = 'El asiento seleccionado no coincide con el vuelo.';
        } else {
            $discount = (float)($flight['descuento_porcentaje'] ?? 0);
            $originalPrice = (float)$flight['precio'];
            $discountAmount = $originalPrice * ($discount / 100);
            $finalPrice = $originalPrice - $discountAmount;
            $code = generateCode('VR', 8);

            $reservationStmt = $db->prepare(
                'INSERT INTO reservas (codigo, usuario_id, vuelo_id, asiento_id, asiento_label, estado,
                 precio_original, descuento, precio_final, promocion_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $reservationStmt->execute([$code, $user['id'], $flightId, $seatId, $seatLabel, 'pendiente_pago', $originalPrice, $discountAmount, $finalPrice, $flight['promocion_id']]);

            $db->prepare("UPDATE asientos SET estado = 'ocupado' WHERE id = ?")->execute([$seatId]);
            $db->prepare('UPDATE vuelos SET asientos_disponibles = asientos_disponibles - 1 WHERE id = ? AND asientos_disponibles > 0')->execute([$flightId]);
            $db->commit();

            $reservation = [
                'codigo' => $code,
                'origen' => $flight['origen'],
                'destino' => $flight['destino'],
                'origen_codigo' => $flight['origen_codigo'],
                'destino_codigo' => $flight['destino_codigo'],
                'fecha_salida' => $flight['fecha_salida'],
                'asiento_label' => $seatLabel,
                'precio_original' => $originalPrice,
                'descuento' => $discountAmount,
                'precio_final' => $finalPrice,
            ];
        }

        if ($errors && $db->inTransaction()) $db->rollBack();
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $errors[] = 'No se pudo crear la reserva. Intentá nuevamente.';
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <div class="page-header"><div class="container"><h1>Confirmar reserva</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/usuario/inicioUsuario.php') ?>">Mi cuenta</a></li><li class="breadcrumb-item active">Confirmar reserva</li></ol></nav></div></div>
    <section class="section"><div class="container"><div class="row justify-content-center"><div class="col-lg-7">
        <?php if ($errors): ?><div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div><a href="<?= url('pages/publico/buscar.php') ?>" class="btn btn-volara">Volver a buscar</a><?php elseif ($reservation): ?>
            <div class="volara-card reservation-success"><div class="reservation-success-icon"><i class="bi bi-check2" aria-hidden="true"></i></div><span class="eyebrow">Reserva creada</span><h2 class="h3 mt-2">Tu lugar está reservado</h2><p class="text-muted">La reserva queda pendiente de pago hasta completar la compra.</p><div class="reservation-code"><span>Código de reserva</span><strong><?= e($reservation['codigo']) ?></strong></div><dl class="reservation-details"><div><dt>Ruta</dt><dd><?= e($reservation['origen_codigo']) ?> → <?= e($reservation['destino_codigo']) ?></dd></div><div><dt>Salida</dt><dd><?= formatDate($reservation['fecha_salida']) ?> · <?= formatTime($reservation['fecha_salida']) ?></dd></div><div><dt>Asiento</dt><dd><?= e($reservation['asiento_label']) ?></dd></div><div><dt>Estado</dt><dd><span class="volara-badge badge-pending">Pendiente de pago</span></dd></div></dl><div class="flight-summary-price mt-4"><span>Total</span><strong><?= formatPrice($reservation['precio_final']) ?></strong><?php if ($reservation['descuento'] > 0): ?><del><?= formatPrice($reservation['precio_original']) ?></del><?php endif; ?></div><div class="d-flex gap-2 mt-4"><a href="<?= url('pages/publico/buscar.php') ?>" class="btn btn-volara">Buscar otro vuelo</a><a href="<?= url('pages/usuario/inicioUsuario.php') ?>" class="btn btn-volara-outline">Ir a mi cuenta</a></div></div>
        <?php endif; ?>
    </div></div></div></section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
