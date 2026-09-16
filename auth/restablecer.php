<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isLoggedIn()) redirect(dashboardUrl());

$pageTitle = 'Nueva contraseña';
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$errors = [];
$validToken = false;
$db = null;

if ($token !== '') {
    try {
        $db = getDB();
        $check = $db->prepare('SELECT id FROM usuarios WHERE token_reset = ? AND token_expira > NOW() AND activo = 1 LIMIT 1');
        $check->execute([$token]);
        $validToken = (bool)$check->fetch();
    } catch (PDOException $e) {
        $errors[] = 'No se pudo validar el enlace.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página e intentá nuevamente.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Las contraseñas no coinciden.';
    } else {
        if (!$db) {
            $db = getDB();
        }
        $stmt = $db->prepare(
            'UPDATE usuarios SET password = ?, token_reset = NULL, token_expira = NULL WHERE token_reset = ?'
        );
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $token]);
        setFlash('success', 'Contraseña actualizada. Ya podés iniciar sesión.');
        redirect('auth/login.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main>
    <div class="page-header">
        <div class="container">
            <h1>Nueva contraseña</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Nueva contraseña</li>
                </ol>
            </nav>
        </div>
    </div>
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="volara-card">
                        <?php if ($errors): ?>
                            <div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div>
                        <?php endif; ?>
                        <?php if (!$validToken): ?>
                            <div class="volara-alert alert-warning" role="alert">El enlace no es válido o ya venció.</div>
                            <a href="<?= url('auth/recuperar.php') ?>" class="btn btn-volara w-100">Solicitar otro enlace</a>
                        <?php else: ?>
                            <form method="POST" data-validate novalidate>
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="token" value="<?= e($token) ?>">
                                <div class="form-group">
                                    <label class="volara-label" for="password">Nueva contraseña</label>
                                    <input type="password" class="volara-input" id="password" name="password" required minlength="8">
                                </div>
                                <div class="form-group">
                                    <label class="volara-label" for="password_confirm">Confirmar contraseña</label>
                                    <input type="password" class="volara-input" id="password_confirm" name="password_confirm" required minlength="8">
                                </div>
                                <button type="submit" class="btn btn-volara w-100 btn-volara-lg">Actualizar contraseña</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
