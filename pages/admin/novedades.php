<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

$pageTitle = 'Gestión de novedades';
$errors = [];
$editNews = null;
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newsId = (int)($_POST['novedad_id'] ?? 0);
    $title = trim($_POST['titulo'] ?? '');
    $content = trim($_POST['contenido'] ?? '');
    $active = isset($_POST['activa']) ? 1 : 0;
    $startDate = $_POST['fecha_inicio'] ?: null;
    $expirationDate = $_POST['fecha_expiracion'] ?: null;

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif ($action === 'guardar') {
        if (mb_strlen($title) < 3 || mb_strlen($title) > 200) $errors[] = 'El título debe tener entre 3 y 200 caracteres.';
        if (mb_strlen($content) < 10) $errors[] = 'El contenido debe tener al menos 10 caracteres.';
        if ($startDate && $expirationDate && $expirationDate < $startDate) $errors[] = 'La expiración debe ser posterior al inicio.';

        if (!$errors) {
            try {
                $db = getDB();
                if ($newsId > 0) {
                    $stmt = $db->prepare('UPDATE novedades SET titulo = ?, contenido = ?, activa = ?, fecha_inicio = ?, fecha_expiracion = ? WHERE id = ?');
                    $stmt->execute([$title, $content, $active, $startDate, $expirationDate, $newsId]);
                    setFlash('success', 'Novedad actualizada correctamente.');
                } else {
                    $stmt = $db->prepare('INSERT INTO novedades (titulo, contenido, activa, fecha_inicio, fecha_expiracion) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$title, $content, $active, $startDate, $expirationDate]);
                    setFlash('success', 'Novedad creada correctamente.');
                }
                redirect('pages/admin/novedades.php');
            } catch (PDOException $e) {
                $errors[] = 'No se pudo guardar la novedad.';
            }
        }
        // Normalizar nombres de campos para consistencia en el formulario
        $editNews = [
            'id' => $newsId,
            'titulo' => $title,
            'contenido' => $content,
            'activa' => $active,
            'fecha_inicio' => $startDate,
            'fecha_expiracion' => $expirationDate
        ];
    } elseif ($action === 'eliminar' && $newsId > 0) {
        try {
            $db = getDB();
            $stmt = $db->prepare('DELETE FROM novedades WHERE id = ?');
            $stmt->execute([$newsId]);
            setFlash('success', 'Novedad eliminada correctamente.');
            redirect('pages/admin/novedades.php');
        } catch (PDOException $e) {
            $errors[] = 'No se pudo eliminar la novedad.';
        }
    }
}

