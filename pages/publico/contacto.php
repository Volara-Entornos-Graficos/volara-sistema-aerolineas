<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$pageTitle = 'Contacto';
$errors = [];
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 80) {
        $errors[] = 'El nombre debe tener entre 2 y 80 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ingresá un email válido.';
    } elseif (mb_strlen($mensaje) < 10 || mb_strlen($mensaje) > 2000) {
        $errors[] = 'El mensaje debe tener entre 10 y 2000 caracteres.';
    } else {
        $destination = (string) env('MAIL_USERNAME', MAIL_FROM);
        $body = '<p><strong>Nombre:</strong> ' . e($nombre) . '</p>'
            . '<p><strong>Email:</strong> ' . e($email) . '</p>'
            . '<p><strong>Mensaje:</strong></p><p>' . nl2br(e($mensaje)) . '</p>';
        $sent = sendVolaraEmail($destination, 'Consulta VOLARA de ' . $nombre, $body);
        if ($sent) {
            setFlash('success', 'Recibimos tu consulta. Te vamos a responder a la brevedad.');
            redirect('pages/publico/contacto.php');
        }
        $errors[] = 'No se pudo enviar el mensaje. Intentá más tarde o escribinos a ' . $destination . '.';
        $_SESSION['old'] = compact('nombre', 'email', 'mensaje');
    }
}

$flash = getFlash();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main id="contenido-principal" tabindex="-1">
    <section class="page-header">
        <div class="container">
            <h1>Contacto</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Contacto</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="volara-card">
                        <?php if ($errors): ?>
                            <div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div>
                        <?php endif; ?>
                        <?php if ($flash): ?>
                            <div class="volara-alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
                        <?php endif; ?>
                        <form method="POST" data-validate novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <div class="form-group">
                                <label class="volara-label" for="nombre">Nombre *</label>
                                <input class="volara-input" id="nombre" name="nombre" required maxlength="80" value="<?= old('nombre') ?>">
                            </div>
                            <div class="form-group">
                                <label class="volara-label" for="email">Email *</label>
                                <input class="volara-input" type="email" id="email" name="email" required value="<?= old('email') ?>">
                            </div>
                            <div class="form-group">
                                <label class="volara-label" for="mensaje">Mensaje *</label>
                                <textarea class="volara-input" id="mensaje" name="mensaje" rows="6" required minlength="10" maxlength="2000"><?= old('mensaje') ?></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-volara">Enviar consulta</button>
                                <a href="<?= url('index.php') ?>" class="btn btn-volara-outline">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
                <aside class="col-lg-5">
                    <div class="volara-card">
                        <h2 class="h5">Datos de contacto</h2>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-envelope me-2" aria-hidden="true"></i><a href="mailto:volara.aerolinea@gmail.com">volara.aerolinea@gmail.com</a></li>
                            <li class="mb-2"><i class="bi bi-geo-alt me-2" aria-hidden="true"></i>Rosario, Santa Fe, Argentina</li>
                            <li><i class="bi bi-mortarboard me-2" aria-hidden="true"></i>UTN Facultad Regional Rosario</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</main>
<?php
clearOld();
require_once __DIR__ . '/../../includes/footer.php';
