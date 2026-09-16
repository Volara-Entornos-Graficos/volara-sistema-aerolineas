<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireRole('admin');

$pageTitle = 'Reportes administrativos';
$type = $_GET['tipo'] ?? 'ventas';
$allowedTypes = ['ventas', 'vuelos', 'usuarios'];
if (!in_array($type, $allowedTypes, true)) {
    $type = 'ventas';
}

$from = $_GET['desde'] ?? '';
$to = $_GET['hasta'] ?? '';
$rows = [];
$summary = [];
$errors = [];
$pagination = paginate(0, 1);

try {
    $db = getDB();
    
    if ($type === 'ventas') {
        // Reporte de ventas confirmadas
        $params = ["confirmada"];
        $sql = "SELECT a.codigo AS aerolinea_codigo, a.nombre AS aerolinea_nombre, 
                COUNT(r.id) AS reservas, COALESCE(SUM(r.precio_final), 0) AS total
                FROM reservas r 
                JOIN vuelos v ON v.id = r.vuelo_id 
                JOIN aerolineas a ON a.id = v.aerolinea_id
                WHERE r.estado = ?";
        
        // Agregar filtro de fecha SOLO si se proporciona
        if ($from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $sql .= " AND DATE(r.fecha_pago) >= ?";
            $params[] = $from;
        }
        if ($to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $sql .= " AND DATE(r.fecha_pago) <= ?";
            $params[] = $to;
        }
        
        $sql .= " GROUP BY a.id ORDER BY total DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        
        $summary = [
            'title' => 'Ventas confirmadas',
            'total' => array_sum(array_column($rows, 'total') ?: [0]),
            'count' => array_sum(array_column($rows, 'reservas') ?: [0])
        ];
        
    } elseif ($type === 'vuelos') {
        // Reporte de vuelos por aerolínea
        $stmt = $db->query(
            "SELECT a.codigo AS aerolinea_codigo, a.nombre AS aerolinea_nombre, 
                    COUNT(v.id) AS vuelos,
                    COALESCE(SUM(v.asientos_total - v.asientos_disponibles), 0) AS ocupados,
                    COALESCE(SUM(v.asientos_total), 0) AS capacidad
             FROM aerolineas a 
             LEFT JOIN vuelos v ON v.aerolinea_id = a.id
             GROUP BY a.id ORDER BY vuelos DESC"
        );
        $rows = $stmt->fetchAll();
        
        $summary = [
            'title' => 'Operación de vuelos',
            'total' => array_sum(array_column($rows, 'vuelos') ?: [0]),
            'count' => count($rows)
        ];
        
    } else {
        // Reporte de usuarios
        $stmt = $db->query(
            "SELECT rol, estado_aprobacion, COUNT(*) AS cantidad 
             FROM usuarios 
             GROUP BY rol, estado_aprobacion 
             ORDER BY rol, estado_aprobacion"
        );
        $rows = $stmt->fetchAll();
        
        $summary = [
            'title' => 'Usuarios registrados',
            'total' => array_sum(array_column($rows, 'cantidad') ?: [0]),
            'count' => count($rows)
        ];
    }
    
} catch (PDOException $e) {
    error_log('Error en reporte: ' . $e->getMessage());
    $errors[] = 'No se pudo generar el reporte. Contactá al administrador.';
    $summary = ['title' => '', 'total' => 0, 'count' => 0];
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main><section class="page-header"><div class="container"><h1>Reportes administrativos</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/admin/inicioAdmin.php') ?>">Panel admin</a></li><li class="breadcrumb-item active">Reportes</li></ol></nav></div></section><section class="section"><div class="container">
<?php if ($errors): ?><div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
<div class="report-toolbar volara-card mb-4"><div class="d-flex flex-wrap gap-2"><a class="btn <?= $type === 'ventas' ? 'btn-volara' : 'btn-volara-outline' ?>" href="?tipo=ventas">Ventas</a><a class="btn <?= $type === 'vuelos' ? 'btn-volara' : 'btn-volara-outline' ?>" href="?tipo=vuelos">Vuelos</a><a class="btn <?= $type === 'usuarios' ? 'btn-volara' : 'btn-volara-outline' ?>" href="?tipo=usuarios">Usuarios</a></div><?php if ($type === 'ventas'): ?><form class="row g-2 mt-3" method="GET"><input type="hidden" name="tipo" value="ventas"><div class="col-md-4"><label class="volara-label" for="desde">Desde</label><input class="volara-input" type="date" id="desde" name="desde" value="<?= e($from) ?>"></div><div class="col-md-4"><label class="volara-label" for="hasta">Hasta</label><input class="volara-input" type="date" id="hasta" name="hasta" value="<?= e($to) ?>"></div><div class="col-md-4 d-flex align-items-end"><button class="btn btn-volara" type="submit">Filtrar reporte</button></div></form><?php endif; ?></div>
<div class="row g-3 mb-4"><div class="col-md-6"><article class="stat-card"><div class="stat-icon stat-icon-red"><i class="bi bi-bar-chart" aria-hidden="true"></i></div><div class="stat-value"><?= $type === 'ventas' ? formatPrice((float)$summary['total']) : (int)$summary['total'] ?></div><div class="stat-label"><?= e($summary['title']) ?></div></article></div><div class="col-md-6"><article class="stat-card"><div class="stat-icon stat-icon-dark"><i class="bi bi-list-check" aria-hidden="true"></i></div><div class="stat-value"><?= (int)$summary['count'] ?></div><div class="stat-label">Registros agrupados</div></article></div></div>
<div class="volara-card"><div class="volara-table-responsive"><table class="volara-table"><caption class="visually-hidden">Reporte <?= e($summary['title']) ?></caption><thead><tr><?php if ($type === 'ventas'): ?><th scope="col">Aerolínea</th><th scope="col">Reservas</th><th scope="col">Total</th><?php elseif ($type === 'vuelos'): ?><th scope="col">Aerolínea</th><th scope="col">Vuelos</th><th scope="col">Asientos ocupados</th><th scope="col">Capacidad</th><?php else: ?><th scope="col">Rol</th><th scope="col">Estado</th><th scope="col">Cantidad</th><?php endif; ?></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><?php if ($type === 'ventas'): ?><td><strong><?= e($row['aerolinea_codigo']) ?></strong><small class="d-block text-muted"><?= e($row['aerolinea_nombre']) ?></small></td><td><?= (int)$row['reservas'] ?></td><td><strong><?= formatPrice((float)$row['total']) ?></strong></td><?php elseif ($type === 'vuelos'): ?><td><strong><?= e($row['aerolinea_codigo']) ?></strong><small class="d-block text-muted"><?= e($row['aerolinea_nombre']) ?></small></td><td><?= (int)$row['vuelos'] ?></td><td><?= (int)$row['ocupados'] ?></td><td><?= (int)$row['capacidad'] ?></td><?php else: ?><td><?= e(ucfirst($row['rol'])) ?></td><td><span class="volara-badge <?= badgeClass($row['estado_aprobacion']) ?>"><?= e(ucfirst($row['estado_aprobacion'])) ?></span></td><td><?= (int)$row['cantidad'] ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div></div>
</div></section></main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
