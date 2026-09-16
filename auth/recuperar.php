<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isLoggedIn()) redirect(dashboardUrl());

$pageTitle = 'Recuperar contraseña';
$errors = [];
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página e intentá nuevamente.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ingresá un email válido.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT id, nombre FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1');
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);
                $update = $db->prepare('UPDATE usuarios SET token_reset = ?, token_expira = ? WHERE id = ?');
                $update->execute([$token, $expires, $usuario['id']]);

                $resetUrl = url('auth/restablecer.php?token=' . rawurlencode($token));
                sendVolaraEmail($email, 'Restablecé tu contraseña VOLARA', resetEmail($usuario['nombre'], $resetUrl));
            }

            $sent = true;
        } catch (PDOException $e) {
            $errors[] = 'No se pudo procesar la solicitud. Intentá nuevamente más tarde.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main>
    <div class="page-header">
        <div class="container">
            <h1>Recuperar contraseña</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Recuperar contraseña</li>
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
                        <?php elseif ($sent): ?>
                            <div class="volara-alert alert-success" role="status">
                                Si existe una cuenta con ese email, recibirás instrucciones para restablecer la contraseña.
                            </div>
                        <?php endif; ?>
                        <form method="POST" data-validate novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <div class="form-group">
                                <label class="volara-label" for="email">Email</label>
                                <input type="email" class="volara-input" id="email" name="email" required autocomplete="email">
                            </div>
                            <button type="submit" class="btn btn-volara w-100 btn-volara-lg">Enviar instrucciones</button>
                        </form>
                        <p class="text-center text-muted small mt-4 mb-0">
                            <a href="<?= url('auth/login.php') ?>">Volver al inicio de sesión</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
