<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

$pageTitle = 'Gestión de aerolíneas';
$errors = [];
$editAirline = null;
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['codigo'] ?? ''));
    $name = trim($_POST['nombre'] ?? '');
    $description = trim($_POST['descripcion'] ?? '');
    $status = $_POST['estado'] ?? 'activa';
    $airlineId = (int)($_POST['aerolinea_id'] ?? 0);

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif ($action === 'guardar') {
        if (!preg_match('/^[A-Z0-9]{2,10}$/', $code)) {
            $errors[] = 'El código debe tener entre 2 y 10 letras o números.';
        }
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $errors[] = 'El nombre debe tener entre 2 y 120 caracteres.';
        }
        if (!in_array($status, ['activa', 'inactiva'], true)) {
            $errors[] = 'El estado seleccionado no es válido.';
        }

        if (!$errors) {
            try {
                $db = getDB();
                $check = $db->prepare('SELECT id FROM aerolineas WHERE codigo = ? AND id <> ?');
                $check->execute([$code, $airlineId]);
                if ($check->fetch()) {
                    $errors[] = 'Ya existe una aerolínea con ese código.';
                } elseif ($airlineId > 0) {
                    $stmt = $db->prepare(
                        'UPDATE aerolineas SET codigo = ?, nombre = ?, descripcion = ?, estado = ? WHERE id = ?'
                    );
                    $stmt->execute([$code, $name, $description ?: null, $status, $airlineId]);
                    setFlash('success', 'Aerolínea actualizada correctamente.');
                    redirect('pages/admin/aerolineas.php');
                } else {
                    $stmt = $db->prepare(
                        'INSERT INTO aerolineas (codigo, nombre, descripcion, estado) VALUES (?, ?, ?, ?)'
                    );
                    $stmt->execute([$code, $name, $description ?: null, $status]);
                    setFlash('success', 'Aerolínea creada correctamente.');
                    redirect('pages/admin/aerolineas.php');
                }
            } catch (PDOException $e) {
                $errors[] = 'No se pudo guardar la aerolínea.';
            }
        }

        $editAirline = [
            'id' => $airlineId,
            'codigo' => $code,
            'nombre' => $name,
            'descripcion' => $description,
            'estado' => $status,
        ];
    } elseif ($action === 'eliminar' && $airlineId > 0) {
        try {
            $db = getDB();
            $dependency = $db->prepare(
                'SELECT
                    (SELECT COUNT(*) FROM vuelos WHERE aerolinea_id = ?) +
                    (SELECT COUNT(*) FROM promociones WHERE aerolinea_id = ?) +
                    (SELECT COUNT(*) FROM usuarios WHERE aerolinea_id = ? AND rol = \'ceo\') AS total'
            );
            $dependency->execute([$airlineId, $airlineId, $airlineId]);

            if ((int)$dependency->fetchColumn() > 0) {
                $errors[] = 'No se puede eliminar una aerolínea con vuelos, promociones o CEOs asociados. Podés marcarla como inactiva.';
            } else {
                $stmt = $db->prepare('DELETE FROM aerolineas WHERE id = ?');
                $stmt->execute([$airlineId]);
                setFlash('success', 'Aerolínea eliminada correctamente.');
                redirect('pages/admin/aerolineas.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'No se pudo eliminar la aerolínea.';
        }
    }
}

try {
    $db = getDB();
    if (isset($_GET['editar'])) {
        $stmt = $db->prepare('SELECT * FROM aerolineas WHERE id = ?');
        $stmt->execute([(int)$_GET['editar']]);
        $editAirline = $stmt->fetch() ?: null;
    }

    $total = (int)$db->query('SELECT COUNT(*) FROM aerolineas')->fetchColumn();
    $page = max(1, (int)($_GET['p'] ?? 1));
    $pagination = paginate($total, $page);
    $stmt = $db->query(
        "SELECT * FROM aerolineas ORDER BY nombre ASC
         LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}"
    );
    $airlines = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'No se pudieron cargar las aerolíneas.';
    $airlines = [];
    $pagination = paginate(0, 1);
}

$flash = getFlash();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header">
        <div class="container">
            <h1>Gestión de aerolíneas</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('pages/admin/inicioAdmin.php') ?>">Panel admin</a></li>
                    <li class="breadcrumb-item active">Aerolíneas</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if ($errors): ?>
                <div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div>
            <?php endif; ?>
            <?php if ($flash): ?>
                <div class="volara-alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <div class="row g-4 align-items-start">
                <div class="col-lg-4">
                    <div class="volara-card">
                        <h2 class="h4 mb-3"><?= $editAirline ? 'Editar aerolínea' : 'Nueva aerolínea' ?></h2>
                        <form method="POST" data-validate novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="action" value="guardar">
                            <input type="hidden" name="aerolinea_id" value="<?= (int)($editAirline['id'] ?? 0) ?>">

                            <div class="form-group">
                                <label class="volara-label" for="codigo">Código *</label>
                                <input class="volara-input" type="text" id="codigo" name="codigo" maxlength="10"
                                       value="<?= e($editAirline['codigo'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="volara-label" for="nombre">Nombre *</label>
                                <input class="volara-input" type="text" id="nombre" name="nombre" maxlength="120"
                                       value="<?= e($editAirline['nombre'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="volara-label" for="descripcion">Descripción</label>
                                <textarea class="volara-input" id="descripcion" name="descripcion" rows="4"><?= e($editAirline['descripcion'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="volara-label" for="estado">Estado</label>
                                <select class="volara-select" id="estado" name="estado">
                                    <option value="activa" <?= ($editAirline['estado'] ?? 'activa') === 'activa' ? 'selected' : '' ?>>Activa</option>
                                    <option value="inactiva" <?= ($editAirline['estado'] ?? '') === 'inactiva' ? 'selected' : '' ?>>Inactiva</option>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-volara">
                                    <i class="bi bi-check-lg" aria-hidden="true"></i> Guardar
                                </button>
                                <?php if ($editAirline): ?>
                                    <a href="<?= url('pages/admin/aerolineas.php') ?>" class="btn btn-volara-outline">Cancelar</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="volara-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h2 class="h4 mb-1">Aerolíneas registradas</h2>
                                <p class="text-muted mb-0"><?= $pagination['total'] ?> registros</p>
                            </div>
                        </div>
                        <div class="volara-table-responsive">
                            <table class="volara-table">
                                <caption class="visually-hidden">Listado de aerolíneas</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Código</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($airlines as $airline): ?>
                                        <tr>
                                            <td><strong><?= e($airline['codigo']) ?></strong></td>
                                            <td><?= e($airline['nombre']) ?></td>
                                            <td><span class="volara-badge <?= badgeClass($airline['estado']) ?>"><?= estadoLabel($airline['estado']) ?></span></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a class="table-action-btn" href="?editar=<?= (int)$airline['id'] ?>" aria-label="Editar <?= e($airline['nombre']) ?>" title="Editar">
                                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                                    </a>
                                                    <form method="POST">
                                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                                        <input type="hidden" name="action" value="eliminar">
                                                        <input type="hidden" name="aerolinea_id" value="<?= (int)$airline['id'] ?>">
                                                        <button type="submit" class="table-action-btn danger" data-confirm="¿Eliminar esta aerolínea?" aria-label="Eliminar <?= e($airline['nombre']) ?>" title="Eliminar">
                                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <nav class="volara-pagination mt-4" aria-label="Paginación de aerolíneas">
                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <?php if ($i === $pagination['current']): ?>
                                        <span class="active" aria-current="page"><?= $i ?></span>
                                    <?php else: ?>
                                        <a href="?p=<?= $i ?>"><?= $i ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
