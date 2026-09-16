<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Registrarse';
$errors = [];
$aerolineas = [];

try {
    $db = getDB();
    $aerolineas = $db->query("SELECT id, codigo, nombre FROM aerolineas WHERE estado = 'activa' ORDER BY nombre")->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'No se pudieron cargar las aerolíneas disponibles.';
}

if (isLoggedIn()) redirect(dashboardUrl());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';
    $rol      = $_POST['rol'] ?? 'pasajero';
    $aerolineaId = (int)($_POST['aerolinea_id'] ?? 0);

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página e intentá nuevamente.';
    } elseif (!$nombre || !$apellido || !$email || !$password) {
        $errors[] = 'Completá todos los campos obligatorios.';
    }

    if (!in_array($rol, ['pasajero', 'ceo'], true)) {
        $errors[] = 'Seleccioná un tipo de cuenta válido.';
    }

    if ($rol === 'ceo' && $aerolineaId < 1) {
        $errors[] = 'Seleccioná la aerolínea que representás.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errors)) {
        try {
            $db = getDB();

            $check = $db->prepare('SELECT id FROM usuarios WHERE email = ?');
            $check->execute([$email]);

            if ($check->fetch()) {
                $errors[] = 'Ya existe una cuenta con ese email.';
            } else {
                if ($rol === 'ceo') {
                    $airlineCheck = $db->prepare("SELECT id FROM aerolineas WHERE id = ? AND estado = 'activa'");
                    $airlineCheck->execute([$aerolineaId]);
                    if (!$airlineCheck->fetch()) {
                        $errors[] = 'La aerolínea seleccionada no está disponible.';
                    }
                }

                if (!empty($errors)) {
                    $_SESSION['old'] = compact('nombre', 'apellido', 'email', 'rol', 'aerolineaId');
                } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $activationToken = bin2hex(random_bytes(32));
                $activationExpires = date('Y-m-d H:i:s', time() + 86400);

                $stmt = $db->prepare(
                    'INSERT INTO usuarios
                        (nombre, apellido, email, password, rol, aerolinea_id, activo, estado_aprobacion,
                         email_verificado, token_activacion, token_activacion_expira)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)'
                );

                $stmt->execute([
                    $nombre,
                    $apellido,
                    $email,
                    $hash,
                    $rol,
                    $rol === 'ceo' ? $aerolineaId : null,
                    $rol === 'ceo' ? 0 : 1,
                    $rol === 'ceo' ? 'pendiente' : 'aprobado',
                    $activationToken,
                    $activationExpires
                ]);

                $activationUrl = url('auth/activar.php?token=' . rawurlencode($activationToken));
                $emailSent = sendVolaraEmail($email, 'Activá tu cuenta VOLARA', activationEmail($nombre, $activationUrl));
                $message = $rol === 'ceo'
                    ? 'Solicitud enviada. Verificá tu email y esperá la validación del administrador.'
                    : 'Cuenta creada. Verificá tu email para activar el acceso.';
                setFlash($emailSent ? 'success' : 'warning', $emailSent
                    ? $message
                    : $message . ' El servidor de correo local no está configurado.'
                );
                redirect('auth/login.php');
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Error al registrar. Verificá la conexión a la base de datos.';
        }
    }

    $_SESSION['old'] = compact('nombre', 'apellido', 'email', 'rol', 'aerolineaId');
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main>
    <div class="page-header">
        <div class="container">
            <h1>Crear cuenta</h1>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= url('index.php') ?>">Inicio</a>
                    </li>

                    <li class="breadcrumb-item active">
                        Registrarse
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">

                    <div class="volara-card">

                        <?php if ($errors): ?>
                            <div class="volara-alert alert-danger" role="alert">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $err): ?>
                                        <li><?= e($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" data-validate novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                            <div class="row">

                                <div class="col-md-6 form-group">
                                    <label class="volara-label" for="nombre">
                                        Nombre *
                                    </label>

                                    <input
                                        type="text"
                                        class="volara-input"
                                        id="nombre"
                                        name="nombre"
                                        value="<?= old('nombre') ?>"
                                        required
                                        minlength="2"
                                        autocomplete="given-name">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label class="volara-label" for="apellido">
                                        Apellido *
                                    </label>

                                    <input
                                        type="text"
                                        class="volara-input"
                                        id="apellido"
                                        name="apellido"
                                        value="<?= old('apellido') ?>"
                                        required
                                        minlength="2"
                                        autocomplete="family-name">
                                </div>

                            </div>

                            <div class="form-group">
                                <label class="volara-label" for="email">
                                    Email *
                                </label>

                                <input
                                    type="email"
                                    class="volara-input"
                                    id="email"
                                    name="email"
                                    value="<?= old('email') ?>"
                                    required
                                    autocomplete="email">
                            </div>

                            <div class="form-group">
                                <label class="volara-label" for="rol">Tipo de cuenta *</label>
                                <select class="volara-select" id="rol" name="rol" required>
                                    <option value="pasajero" <?= old('rol', 'pasajero') === 'pasajero' ? 'selected' : '' ?>>Pasajero</option>
                                    <option value="ceo" <?= old('rol') === 'ceo' ? 'selected' : '' ?>>CEO de aerolínea</option>
                                </select>
                                <small class="text-muted">Las cuentas CEO requieren aprobación del administrador.</small>
                            </div>

                            <div class="form-group" id="aerolineaGroup" hidden>
                                <label class="volara-label" for="aerolinea_id">Aerolínea *</label>
                                <select class="volara-select" id="aerolinea_id" name="aerolinea_id">
                                    <option value="">Seleccioná una aerolínea</option>
                                    <?php foreach ($aerolineas as $aerolinea): ?>
                                        <option value="<?= (int)$aerolinea['id'] ?>"
                                            <?= (int)old('aerolineaId') === (int)$aerolinea['id'] ? 'selected' : '' ?>>
                                            <?= e($aerolinea['nombre']) ?> (<?= e($aerolinea['codigo']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- CONTRASEÑA -->
                            <div class="form-group">
                                <label class="volara-label" for="password">
                                    Contraseña *
                                </label>

                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="volara-input form-control"
                                        id="password"
                                        name="password"
                                        required
                                        minlength="8"
                                        placeholder="Mínimo 8 caracteres"
                                        autocomplete="new-password">

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        data-toggle-password="password"
                                        aria-label="Mostrar contraseña">

                                        <i class="bi bi-eye"></i>

                                    </button>
                                </div>
                            </div>

                            <!-- CONFIRMAR CONTRASEÑA -->
                            <div class="form-group">
                                <label class="volara-label" for="password_confirm">
                                    Confirmar contraseña *
                                </label>

                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="volara-input form-control"
                                        id="password_confirm"
                                        name="password_confirm"
                                        required
                                        minlength="8"
                                        placeholder="Repeti la contraseña"
                                        autocomplete="new-password">

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        data-toggle-password="password_confirm"
                                        aria-label="Mostrar contraseña">

                                        <i class="bi bi-eye"></i>

                                    </button>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-volara w-100 btn-volara-lg">
                                Crear cuenta
                            </button>

                        </form>

                        <p class="text-center text-muted small mt-4 mb-0">
                            ¿Ya tenés cuenta?
                            <a href="<?= url('auth/login.php') ?>">
                                Iniciá sesión
                            </a>
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php
clearOld();
require_once __DIR__ . '/../includes/footer.php';
?>