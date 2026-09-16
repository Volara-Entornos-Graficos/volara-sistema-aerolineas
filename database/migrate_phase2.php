<?php
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$columns = $db->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_COLUMN);

$addColumns = [
    'email_verificado' => "ADD COLUMN email_verificado TINYINT(1) NOT NULL DEFAULT 0 AFTER activo",
    'estado_aprobacion' => "ADD COLUMN estado_aprobacion ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'aprobado' AFTER email_verificado",
    'token_activacion' => "ADD COLUMN token_activacion VARCHAR(100) DEFAULT NULL AFTER estado_aprobacion",
    'token_activacion_expira' => "ADD COLUMN token_activacion_expira DATETIME DEFAULT NULL AFTER token_activacion",
];

foreach ($addColumns as $column => $definition) {
    if (!in_array($column, $columns, true)) {
        $db->exec("ALTER TABLE usuarios $definition");
    }
}

$indexes = $db->query('SHOW INDEX FROM usuarios')->fetchAll(PDO::FETCH_COLUMN, 2);
$addIndexes = [
    'uk_usuario_token_activacion' => 'ADD UNIQUE KEY uk_usuario_token_activacion (token_activacion)',
    'idx_usuario_rol_activo' => 'ADD KEY idx_usuario_rol_activo (rol, activo)',
    'idx_usuario_aerolinea_rol' => 'ADD KEY idx_usuario_aerolinea_rol (aerolinea_id, rol)',
];

foreach ($addIndexes as $index => $definition) {
    if (!in_array($index, $indexes, true)) {
        $db->exec("ALTER TABLE usuarios $definition");
    }
}

$db->exec("UPDATE usuarios SET email_verificado = 1 WHERE email IN ('admin@volara.com', 'ceo@volara.com', 'maria@email.com')");

echo "Migracion Fase 2 aplicada correctamente." . PHP_EOL;
