<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Registrarse';
$errors = [];

if (isLoggedIn()) redirect(dashboardUrl());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if (!$nombre || !$apellido || !$email || !$password) {
        $errors[] = 'Completá todos los campos obligatorios.';
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
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare(
                    'INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([$nombre, $apellido, $email, $hash, 'pasajero']);
                setFlash('success', 'Cuenta creada exitosamente. Iniciá sesión.');
                redirect('auth/login.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'Error al registrar. Verificá la conexión a la base de datos.';
        }
    }
    $_SESSION['old'] = compact('nombre', 'apellido', 'email');
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
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Registrarse</li>
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
                                <ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?></ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" data-validate novalidate>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="volara-label" for="nombre">Nombre *</label>
                                    <input type="text" class="volara-input" id="nombre" name="nombre"
                                           value="<?= old('nombre') ?>" required minlength="2">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="volara-label" for="apellido">Apellido *</label>
                                    <input type="text" class="volara-input" id="apellido" name="apellido"
                                           value="<?= old('apellido') ?>" required minlength="2">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="volara-label" for="email">Email *</label>
                                <input type="email" class="volara-input" id="email" name="email"
                                       value="<?= old('email') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="volara-label" for="password">Contraseña *</label>
                                <input type="password" class="volara-input" id="password" name="password"
                                       required minlength="8" placeholder="Mínimo 8 caracteres">
                            </div>
                            <div class="form-group">
                                <label class="volara-label" for="password_confirm">Confirmar contraseña *</label>
                                <input type="password" class="volara-input" id="password_confirm"
                                       name="password_confirm" required minlength="8">
                            </div>
                            <button type="submit" class="btn btn-volara w-100 btn-volara-lg">
                                Crear cuenta
                            </button>
                        </form>
                        <p class="text-center text-muted small mt-4 mb-0">
                            ¿Ya tenés cuenta? <a href="<?= url('auth/login.php') ?>">Iniciá sesión</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php clearOld(); require_once __DIR__ . '/../includes/footer.php'; ?>
