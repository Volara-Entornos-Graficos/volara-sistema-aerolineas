<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Novedades';
try {
    $db = getDB();
    $stmt = $db->query("SELECT titulo, contenido, created_at FROM novedades WHERE activa = 1 AND (fecha_inicio IS NULL OR fecha_inicio <= CURDATE()) AND (fecha_expiracion IS NULL OR fecha_expiracion >= CURDATE()) ORDER BY created_at DESC");
    $news = $stmt->fetchAll();
} catch (PDOException $e) {
    $news = [];
}
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main><section class="page-header"><div class="container"><h1>Novedades</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li><li class="breadcrumb-item active">Novedades</li></ol></nav></div></section><section class="section"><div class="container"><?php if (!$news): ?><div class="empty-state"><div class="empty-state-icon"><i class="bi bi-newspaper"></i></div><h2 class="h4">No hay novedades publicadas</h2><p>Volvé pronto para conocer las noticias de VOLARA.</p></div><?php else: ?><div class="row g-4"><?php foreach ($news as $item): ?><div class="col-md-6 col-lg-4"><article class="news-card h-100"><div class="news-card-body"><div class="news-card-date"><i class="bi bi-calendar3 me-1" aria-hidden="true"></i><?= formatDate($item['created_at']) ?></div><h2 class="h5"><?= e($item['titulo']) ?></h2><p><?= e($item['contenido']) ?></p></div></article></div><?php endforeach; ?></div><?php endif; ?></div></section></main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
