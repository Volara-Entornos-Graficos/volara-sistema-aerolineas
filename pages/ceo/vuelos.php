<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('ceo');

$pageTitle = 'Gestión de vuelos';
$user = currentUser();
$airlineId = (int)($user['aerolinea_id'] ?? 0);
$errors = [];
$editFlight = null;
$action = $_POST['action'] ?? '';

if ($airlineId < 1) {
    $errors[] = 'Tu cuenta CEO no tiene una aerolínea asociada.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $airlineId > 0) {
    $flightId = (int)($_POST['vuelo_id'] ?? 0);
    $flightUpdatedAt = $_POST['vuelo_updated_at'] ?? null; // Para detectar race conditions
    $code = strtoupper(trim($_POST['codigo'] ?? ''));
    $origin = trim($_POST['origen'] ?? '');
    $originCode = strtoupper(trim($_POST['origen_codigo'] ?? ''));
    $destination = trim($_POST['destino'] ?? '');
    $destinationCode = strtoupper(trim($_POST['destino_codigo'] ?? ''));
    $departure = trim($_POST['fecha_salida'] ?? '');
    $arrival = trim($_POST['fecha_llegada'] ?? '');
    $price = (float)($_POST['precio'] ?? 0);
    $totalSeats = (int)($_POST['asientos_total'] ?? 0);
    $availableSeats = (int)($_POST['asientos_disponibles'] ?? 0);
    $class = $_POST['clase'] ?? 'economica';
    $model = trim($_POST['avion_modelo'] ?? '');
    $distance = trim($_POST['avion_distancia'] ?? '');
    $speed = trim($_POST['avion_velocidad'] ?? '');
    $status = $_POST['estado'] ?? 'programado';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif ($action === 'guardar') {
        if (!preg_match('/^[A-Z0-9-]{3,15}$/', $code)) $errors[] = 'El código debe tener entre 3 y 15 caracteres válidos.';
        if ($origin === '' || $destination === '') $errors[] = 'Completá origen y destino.';
        if ($originCode === '' || $destinationCode === '') $errors[] = 'Completá los códigos de origen y destino.';
        if ($originCode === $destinationCode) $errors[] = 'El origen y el destino deben ser diferentes.';
        if (!$departure || !$arrival || strtotime($departure) === false || strtotime($arrival) === false) $errors[] = 'Ingresá fechas de salida y llegada válidas.';
        if ($departure && $arrival && strtotime($arrival) <= strtotime($departure)) $errors[] = 'La llegada debe ser posterior a la salida.';
        if ($price <= 0) $errors[] = 'El precio debe ser mayor a cero.';
        if ($totalSeats < 1 || $totalSeats > 500) $errors[] = 'La cantidad de asientos debe estar entre 1 y 500.';
        if ($availableSeats < 0 || $availableSeats > $totalSeats) $errors[] = 'Los asientos disponibles no pueden superar el total.';
        if (!in_array($class, ['economica', 'premium', 'business'], true)) $errors[] = 'La clase seleccionada no es válida.';
        if (!in_array($status, ['programado', 'cancelado', 'completado'], true)) $errors[] = 'El estado seleccionado no es válido.';

        if (!$errors) {
            try {
                $db = getDB();
                $check = $db->prepare('SELECT id FROM vuelos WHERE codigo = ? AND id <> ?');
                $check->execute([$code, $flightId]);
                if ($check->fetch()) {
                    $errors[] = 'Ya existe un vuelo con ese código.';
                } elseif ($flightId > 0) {
                    $existingStmt = $db->prepare('SELECT * FROM vuelos WHERE id = ? AND aerolinea_id = ?');
                    $existingStmt->execute([$flightId, $airlineId]);
                    $existing = $existingStmt->fetch();
                    if (!$existing) {
                        $errors[] = 'El vuelo no existe o no pertenece a tu aerolínea.';
                    } else {
                        $occupiedSeats = (int)$existing['asientos_total'] - (int)$existing['asientos_disponibles'];
                        if ($totalSeats < $occupiedSeats) {
                            $errors[] = 'El total no puede ser menor que los asientos ya ocupados.';
                        } elseif ($totalSeats !== (int)$existing['asientos_total']) {
                            $errors[] = 'No se puede cambiar la capacidad total de un vuelo existente.';
                        } elseif ($availableSeats < $occupiedSeats) {
                            $errors[] = 'Los asientos disponibles no pueden ser menores a los ya ocupados.';
                        } else {
                            $stmt = $db->prepare(
                                'UPDATE vuelos SET codigo = ?, origen = ?, origen_codigo = ?, destino = ?, destino_codigo = ?,
                                 fecha_salida = ?, fecha_llegada = ?, precio = ?, asientos_disponibles = ?, clase = ?,
                                 avion_modelo = ?, avion_distancia = ?, avion_velocidad = ?, estado = ?
                                 WHERE id = ? AND aerolinea_id = ? AND updated_at = ?'
                            );
                            $affected = $stmt->execute([$code, $origin, $originCode, $destination, $destinationCode, date('Y-m-d H:i:s', strtotime($departure)), date('Y-m-d H:i:s', strtotime($arrival)), $price, $availableSeats, $class, $model ?: null, $distance ?: null, $speed ?: null, $status, $flightId, $airlineId, $flightUpdatedAt]);
                            
                            if ($stmt->rowCount() === 0) {
                                // Verificar si el vuelo existe (pero fue modificado por otro usuario)
                                $checkStmt = $db->prepare('SELECT updated_at FROM vuelos WHERE id = ? AND aerolinea_id = ?');
                                $checkStmt->execute([$flightId, $airlineId]);
                                if ($checkStmt->rowCount() > 0) {
                                    $errors[] = 'Este vuelo fue modificado recientemente por otro usuario. Recargá la página e intentá nuevamente.';
                                } else {
                                    $errors[] = 'El vuelo no existe.';
                                }
                            } else {
                                setFlash('success', 'Vuelo actualizado correctamente.');
                                redirect('pages/ceo/vuelos.php');
                            }
                        }
                    }
                } else {
                    $db->beginTransaction();
                    $stmt = $db->prepare(
                        'INSERT INTO vuelos (codigo, aerolinea_id, origen, origen_codigo, destino, destino_codigo,
                         fecha_salida, fecha_llegada, precio, asientos_total, asientos_disponibles, clase,
                         avion_modelo, avion_distancia, avion_velocidad, estado)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([$code, $airlineId, $origin, $originCode, $destination, $destinationCode, date('Y-m-d H:i:s', strtotime($departure)), date('Y-m-d H:i:s', strtotime($arrival)), $price, $totalSeats, $availableSeats, $class, $model ?: null, $distance ?: null, $speed ?: null, $status]);
                    $newFlightId = (int)$db->lastInsertId();
                    $seatStmt = $db->prepare('INSERT INTO asientos (vuelo_id, fila, columna, clase) VALUES (?, ?, ?, ?)');
                    $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
                    for ($seatNumber = 0; $seatNumber < $totalSeats; $seatNumber++) {
                        $seatStmt->execute([$newFlightId, intdiv($seatNumber, 6) + 1, $columns[$seatNumber % 6], $class]);
                    }
                    $db->commit();
                    setFlash('success', 'Vuelo creado correctamente.');
                    redirect('pages/ceo/vuelos.php');
                }
            } catch (PDOException $e) {
                if (isset($db) && $db->inTransaction()) $db->rollBack();
                $errors[] = 'No se pudo guardar el vuelo.';
            }
        }

        $editFlight = compact('flightId', 'code', 'origin', 'originCode', 'destination', 'destinationCode', 'departure', 'arrival', 'price', 'totalSeats', 'availableSeats', 'class', 'model', 'distance', 'speed', 'status');
    } elseif ($action === 'eliminar' && $flightId > 0) {
        try {
            $db = getDB();
            $reservationCheck = $db->prepare('SELECT COUNT(*) FROM reservas WHERE vuelo_id = ?');
            $reservationCheck->execute([$flightId]);
            if ((int)$reservationCheck->fetchColumn() > 0) {
                $errors[] = 'No se puede eliminar un vuelo que tiene reservas asociadas. Podés cancelarlo.';
            } else {
                $stmt = $db->prepare('DELETE FROM vuelos WHERE id = ? AND aerolinea_id = ?');
                $stmt->execute([$flightId, $airlineId]);
                setFlash('success', 'Vuelo eliminado correctamente.');
                redirect('pages/ceo/vuelos.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'No se pudo eliminar el vuelo.';
        }
    }
}

try {
    $db = getDB();
    if (isset($_GET['editar']) && $airlineId > 0) {
        $stmt = $db->prepare('SELECT * FROM vuelos WHERE id = ? AND aerolinea_id = ?');
        $stmt->execute([(int)$_GET['editar'], $airlineId]);
        $editFlight = $stmt->fetch() ?: null;
    }

    $countStmt = $db->prepare('SELECT COUNT(*) FROM vuelos WHERE aerolinea_id = ?');
    $countStmt->execute([$airlineId]);
    $pagination = paginate((int)$countStmt->fetchColumn(), max(1, (int)($_GET['p'] ?? 1)));
    $stmt = $db->prepare(
        "SELECT * FROM vuelos WHERE aerolinea_id = ? ORDER BY fecha_salida DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}"
    );
    $stmt->execute([$airlineId]);
    $flights = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'No se pudieron cargar los vuelos.';
    $flights = [];
    $pagination = paginate(0, 1);
}

$flash = getFlash();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
function flightInputDate($value): string { return $value ? date('Y-m-d\TH:i', strtotime($value)) : ''; }
?>
<main>
    <section class="page-header">
        <div class="container">
            <h1>Gestión de vuelos</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/ceo/inicioCeo.php') ?>">Panel CEO</a></li><li class="breadcrumb-item active">Vuelos</li></ol></nav>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <?php if ($errors): ?><div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
            <?php if ($flash): ?><div class="volara-alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
            <div class="row g-4 align-items-start">
                <div class="col-lg-5">
                    <div class="volara-card">
                        <h2 class="h4 mb-3"><?= $editFlight ? 'Editar vuelo' : 'Nuevo vuelo' ?></h2>
                        <form method="POST" data-validate novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="guardar"><input type="hidden" name="vuelo_id" value="<?= (int)($editFlight['id'] ?? 0) ?>"><input type="hidden" name="vuelo_updated_at" value="<?= e($editFlight['updated_at'] ?? '') ?>">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="volara-label" for="codigo">Código *</label><input class="volara-input" id="codigo" name="codigo" maxlength="15" required value="<?= e($editFlight['codigo'] ?? $editFlight['code'] ?? '') ?>"></div>
                                <div class="col-md-6"><label class="volara-label" for="clase">Clase *</label><select class="volara-select" id="clase" name="clase"><option value="economica" <?= ($editFlight['clase'] ?? $editFlight['class'] ?? 'economica') === 'economica' ? 'selected' : '' ?>>Económica</option><option value="premium" <?= ($editFlight['clase'] ?? $editFlight['class'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium</option><option value="business" <?= ($editFlight['clase'] ?? $editFlight['class'] ?? '') === 'business' ? 'selected' : '' ?>>Business</option></select></div>
                                <div class="col-md-8"><label class="volara-label" for="origen">Origen *</label><input class="volara-input" id="origen" name="origen" required value="<?= e($editFlight['origen'] ?? $editFlight['origin'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="volara-label" for="origen_codigo">Código *</label><input class="volara-input" id="origen_codigo" name="origen_codigo" maxlength="5" required value="<?= e($editFlight['origen_codigo'] ?? $editFlight['originCode'] ?? '') ?>"></div>
                                <div class="col-md-8"><label class="volara-label" for="destino">Destino *</label><input class="volara-input" id="destino" name="destino" required value="<?= e($editFlight['destino'] ?? $editFlight['destination'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="volara-label" for="destino_codigo">Código *</label><input class="volara-input" id="destino_codigo" name="destino_codigo" maxlength="5" required value="<?= e($editFlight['destino_codigo'] ?? $editFlight['destinationCode'] ?? '') ?>"></div>
                                <div class="col-md-6"><label class="volara-label" for="fecha_salida">Salida *</label><input class="volara-input" type="datetime-local" id="fecha_salida" name="fecha_salida" required value="<?= e(flightInputDate($editFlight['fecha_salida'] ?? $editFlight['departure'] ?? '')) ?>"></div>
                                <div class="col-md-6"><label class="volara-label" for="fecha_llegada">Llegada *</label><input class="volara-input" type="datetime-local" id="fecha_llegada" name="fecha_llegada" required value="<?= e(flightInputDate($editFlight['fecha_llegada'] ?? $editFlight['arrival'] ?? '')) ?>"></div>
                                <div class="col-md-4"><label class="volara-label" for="precio">Precio *</label><input class="volara-input" type="number" id="precio" name="precio" min="0.01" step="0.01" required value="<?= e((string)($editFlight['precio'] ?? $editFlight['price'] ?? '')) ?>"></div>
                                <div class="col-md-4"><label class="volara-label" for="asientos_total">Asientos *</label><input class="volara-input" type="number" id="asientos_total" name="asientos_total" min="1" max="500" required value="<?= e((string)($editFlight['asientos_total'] ?? $editFlight['totalSeats'] ?? 30)) ?>"></div>
                                <div class="col-md-4"><label class="volara-label" for="asientos_disponibles">Disponibles *</label><input class="volara-input" type="number" id="asientos_disponibles" name="asientos_disponibles" min="0" required value="<?= e((string)($editFlight['asientos_disponibles'] ?? $editFlight['availableSeats'] ?? 30)) ?>"></div>
                                <div class="col-md-6"><label class="volara-label" for="avion_modelo">Modelo</label><input class="volara-input" id="avion_modelo" name="avion_modelo" value="<?= e($editFlight['avion_modelo'] ?? $editFlight['model'] ?? '') ?>"></div>
                                <div class="col-md-6"><label class="volara-label" for="estado">Estado</label><select class="volara-select" id="estado" name="estado"><option value="programado" <?= ($editFlight['estado'] ?? $editFlight['status'] ?? 'programado') === 'programado' ? 'selected' : '' ?>>Programado</option><option value="cancelado" <?= ($editFlight['estado'] ?? $editFlight['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option><option value="completado" <?= ($editFlight['estado'] ?? $editFlight['status'] ?? '') === 'completado' ? 'selected' : '' ?>>Completado</option></select></div>
                                <div class="col-md-6"><label class="volara-label" for="avion_distancia">Distancia</label><input class="volara-input" id="avion_distancia" name="avion_distancia" value="<?= e($editFlight['avion_distancia'] ?? $editFlight['distance'] ?? '') ?>"></div>
                                <div class="col-md-6"><label class="volara-label" for="avion_velocidad">Velocidad</label><input class="volara-input" id="avion_velocidad" name="avion_velocidad" value="<?= e($editFlight['avion_velocidad'] ?? $editFlight['speed'] ?? '') ?>"></div>
                            </div>
                            <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-volara"><i class="bi bi-check-lg" aria-hidden="true"></i> Guardar</button><?php if ($editFlight): ?><a href="<?= url('pages/ceo/vuelos.php') ?>" class="btn btn-volara-outline">Cancelar</a><?php endif; ?></div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-7"><div class="volara-card"><h2 class="h4 mb-1">Vuelos de mi aerolínea</h2><p class="text-muted mb-3"><?= $pagination['total'] ?> registros</p><div class="volara-table-responsive"><table class="volara-table"><caption class="visually-hidden">Vuelos gestionados por mi aerolínea</caption><thead><tr><th scope="col">Vuelo</th><th scope="col">Ruta</th><th scope="col">Salida</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead><tbody><?php foreach ($flights as $flight): ?><tr><td><strong><?= e($flight['codigo']) ?></strong><small class="d-block text-muted"><?= e(ucfirst($flight['clase'])) ?></small></td><td><?= e($flight['origen_codigo']) ?> → <?= e($flight['destino_codigo']) ?><small class="d-block text-muted"><?= e($flight['origen']) ?> / <?= e($flight['destino']) ?></small></td><td><?= formatDate($flight['fecha_salida']) ?><small class="d-block text-muted"><?= formatTime($flight['fecha_salida']) ?></small></td><td><span class="volara-badge <?= badgeClass($flight['estado']) ?>"><?= estadoLabel($flight['estado']) ?></span><small class="d-block text-muted"><?= (int)$flight['asientos_disponibles'] ?> disponibles</small></td><td><div class="table-actions"><a class="table-action-btn" href="?editar=<?= (int)$flight['id'] ?>" aria-label="Editar <?= e($flight['codigo']) ?>" title="Editar"><i class="bi bi-pencil" aria-hidden="true"></i></a><form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="vuelo_id" value="<?= (int)$flight['id'] ?>"><button type="submit" class="table-action-btn danger" data-confirm="¿Eliminar este vuelo?" aria-label="Eliminar <?= e($flight['codigo']) ?>" title="Eliminar"><i class="bi bi-trash" aria-hidden="true"></i></button></form></div></td></tr><?php endforeach; ?></tbody></table></div><?php if ($pagination['total_pages'] > 1): ?><nav class="volara-pagination mt-4" aria-label="Paginación de vuelos"><?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?><?php if ($i === $pagination['current']): ?><span class="active" aria-current="page"><?= $i ?></span><?php else: ?><a href="?p=<?= $i ?>"><?= $i ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; ?></div></div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
