<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('pasajero');

$pageTitle = 'Mis reservas';
$user = currentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservationId = (int)($_POST['reserva_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {

        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif (
        $reservationId < 1 ||
        !in_array($action, ['pagar', 'cancelar'], true)
    ) {

        $errors[] = 'La acción solicitada no es válida.';
    } else {

        try {
            $db = getDB();
            $db->beginTransaction();

            $stmt = $db->prepare(
                'SELECT r.*, v.fecha_salida
                 FROM reservas r
                 JOIN vuelos v ON v.id = r.vuelo_id
                 WHERE r.id = ?
                   AND r.usuario_id = ?
                 FOR UPDATE'
            );

            $stmt->execute([
                $reservationId,
                $user['id']
            ]);

            $reservation = $stmt->fetch();


            /* ==========================================
             * VALIDAR RESERVA
             * ========================================== */

            if (!$reservation) {

                $errors[] = 'La reserva no existe.';


                /* ==========================================
             * PAGAR
             * ========================================== */
            } elseif ($action === 'pagar') {

                if ($reservation['estado'] !== 'pendiente_pago') {

                    $errors[] =
                        'Solo se pueden confirmar reservas pendientes de pago.';
                } else {

                    $update = $db->prepare(
                        "UPDATE reservas
                         SET estado = 'confirmada',
                             fecha_pago = NOW()
                         WHERE id = ?
                           AND usuario_id = ?
                           AND estado = 'pendiente_pago'"
                    );

                    $update->execute([
                        $reservationId,
                        $user['id']
                    ]);

                    setFlash(
                        'success',
                        'Reserva confirmada correctamente.'
                    );
                }


                /* ==========================================
             * CANCELAR
             * ========================================== */
            } elseif ($action === 'cancelar') {

                if (
                    !in_array(
                        $reservation['estado'],
                        ['pendiente_pago', 'confirmada'],
                        true
                    )
                ) {

                    $errors[] =
                        'La reserva ya no puede cancelarse.';
                } else {

                    /*
                     * Comprobar las 72 horas.
                     */
                    $timeUntilFlight =
                        strtotime($reservation['fecha_salida']) - time();

                    $cancellationHours =
                        defined('CANCELACION_HORAS')
                        ? (int)CANCELACION_HORAS
                        : 72;


                    if ($timeUntilFlight <= 0) {

                        $errors[] =
                            'No se pueden cancelar reservas de vuelos que ya han salido.';
                    } elseif (
                        $timeUntilFlight <
                        $cancellationHours * 3600
                    ) {

                        $errors[] =
                            'La reserva solo puede cancelarse hasta ' .
                            $cancellationHours .
                            ' horas antes de la salida.';
                    } else {

                        /*
                         * Liberar asiento.
                         */
                        $seatId =
                            (int)($reservation['asiento_id'] ?? 0);

                        if ($seatId > 0) {

                            $seatUpdate = $db->prepare(
                                "UPDATE asientos
                                 SET estado = 'disponible'
                                 WHERE id = ?
                                   AND estado = 'ocupado'"
                            );

                            $seatUpdate->execute([
                                $seatId
                            ]);


                            /*
                             * Devolver asiento al vuelo.
                             */
                            $flightUpdate = $db->prepare(
                                'UPDATE vuelos
                                 SET asientos_disponibles =
                                     asientos_disponibles + 1
                                 WHERE id = ?'
                            );

                            $flightUpdate->execute([
                                $reservation['vuelo_id']
                            ]);
                        }


                        /*
                         * Cancelar reserva.
                         */
                        $update = $db->prepare(
                            "UPDATE reservas
                             SET estado = 'cancelada',
                                 fecha_cancelacion = NOW()
                             WHERE id = ?
                               AND usuario_id = ?"
                        );

                        $update->execute([
                            $reservationId,
                            $user['id']
                        ]);

                        setFlash(
                            'success',
                            'Reserva cancelada y asiento liberado.'
                        );
                    }
                }
            }


            /* ==========================================
             * FINALIZAR TRANSACCIÓN
             * ========================================== */

            if ($db->inTransaction()) {

                if ($errors) {
                    $db->rollBack();
                } else {
                    $db->commit();
                }
            }

            if (!$errors) {
                redirect('pages/usuario/mis-reservas.php');
            }
        } catch (PDOException $e) {

            if (
                isset($db) &&
                $db->inTransaction()
            ) {
                $db->rollBack();
            }

            $errors[] =
                'No se pudo actualizar la reserva.';
        }
    }
}


/* ==========================================
 * CARGAR RESERVAS
 * ========================================== */

try {

    $db = getDB();

    $stmt = $db->prepare(
        'SELECT
            r.*,
            v.codigo AS vuelo_codigo,
            v.origen,
            v.destino,
            v.origen_codigo,
            v.destino_codigo,
            v.fecha_salida,
            v.fecha_llegada,
            a.nombre AS aerolinea_nombre
         FROM reservas r
         JOIN vuelos v
           ON v.id = r.vuelo_id
         JOIN aerolineas a
           ON a.id = v.aerolinea_id
         WHERE r.usuario_id = ?
         ORDER BY r.fecha_reserva DESC'
    );

    $stmt->execute([
        $user['id']
    ]);

    $reservations = $stmt->fetchAll();
} catch (PDOException $e) {

    $errors[] =
        'No se pudieron cargar tus reservas.';

    $reservations = [];
}


$flash = getFlash();


/* ==========================================
 * HEADER
 * ========================================== */

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main>

    <section class="page-header">

        <div class="container">

            <h1>Mis reservas</h1>

            <nav aria-label="breadcrumb">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item">

                        <a href="<?= url('pages/usuario/inicioUsuario.php') ?>">
                            Mi cuenta
                        </a>

                    </li>

                    <li class="breadcrumb-item active">
                        Mis reservas
                    </li>

                </ol>

            </nav>

        </div>

    </section>


    <section class="section">

        <div class="container">

            <?php if ($errors): ?>

                <div
                    class="volara-alert alert-danger"
                    role="alert">
                    <?= e(implode(' ', $errors)) ?>
                </div>

            <?php endif; ?>


            <?php if ($flash): ?>

                <div
                    class="volara-alert alert-<?= e($flash['type']) ?>"
                    role="status">
                    <?= e($flash['message']) ?>
                </div>

            <?php endif; ?>


            <?php if (!$reservations): ?>

                <div class="empty-state">

                    <div class="empty-state-icon">

                        <i
                            class="bi bi-ticket-perforated"
                            aria-hidden="true"></i>

                    </div>

                    <h2 class="h4">
                        Todavía no tenés reservas
                    </h2>

                    <p>
                        Encontrá un vuelo y elegí tu asiento para comenzar.
                    </p>

                    <a
                        href="<?= url('pages/publico/buscar.php') ?>"
                        class="btn btn-volara">
                        Buscar vuelos
                    </a>

                </div>

            <?php else: ?>

                <div class="reservation-list">

                    <?php foreach ($reservations as $reservation): ?>

                        <article class="volara-card reservation-card">

                            <div class="reservation-card-top">

                                <div>

                                    <span class="eyebrow">

                                        <?= e($reservation['aerolinea_nombre']) ?>

                                        ·

                                        <?= e($reservation['vuelo_codigo']) ?>

                                    </span>

                                    <h2 class="h4 mt-2 mb-0">

                                        <?= e($reservation['origen_codigo']) ?>

                                        <span class="text-muted">
                                            →
                                        </span>

                                        <?= e($reservation['destino_codigo']) ?>

                                    </h2>

                                </div>


                                <span
                                    class="volara-badge <?= badgeClass($reservation['estado']) ?>">
                                    <?= estadoLabel($reservation['estado']) ?>
                                </span>

                            </div>


                            <div class="reservation-card-meta">

                                <span>

                                    <i
                                        class="bi bi-calendar3"
                                        aria-hidden="true"></i>

                                    <?= formatDate($reservation['fecha_salida']) ?>

                                </span>


                                <span>

                                    <i
                                        class="bi bi-clock"
                                        aria-hidden="true"></i>

                                    <?= formatTime($reservation['fecha_salida']) ?>

                                </span>


                                <span>

                                    <i
                                        class="bi bi-grid-3x3-gap"
                                        aria-hidden="true"></i>

                                    Asiento

                                    <?= e(
                                        $reservation['asiento_label']
                                            ?: 'Sin asignar'
                                    ) ?>

                                </span>


                                <strong>

                                    <?= formatPrice(
                                        (float)$reservation['precio_final']
                                    ) ?>

                                </strong>

                            </div>


                            <div class="reservation-card-actions">

                                <?php if (
                                    $reservation['estado'] === 'pendiente_pago'
                                ): ?>

                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(csrfToken()) ?>">

                                        <input
                                            type="hidden"
                                            name="reserva_id"
                                            value="<?= (int)$reservation['id'] ?>">

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="pagar">

                                        <button
                                            type="submit"
                                            class="btn btn-volara btn-volara-sm">

                                            <i
                                                class="bi bi-credit-card"
                                                aria-hidden="true"></i>

                                            Confirmar pago

                                        </button>

                                    </form>

                                <?php endif; ?>


                                <?php if (
                                    in_array(
                                        $reservation['estado'],
                                        ['pendiente_pago', 'confirmada'],
                                        true
                                    )
                                ): ?>

                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(csrfToken()) ?>">

                                        <input
                                            type="hidden"
                                            name="reserva_id"
                                            value="<?= (int)$reservation['id'] ?>">

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="cancelar">

                                        <button
                                            type="submit"
                                            class="btn btn-outline-danger btn-volara-sm"
                                            data-confirm="¿Cancelar esta reserva? El asiento volverá a estar disponible.">

                                            <i
                                                class="bi bi-x-circle"
                                                aria-hidden="true"></i>

                                            Cancelar

                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>