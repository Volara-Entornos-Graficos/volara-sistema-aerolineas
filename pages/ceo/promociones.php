<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('ceo');

$pageTitle = 'Gestión de promociones';
$user = currentUser();
$airlineId = (int)($user['aerolinea_id'] ?? 0);
$errors = [];
$editPromotion = null;
$promotions = [];
$pagination = paginate(0, 1);
$action = $_POST['action'] ?? '';

if ($airlineId < 1) {
    $errors[] = 'Tu cuenta CEO no tiene una aerolínea asociada.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $airlineId > 0) {
    $promotionId = (int)($_POST['promocion_id'] ?? 0);
    $title = trim($_POST['titulo'] ?? '');
    $description = trim($_POST['descripcion'] ?? '');
    $discount = (float)($_POST['descuento_porcentaje'] ?? 0);
    $startDate = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
    $endDate = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif ($action === 'guardar') {
        if (mb_strlen($title) < 3 || mb_strlen($title) > 120) {
            $errors[] = 'El título debe tener entre 3 y 120 caracteres.';
        }

        if ($discount <= 0 || $discount > 100) {
            $errors[] = 'El descuento debe estar entre 0,01% y 100%.';
        }

        if ($startDate && $endDate && $endDate < $startDate) {
            $errors[] = 'La fecha de fin debe ser posterior al inicio.';
        }

        if (!$errors) {
            try {
                $db = getDB();

                if ($promotionId > 0) {
                    $check = $db->prepare(
                        "SELECT estado
                         FROM promociones
                         WHERE id = ? AND aerolinea_id = ?"
                    );
                    $check->execute([$promotionId, $airlineId]);
                    $existing = $check->fetch();

                    if (!$existing) {
                        $errors[] = 'La promoción no existe o no pertenece a tu aerolínea.';
                    } elseif ($existing['estado'] === 'vigente') {
                        $errors[] = 'Una promoción vigente no puede editarse directamente.';
                    } else {
                        $stmt = $db->prepare(
                            "UPDATE promociones
                             SET titulo = ?,
                                 descripcion = ?,
                                 descuento_porcentaje = ?,
                                 fecha_inicio = ?,
                                 fecha_fin = ?,
                                 estado = 'pendiente'
                             WHERE id = ? AND aerolinea_id = ?"
                        );

                        $stmt->execute([
                            $title,
                            $description ?: null,
                            $discount,
                            $startDate,
                            $endDate,
                            $promotionId,
                            $airlineId
                        ]);

                        setFlash(
                            'success',
                            'Promoción actualizada y enviada nuevamente a aprobación.'
                        );

                        redirect('pages/ceo/promociones.php');
                    }
                } else {
                    $stmt = $db->prepare(
                        "INSERT INTO promociones
                            (aerolinea_id, titulo, descripcion, descuento_porcentaje,
                             estado, fecha_inicio, fecha_fin)
                         VALUES (?, ?, ?, ?, 'pendiente', ?, ?)"
                    );

                    $stmt->execute([
                        $airlineId,
                        $title,
                        $description ?: null,
                        $discount,
                        $startDate,
                        $endDate
                    ]);

                    setFlash(
                        'success',
                        'Promoción creada y enviada a aprobación.'
                    );

                    redirect('pages/ceo/promociones.php');
                }
            } catch (PDOException $e) {
                $errors[] = 'No se pudo guardar la promoción.';
            }
        }

        $editPromotion = [
            'id' => $promotionId,
            'titulo' => $title,
            'descripcion' => $description,
            'descuento_porcentaje' => $discount,
            'fecha_inicio' => $startDate,
            'fecha_fin' => $endDate,
        ];
    } elseif ($action === 'eliminar' && $promotionId > 0) {
        try {
            $db = getDB();

            $stmt = $db->prepare(
                "DELETE FROM promociones
                 WHERE id = ?
                   AND aerolinea_id = ?
                   AND estado <> 'vigente'"
            );

            $stmt->execute([$promotionId, $airlineId]);

            if ($stmt->rowCount() === 1) {
                setFlash(
                    'success',
                    'Promoción eliminada correctamente.'
                );

                redirect('pages/ceo/promociones.php');
            }

            $errors[] = 'No se puede eliminar una promoción vigente o inexistente.';
        } catch (PDOException $e) {
            $errors[] = 'No se pudo eliminar la promoción.';
        }
    }
}

