<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (isLoggedIn()) {
    redirect(dashboardUrl());
}

$pageTitle = 'Iniciar sesión';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Completá todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT * FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1');
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                loginUser($usuario);
                setFlash('success', 'Bienvenido/a, ' . $usuario['nombre'] . '!');
                redirect(dashboardUrl());
            } else {
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