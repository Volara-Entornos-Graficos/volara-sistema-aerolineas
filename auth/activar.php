<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$token = trim($_GET['token'] ?? '');
$message = 'El enlace de activación no es válido o ya venció.';
$type = 'danger';

if ($token !== '') {
    try {
        $db = getDB();
        $stmt = $db->prepare(
            'SELECT id FROM usuarios
             WHERE token_activacion = ? AND token_activacion_expira > NOW()
             LIMIT 1'
        );
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $update = $db->prepare(
                'UPDATE usuarios
                 SET email_verificado = 1, token_activacion = NULL, token_activacion_expira = NULL
                 WHERE id = ?'
            );
            $update->execute([$usuario['id']]);
            $message = 'Tu email fue verificado. Ya podés iniciar sesión.';
            $type = 'success';
        }
    } catch (PDOException $e) {
        $message = 'No se pudo verificar la cuenta. Intentá nuevamente más tarde.';
    }
}

setFlash($type, $message);
redirect('auth/login.php');
