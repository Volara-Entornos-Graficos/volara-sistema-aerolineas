<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isLoggedIn()) {
    redirect(dashboardUrl());
}

$pageTitle = 'Iniciar sesión';
$errors = [];

// Rate limiting: proteger contra ataques de fuerza bruta
$maxLoginAttempts = 5;
$loginAttempts = $_SESSION['login_attempts'] ?? 0;
$lastLoginAttempt = $_SESSION['last_login_attempt'] ?? 0;

if ($loginAttempts >= $maxLoginAttempts) {
    $timeSinceLastAttempt = time() - $lastLoginAttempt;
    $delaySeconds = max(0, 30 - $timeSinceLastAttempt); // 30 segundos de delay
    
    if ($delaySeconds > 0) {
        // Esperar un poco para desacelerar intentos
        sleep(min(2, $delaySeconds));
        $errors[] = "Demasiados intentos fallidos. Esperá $delaySeconds segundos e intentá nuevamente.";
    } else {
        // Resetear contador después del delay
        $_SESSION['login_attempts'] = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'La sesión del formulario venció. Recargá la página e intentá nuevamente.';
    } elseif (!$email || !$password) {
        $errors[] = 'Completá todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                if ((int)$usuario['activo'] !== 1) {
                    $errors[] = match ($usuario['estado_aprobacion'] ?? null) {
                        'pendiente' => 'Tu solicitud de CEO está pendiente de aprobación administrativa.',
                        'rechazado' => 'Tu solicitud de CEO fue rechazada.',
                        default     => 'La cuenta está inactiva. Contactá al administrador.',
                    };
                } elseif ((int)($usuario['email_verificado'] ?? 0) !== 1) {
                    // Email verificado es OBLIGATORIO para todos los roles excepto admin
                    if ($usuario['rol'] !== 'admin') {
                        $errors[] = 'Verificá tu email antes de iniciar sesión. Revisá tu bandeja de entrada.';
                    } else {
                        // Admin puede iniciar sesión sin verificar (para emergencias)
                        // pero se debería forzar verificación en perfil
                        loginUser($usuario);
                        $_SESSION['login_attempts'] = 0; // Reset en login exitoso
                        setFlash('warning', 'Tu email no está verificado. Completá la verificación en tu perfil.');
                        redirect(dashboardUrl());
                    }
                } else {
                    loginUser($usuario);
                    $_SESSION['login_attempts'] = 0; // Reset en login exitoso
                    setFlash('success', 'Bienvenido/a, ' . $usuario['nombre'] . '!');
                    redirect(dashboardUrl());
                }
            } else {
                // Fallo de autenticación: incrementar contador
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $_SESSION['last_login_attempt'] = time();
                $errors[] = 'Email o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error de conexión. Verificá que la base de datos esté configurada.';
        }
    }
    $_SESSION['old'] = ['email' => $email];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main>
    <div class="page-header">
        <div class="container">
            <h1>Iniciar sesión</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li class="breadcrumb-item active">Iniciar sesión</li>
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
                            <div class="volara-alert alert-danger" role="alert">
                                <?= e(implode(' ', $errors)) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" data-validate novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <div class="form-group">
                                <label class="volara-label" for="email">Email</label>
                                <input type="email" class="volara-input" id="email" name="email"
                                    value="<?= old('email') ?>" required autocomplete="email"
                                    placeholder="tu@email.com">
                            </div>

                            <div class="form-group">
                                <label class="volara-label" for="password">Contraseña</label>

                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="form-control input-password volara-input"
                                        id="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="••••••••">

                                    <button
                                        class="btn btn-outline-secondary"
                                        type="button"
                                        data-toggle-password="password"
                                        aria-label="Mostrar contraseña">

                                        <i class="bi bi-eye"></i>

                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mb-4">
                                <a href="<?= url('auth/recuperar.php') ?>" class="small">¿Olvidaste tu contraseña?</a>
                            </div>

                            <button type="submit" class="btn btn-volara w-100 btn-volara-lg">
                                Iniciar sesión
                            </button>
                        </form>

                        <p class="text-center text-muted small mt-4 mb-0">
                            ¿No tenés cuenta?
                            <a href="<?= url('auth/registro.php') ?>">Registrate</a>
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