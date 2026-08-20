<?php
/**
 * Autenticación y autorización
 */

function isLoggedIn(): bool
{
    return isset($_SESSION['usuario_id']);
}

function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['usuario'] ?? null;
}

function userRole(): ?string
{
    return currentUser()['rol'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('warning', 'Debes iniciar sesión para acceder a esta sección.');
        redirect('auth/login.php');
    }
}

function requireRole(string ...$roles): void
{
    requireLogin();
    if (!in_array(userRole(), $roles, true)) {
        setFlash('danger', 'No tienes permisos para acceder a esta sección.');
        redirect('index.php');
    }
}

function loginUser(array $usuario): void
{
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario'] = [
        'id'           => $usuario['id'],
        'nombre'       => $usuario['nombre'],
        'apellido'     => $usuario['apellido'],
        'email'        => $usuario['email'],
        'rol'          => $usuario['rol'],
        'aerolinea_id' => $usuario['aerolinea_id'] ?? null,
    ];
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function dashboardUrl(): string
{
    return match (userRole()) {
        'admin'    => 'pages/admin/inicioAdmin.php',
        'ceo'      => 'pages/ceo/inicioCeo.php',
        'pasajero' => 'pages/usuario/inicioUsuario.php',
        default    => 'index.php',
    };
}
