<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

$pageTitle = 'Panel de administración';
$errors = [];
$pendingCeos = [];
$stats = [
    'airlines' => 0,
    'flights' => 0,
    'users' => 0,
    'confirmed' => 0,
    'sales' => 0,
    'pendingPromotions' => 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['usuario_id'] ?? 0);

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif (!in_array($action, ['aprobar', 'rechazar'], true) || $userId < 1) {
        $errors[] = 'La solicitud de aprobación no es válida.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "UPDATE usuarios
                 SET estado_aprobacion = ?, activo = ?
                 WHERE id = ? AND rol = 'ceo' AND estado_aprobacion = 'pendiente'"
            );
            $stmt->execute([
                $action === 'aprobar' ? 'aprobado' : 'rechazado',
                $action === 'aprobar' ? 1 : 0,
                $userId,
            ]);

            if ($stmt->rowCount() === 1) {
                setFlash(
                    $action === 'aprobar' ? 'success' : 'warning',
                    $action === 'aprobar' ? 'CEO aprobado correctamente.' : 'Solicitud de CEO rechazada.'
                );
            } else {
                $errors[] = 'La solicitud ya fue procesada o no existe.';
            }
        } catch (PDOException $e) {
            $errors[] = 'No se pudo actualizar la solicitud.';
        }
    }
}

try {
    $db = getDB();
    $stats['airlines'] = (int)$db->query("SELECT COUNT(*) FROM aerolineas WHERE estado = 'activa'")->fetchColumn();
    $stats['flights'] = (int)$db->query("SELECT COUNT(*) FROM vuelos WHERE estado = 'programado'")->fetchColumn();
    $stats['users'] = (int)$db->query('SELECT COUNT(*) FROM usuarios WHERE activo = 1')->fetchColumn();
    $stats['confirmed'] = (int)$db->query("SELECT COUNT(*) FROM reservas WHERE estado = 'confirmada'")->fetchColumn();
    $stats['sales'] = (float)$db->query("SELECT COALESCE(SUM(precio_final), 0) FROM reservas WHERE estado = 'confirmada'")->fetchColumn();
    $stats['pendingPromotions'] = (int)$db->query("SELECT COUNT(*) FROM promociones WHERE estado = 'pendiente'")->fetchColumn();
    $pendingCeos = $db->query(
        "SELECT u.id, u.nombre, u.apellido, u.email, u.created_at,
                a.nombre AS aerolinea_nombre, a.codigo AS aerolinea_codigo
         FROM usuarios u
         JOIN aerolineas a ON a.id = u.aerolinea_id
         WHERE u.rol = 'ceo' AND u.estado_aprobacion = 'pendiente'
         ORDER BY u.created_at ASC"
    )->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'No se pudieron cargar las solicitudes pendientes.';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header">
        <div class="container">
            <h1>Panel de administración</h1>
            <p class="mb-0">Gestioná el contenido general de VOLARA desde un solo lugar.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if ($errors): ?>
                <div class="volara-alert alert-danger" role="alert">
                    <?= e(implode(' ', $errors)) ?>
                </div>
            <?php endif; ?>

            <?php $flash = getFlash(); if ($flash): ?>
                <div class="volara-alert alert-<?= e($flash['type']) ?>" role="alert">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-5">
                <?php foreach ([
                    ['airlines', 'Aerolíneas activas', 'bi-building', $stats['airlines']],
                    ['flights', 'Vuelos programados', 'bi-airplane', $stats['flights']],
                    ['users', 'Usuarios activos', 'bi-people', $stats['users']],
                    ['confirmed', 'Reservas confirmadas', 'bi-ticket-perforated', $stats['confirmed']],
                    ['sales', 'Ventas confirmadas', 'bi-cash-stack', formatPrice($stats['sales'])],
                    ['pendingPromotions', 'Promociones pendientes', 'bi-megaphone', $stats['pendingPromotions']],
                ] as $stat): ?>
                    <div class="col-6 col-xl-2">
                        <article class="stat-card h-100">
                            <div class="stat-icon stat-icon-red"><i class="bi <?= e($stat[2]) ?>" aria-hidden="true"></i></div>
                            <div class="stat-value"><?= e((string)$stat[3]) ?></div>
                            <div class="stat-label"><?= e($stat[1]) ?></div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/publico/buscar.php') ?>">
                        <i class="bi bi-search fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Consultar vuelos</h2>
                        <p class="text-muted mb-0">Revisá la oferta de vuelos publicada.</p>
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
                    <a class="volara-card h-100 d-block" href="<?= url('pages/admin/aerolineas.php') ?>">
                        <i class="bi bi-building fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Aerolíneas</h2>
                        <p class="text-muted mb-0">Creá, editá y administrá aerolíneas.</p>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/admin/promociones.php') ?>">
                        <i class="bi bi-megaphone fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Aprobar promociones</h2>
                        <p class="text-muted mb-0">Revisá y publicá promociones de las aerolíneas.</p>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/admin/novedades.php') ?>">
                        <i class="bi bi-newspaper fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Gestionar novedades</h2>
                        <p class="text-muted mb-0">Publicá y programá novedades del sistema.</p>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a class="volara-card h-100 d-block" href="<?= url('pages/admin/reportes.php') ?>">
                        <i class="bi bi-bar-chart-line fs-3 text-danger"></i>
                        <h2 class="h5 mt-3">Reportes</h2>
                        <p class="text-muted mb-0">Consultá ventas, vuelos y usuarios.</p>
                    </a>
                </div>
            </div>

            <div class="volara-card mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1">Solicitudes de CEO</h2>
                        <p class="text-muted mb-0">Validá las cuentas antes de habilitar su acceso.</p>
                    </div>
                    <span class="volara-badge badge-neutral"><?= count($pendingCeos) ?> pendientes</span>
                </div>

                <?php if (!$pendingCeos): ?>
                    <div class="empty-state py-4">
                        <div class="empty-state-icon"><i class="bi bi-person-check"></i></div>
                        <p class="mb-0">No hay solicitudes pendientes.</p>
                    </div>
                <?php else: ?>
                    <div class="volara-table-responsive">
                        <table class="volara-table">
                            <caption class="visually-hidden">Solicitudes de cuentas CEO pendientes</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Usuario</th>
                                    <th scope="col">Aerolínea</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingCeos as $ceo): ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($ceo['nombre'] . ' ' . $ceo['apellido']) ?></strong>
                                            <small class="d-block text-muted"><?= e($ceo['email']) ?></small>
                                        </td>
                                        <td><?= e($ceo['aerolinea_nombre']) ?> (<?= e($ceo['aerolinea_codigo']) ?>)</td>
                                        <td><?= formatDate($ceo['created_at']) ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <form method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                                    <input type="hidden" name="usuario_id" value="<?= (int)$ceo['id'] ?>">
                                                    <input type="hidden" name="action" value="aprobar">
                                                    <button type="submit" class="btn btn-volara btn-volara-sm">
                                                        <i class="bi bi-check-lg" aria-hidden="true"></i> Aprobar
                                                    </button>
                                                </form>
                                                <form method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                                    <input type="hidden" name="usuario_id" value="<?= (int)$ceo['id'] ?>">
                                                    <input type="hidden" name="action" value="rechazar">
                                                    <button type="submit" class="btn btn-outline-danger btn-volara-sm" data-confirm="¿Rechazar esta solicitud de CEO?">
                                                        <i class="bi bi-x-lg" aria-hidden="true"></i> Rechazar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