try {
    $db = getDB();

    if (isset($_GET['editar']) && $airlineId > 0) {
        $stmt = $db->prepare(
            "SELECT *
             FROM promociones
             WHERE id = ?
               AND aerolinea_id = ?
               AND estado <> 'vigente'"
        );

        $stmt->execute([
            (int)$_GET['editar'],
            $airlineId
        ]);

        $editPromotion = $stmt->fetch() ?: null;
    }

    $countStmt = $db->prepare(
        "SELECT COUNT(*)
         FROM promociones
         WHERE aerolinea_id = ?"
    );

    $countStmt->execute([$airlineId]);

    $total = (int)$countStmt->fetchColumn();

    $page = max(1, (int)($_GET['p'] ?? 1));
    $pagination = paginate($total, $page);

    $stmt = $db->prepare(
        "SELECT *
         FROM promociones
         WHERE aerolinea_id = ?
         ORDER BY created_at DESC
         LIMIT ? OFFSET ?"
    );

    $stmt->bindValue(1, $airlineId, PDO::PARAM_INT);
    $stmt->bindValue(2, $pagination['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(3, $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();

    $promotions = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'No se pudieron cargar las promociones.';
    $promotions = [];
    $pagination = paginate(0, 1);
}

$flash = getFlash();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main id="contenido-principal" class="management-page" tabindex="-1">
    <section class="page-header">
        <div class="container">
            <h1>Gestión de promociones</h1>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= url('pages/ceo/inicioCeo.php') ?>">
                            Panel CEO
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Promociones
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">

            <?php if ($errors): ?>
                <div class="volara-alert alert-danger" role="alert">
                    <?= e(implode(' ', $errors)) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash): ?>
                <div
                    class="volara-alert alert-<?= e($flash['type']) ?>"
                    role="status"
                >
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="row g-4 align-items-start">

                <div class="col-lg-4">
                    <div class="volara-card">

                        <h2 class="h4 mb-3">
                            <?= $editPromotion
                                ? 'Editar promoción'
                                : 'Nueva promoción' ?>
                        </h2>

                        <form method="POST" data-validate novalidate>

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e(csrfToken()) ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="guardar"
                            >

                            <input
                                type="hidden"
                                name="promocion_id"
                                value="<?= (int)($editPromotion['id'] ?? 0) ?>"
                            >

                            <div class="form-group">
                                <label
                                    class="volara-label"
                                    for="titulo"
                                >
                                    Título *
                                </label>

                                <input
                                    class="volara-input"
                                    type="text"
                                    id="titulo"
                                    name="titulo"
                                    maxlength="120"
                                    required
                                    value="<?= e($editPromotion['titulo'] ?? '') ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label
                                    class="volara-label"
                                    for="descripcion"
                                >
                                    Descripción
                                </label>

                                <textarea
                                    class="volara-input"
                                    id="descripcion"
                                    name="descripcion"
                                    rows="4"
                                ><?= e($editPromotion['descripcion'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label
                                    class="volara-label"
                                    for="descuento_porcentaje"
                                >
                                    Descuento (%) *
                                </label>

                                <input
                                    class="volara-input"
                                    type="number"
                                    id="descuento_porcentaje"
                                    name="descuento_porcentaje"
                                    min="0.01"
                                    max="100"
                                    step="0.01"
                                    required
                                    value="<?= e(
                                        (string)($editPromotion['descuento_porcentaje'] ?? '')
                                    ) ?>"
                                >
                            </div>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label
                                        class="volara-label"
                                        for="fecha_inicio"
                                    >
                                        Desde
                                    </label>

                                    <input
                                        class="volara-input"
                                        type="date"
                                        id="fecha_inicio"
                                        name="fecha_inicio"
                                        value="<?= e(
                                            $editPromotion['fecha_inicio'] ?? ''
                                        ) ?>"
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label
                                        class="volara-label"
                                        for="fecha_fin"
                                    >
                                        Hasta
                                    </label>

                                    <input
                                        class="volara-input"
                                        type="date"
                                        id="fecha_fin"
                                        name="fecha_fin"
                                        value="<?= e(
                                            $editPromotion['fecha_fin'] ?? ''
                                        ) ?>"
                                    >
                                </div>

                            </div>

                            <div class="form-actions d-flex gap-2 mt-4">

                                <button
                                    type="submit"
                                    class="btn btn-volara flex-grow"
                                >
                                    <i
                                        class="bi bi-send"
                                        aria-hidden="true"
                                    ></i>
                                    <?= $editPromotion
                                        ? 'Guardar cambios'
                                        : 'Enviar a aprobación' ?>
                                </button>

                                <?php if ($editPromotion): ?>
                                    <a
                                        href="<?= url('pages/ceo/promociones.php') ?>"
                                        class="btn btn-volara-outline"
                                    >
                                        Cancelar
                                    </a>
                                <?php endif; ?>

                            </div>

                        </form>

                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="volara-card">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h2 class="h4 mb-1">
                                    Promociones de mi aerolínea
                                </h2>

                                <p class="text-muted mb-0">
                                    <?= (int)$pagination['total'] ?> registros
                                </p>
                            </div>
                        </div>

                        <?php if (!$promotions): ?>

                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-megaphone" aria-hidden="true"></i>
                                </div>

                                <p class="mb-0">
                                    Todavía no hay promociones registradas.
                                </p>
                            </div>

                        <?php else: ?>

                            <div
                                class="volara-table-responsive"
                                role="region"
                                aria-label="Promociones de mi aerolínea"
                                tabindex="0"
                            >
                                <table class="volara-table">

                                    <caption class="visually-hidden">
                                        Promociones de mi aerolínea
                                    </caption>

                                    <thead>
                                        <tr>
                                            <th scope="col">Promoción</th>
                                            <th scope="col">Descuento</th>
                                            <th scope="col">Vigencia</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Acciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php foreach ($promotions as $promotion): ?>

                                            <?php
                                            $badgeClass = match ($promotion['estado']) {
                                                'vigente' => 'approved',
                                                'denegada' => 'denied',
                                                'pendiente' => 'pending',
                                                default => 'neutral',
                                            };
                                            ?>

                                            <tr>

                                                <td>
                                                    <strong>
                                                        <?= e($promotion['titulo']) ?>
                                                    </strong>

                                                    <?php if (!empty($promotion['descripcion'])): ?>
                                                        <small class="d-block text-muted">
                                                            <?= e($promotion['descripcion']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?= number_format(
                                                        (float)$promotion['descuento_porcentaje'],
                                                        2,
                                                        ',',
                                                        '.'
                                                    ) ?>%
                                                </td>

                                                <td>
                                                    <?= $promotion['fecha_inicio']
                                                        ? formatDate($promotion['fecha_inicio'])
                                                        : 'Sin inicio' ?>

                                                    <?php if ($promotion['fecha_fin']): ?>
                                                        <small class="d-block text-muted">
                                                            hasta <?= formatDate($promotion['fecha_fin']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <span class="volara-badge badge-<?= $badgeClass ?>">
                                                        <?= e(estadoLabel($promotion['estado'])) ?>
                                                    </span>
                                                </td>

                                                <td>

                                                    <div class="table-actions">

                                                        <?php if ($promotion['estado'] !== 'vigente'): ?>

                                                            <a
                                                                class="table-action-btn"
                                                                href="?editar=<?= (int)$promotion['id'] ?>"
                                                                aria-label="Editar <?= e($promotion['titulo']) ?>"
                                                                title="Editar"
                                                            >
                                                                <i
                                                                    class="bi bi-pencil"
                                                                    aria-hidden="true"
                                                                ></i>
                                                            </a>

                                                            <form method="POST">
                                                                <input
                                                                    type="hidden"
                                                                    name="csrf_token"
                                                                    value="<?= e(csrfToken()) ?>"
                                                                >

                                                                <input
                                                                    type="hidden"
                                                                    name="action"
                                                                    value="eliminar"
                                                                >

                                                                <input
                                                                    type="hidden"
                                                                    name="promocion_id"
                                                                    value="<?= (int)$promotion['id'] ?>"
                                                                >

                                                                <button
                                                                    type="submit"
                                                                    class="table-action-btn danger"
                                                                    data-confirm="¿Eliminar esta promoción?"
                                                                    aria-label="Eliminar <?= e($promotion['titulo']) ?>"
                                                                    title="Eliminar"
                                                                >
                                                                    <i
                                                                        class="bi bi-trash"
                                                                        aria-hidden="true"
                                                                    ></i>
                                                                </button>
                                                            </form>

                                                        <?php endif; ?>

                                                    </div>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>

                            <?php if ($pagination['total_pages'] > 1): ?>

                                <nav
                                    class="volara-pagination mt-3"
                                    aria-label="Paginación de promociones"
                                >

                                    <?php for (
                                        $i = 1;
                                        $i <= $pagination['total_pages'];
                                        $i++
                                    ): ?>

                                        <?php if ($i === $pagination['current']): ?>

                                            <span
                                                class="active"
                                                aria-current="page"
                                            >
                                                <?= $i ?>
                                            </span>

                                        <?php else: ?>

                                            <a href="?p=<?= $i ?>">
                                                <?= $i ?>
                                            </a>

                                        <?php endif; ?>

                                    <?php endfor; ?>

                                </nav>

                            <?php endif; ?>

                        <?php endif; ?>

                    </div>
                </div>

            </div>

        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>