try {
    $db = getDB();
    if (isset($_GET['editar'])) {
        $stmt = $db->prepare('SELECT * FROM novedades WHERE id = ?');
        $stmt->execute([(int)$_GET['editar']]);
        $editNews = $stmt->fetch() ?: null;
    }
    
    // Paginación de novedades
    $countStmt = $db->query('SELECT COUNT(*) FROM novedades');
    $pagination = paginate((int)$countStmt->fetchColumn(), max(1, (int)($_GET['p'] ?? 1)));
    
    $stmt = $db->prepare(
        "SELECT * FROM novedades 
         ORDER BY COALESCE(fecha_inicio, created_at, NOW()) DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->bindValue(1, $pagination['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $news = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'No se pudieron cargar las novedades.';
    $news = [];
    $pagination = paginate(0, 1);
}

$flash = getFlash();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main><section class="page-header"><div class="container"><h1>Gestión de novedades</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/admin/inicioAdmin.php') ?>">Panel admin</a></li><li class="breadcrumb-item active">Novedades</li></ol></nav></div></section><section class="section"><div class="container">
<?php if ($errors): ?><div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div><?php endif; ?><?php if ($flash): ?><div class="volara-alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
<div class="row g-4 align-items-start"><div class="col-lg-4"><div class="volara-card"><h2 class="h4 mb-3"><?= $editNews ? 'Editar novedad' : 'Nueva novedad' ?></h2><form method="POST" data-validate novalidate><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="guardar"><input type="hidden" name="novedad_id" value="<?= (int)($editNews['id'] ?? 0) ?>"><div class="form-group"><label class="volara-label" for="titulo">Título *</label><input class="volara-input" id="titulo" name="titulo" maxlength="200" required value="<?= e($editNews['titulo'] ?? '') ?>"></div><div class="form-group"><label class="volara-label" for="contenido">Contenido *</label><textarea class="volara-input" id="contenido" name="contenido" rows="6" required><?= e($editNews['contenido'] ?? '') ?></textarea></div><div class="row g-3"><div class="col-md-6"><label class="volara-label" for="fecha_inicio">Publicar desde</label><input class="volara-input" type="date" id="fecha_inicio" name="fecha_inicio" value="<?= e($editNews['fecha_inicio'] ?? '') ?>"></div><div class="col-md-6"><label class="volara-label" for="fecha_expiracion">Expira el</label><input class="volara-input" type="date" id="fecha_expiracion" name="fecha_expiracion" value="<?= e($editNews['fecha_expiracion'] ?? '') ?>"></div></div><div class="form-check mt-3"><input class="form-check-input" type="checkbox" id="activa" name="activa" <?= (int)($editNews['activa'] ?? 1) === 1 ? 'checked' : '' ?>><label class="form-check-label" for="activa">Publicada</label></div><div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-volara"><i class="bi bi-check-lg" aria-hidden="true"></i> Guardar</button><?php if ($editNews): ?><a href="<?= url('pages/admin/novedades.php') ?>" class="btn btn-volara-outline">Cancelar</a><?php endif; ?></div></form></div></div>
<div class="col-lg-8"><div class="volara-card"><h2 class="h4 mb-3">Novedades registradas</h2><?php if (!$news): ?><div class="empty-state"><p>Todavía no hay novedades. Creá una nueva para comenzar.</p></div><?php else: ?><div class="volara-table-responsive"><table class="volara-table"><caption class="visually-hidden">Listado de novedades</caption><thead><tr><th scope="col">Título</th><th scope="col">Vigencia</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead><tbody><?php foreach ($news as $item): ?><tr><td><strong><?= e($item['titulo']) ?></strong><small class="d-block text-muted"><?= e(mb_strimwidth($item['contenido'], 0, 90, '...')) ?></small></td><td><?= $item['fecha_inicio'] ? formatDate($item['fecha_inicio']) : 'Inmediata' ?><?php if ($item['fecha_expiracion']): ?><small class="d-block text-muted">hasta <?= formatDate($item['fecha_expiracion']) ?></small><?php endif; ?></td><td><span class="volara-badge <?= badgeClass($item['activa'] ? 'activa' : 'inactiva') ?>"><?= $item['activa'] ? 'Publicada' : 'Oculta' ?></span></td><td><div class="table-actions"><a class="table-action-btn" href="?editar=<?= (int)$item['id'] ?>" aria-label="Editar <?= e($item['titulo']) ?>" title="Editar"><i class="bi bi-pencil" aria-hidden="true"></i></a><form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="novedad_id" value="<?= (int)$item['id'] ?>"><button type="submit" class="table-action-btn danger" data-confirm="¿Eliminar esta novedad?" aria-label="Eliminar <?= e($item['titulo']) ?>" title="Eliminar"><i class="bi bi-trash" aria-hidden="true"></i></button></form></div></td></tr><?php endforeach; ?></tbody></table></div><?php if ($pagination['pages'] > 1): ?><nav class="pagination mt-3" aria-label="Paginación"><ul class="pagination"><?php if ($pagination['current'] > 1): ?><li class="page-item"><a class="page-link" href="?p=1">Primera</a></li><li class="page-item"><a class="page-link" href="?p=<?= $pagination['current'] - 1 ?>">Anterior</a></li><?php endif; ?><?php for ($i = max(1, $pagination['current'] - 2); $i <= min($pagination['pages'], $pagination['current'] + 2); $i++): ?><li class="page-item <?= $i === $pagination['current'] ? 'active' : '' ?>"><a class="page-link" href="?p=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?><?php if ($pagination['current'] < $pagination['pages']): ?><li class="page-item"><a class="page-link" href="?p=<?= $pagination['current'] + 1 ?>">Siguiente</a></li><li class="page-item"><a class="page-link" href="?p=<?= $pagination['pages'] ?>">Última</a></li><?php endif; ?></ul></nav><?php endif; ?><?php endif; ?></div></div></div></div></section></main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
