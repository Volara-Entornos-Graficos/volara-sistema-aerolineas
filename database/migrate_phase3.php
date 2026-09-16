<?php
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$columns = $db->query('SHOW COLUMNS FROM novedades')->fetchAll(PDO::FETCH_COLUMN);

$definitions = [
    'fecha_inicio' => 'ADD COLUMN fecha_inicio DATE DEFAULT NULL AFTER activa',
    'fecha_expiracion' => 'ADD COLUMN fecha_expiracion DATE DEFAULT NULL AFTER fecha_inicio',
];

foreach ($definitions as $column => $definition) {
    if (!in_array($column, $columns, true)) {
        $db->exec("ALTER TABLE novedades $definition");
    }
}

echo "Migracion Fase 3 aplicada correctamente." . PHP_EOL;
