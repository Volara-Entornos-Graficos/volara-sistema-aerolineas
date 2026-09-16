<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireLogin();

$pageTitle = 'Mi perfil';
$user = currentUser();
$errors = [];
$db = null;

try {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->execute([$user['id']]);
    $profile = $stmt->fetch();
} catch (PDOException $e) {
    $profile = null;
    $errors[] = 'No se pudo cargar tu perfil.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $profile) {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $newPassword = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página.';
    } elseif (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 80 || mb_strlen($apellido) < 2 || mb_strlen($apellido) > 80) {
        $errors[] = 'El nombre y apellido deben tener entre 2 y 80 caracteres.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
        $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
    } elseif ($newPassword !== $passwordConfirm) {
        $errors[] = 'Las nuevas contraseñas no coinciden.';
    } else {
        try {
            $fields = ['nombre = ?', 'apellido = ?', 'telefono = ?', 'documento = ?'];
            $params = [$nombre, $apellido, $telefono ?: null, $documento ?: null];
            if ($newPassword !== '') {
                $fields[] = 'password = ?';
                $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
            $params[] = $user['id'];
            $stmt = $db->prepare('UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = ?');
            $stmt->execute($params);

            $_SESSION['usuario']['nombre'] = $nombre;
            $_SESSION['usuario']['apellido'] = $apellido;
            setFlash('success', 'Perfil actualizado correctamente.');
            redirect('pages/usuario/perfil.php');
        } catch (PDOException $e) {
            $errors[] = 'No se pudo actualizar el perfil.';
        }
    }

    $profile = array_merge($profile, compact('nombre', 'apellido', 'telefono', 'documento'));
}

$flash = getFlash();
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<main>
    <section class="page-header"><div class="container"><h1>Mi perfil</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('pages/usuario/inicioUsuario.php') ?>">Mi cuenta</a></li><li class="breadcrumb-item active">Mi perfil</li></ol></nav></div></section>
    <section class="section"><div class="container"><div class="row justify-content-center"><div class="col-lg-7">
        <?php if ($errors): ?><div class="volara-alert alert-danger" role="alert"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <?php if ($flash): ?><div class="volara-alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
        <?php if ($profile): ?><div class="volara-card"><div class="d-flex align-items-center gap-3 mb-4"><span class="user-avatar profile-avatar" aria-hidden="true"><?= e(strtoupper(substr($profile['nombre'], 0, 1) . substr($profile['apellido'], 0, 1))) ?></span><div><h2 class="h4 mb-1"><?= e($profile['nombre'] . ' ' . $profile['apellido']) ?></h2><p class="text-muted mb-0"><?= e($profile['email']) ?></p></div></div><form method="POST" data-validate novalidate><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><div class="row g-3"><div class="col-md-6"><label class="volara-label" for="nombre">Nombre *</label><input class="volara-input" id="nombre" name="nombre" required maxlength="80" value="<?= e($profile['nombre']) ?>"></div><div class="col-md-6"><label class="volara-label" for="apellido">Apellido *</label><input class="volara-input" id="apellido" name="apellido" required maxlength="80" value="<?= e($profile['apellido']) ?>"></div><div class="col-md-6"><label class="volara-label" for="telefono">Teléfono</label><input class="volara-input" id="telefono" name="telefono" maxlength="30" value="<?= e($profile['telefono'] ?? '') ?>"></div><div class="col-md-6"><label class="volara-label" for="documento">Documento</label><input class="volara-input" id="documento" name="documento" maxlength="20" value="<?= e($profile['documento'] ?? '') ?>"></div></div><hr class="my-4"><h3 class="h5">Cambiar contraseña</h3><p class="text-muted small">Dejá estos campos vacíos si no querés cambiarla.</p><div class="row g-3"><div class="col-md-6"><label class="volara-label" for="password">Nueva contraseña</label><input class="volara-input" type="password" id="password" name="password" minlength="8"></div><div class="col-md-6"><label class="volara-label" for="password_confirm">Confirmar contraseña</label><input class="volara-input" type="password" id="password_confirm" name="password_confirm" minlength="8"></div></div><button type="submit" class="btn btn-volara mt-4"><i class="bi bi-check-lg" aria-hidden="true"></i> Guardar cambios</button></form></div><?php endif; ?>
    </div></div></div></section>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
